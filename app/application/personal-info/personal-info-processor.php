<?php 
    require_once("../../../Required.php");

    Required::Logger()
        ->Database()->DbSession()
        ->Clock()
        ->Cryptographer()
        ->JSON()
        ->DataValidator();

    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new Database(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $validator = new DataValidator();
        $clock = new Clock();
        $json = new JSON();
    #endregion

    #region Validate query string
        try {
            $encAction = $validator->title("Action parameter")->get("action")->required()->validate();
            $encSessionId = $validator->title("Session parameter")->get("session-id")->required()->validate();
        } catch (\ValidationException $exp) {
            die($json->fail()->message($exp->getMessage(). " Please login again.")->create());
        }

        $action = $crypto->decrypt($encAction);
        if (!$action) {
            die($json->fail()->message("Invalid action parameter. Please login again.")->create());
        }

        $sessionId = $crypto->decrypt($encSessionId);
        if (!$sessionId) {
            die($json->fail()->message("Invalid session parameter. Please login again.")->create());
        }
    #endregion

    #region Database connection
        $db->connect(); $db->fetchAsObject();
    #endregion

    #region check session
        try {
            //DbSession() constructor requires a connected Database instance.
            $session = new DbSession($db, SESSION_TABLE);
            $session->continue($sessionId);
            $registrationId = $session->getData("registrationId");
            $configId = $session->getData("configId");
        } catch (\SessionException $th) {
            die($json->fail()->message("Invalid session. Please login again.")->create());
        } catch (\Exception $exp) {
            die($json->fail()->message("Invalid session. Please login again.")->create());
        }
    #endregion

    if(isset($registrationId)){
        //action must be update/preview
        if ($action !== "preview") {
            if ($action !== "update") {
                die($json->fail()->message("Invalid action parameter.")->create());
            }
        }
    }
    else{
        if ($action !== "create") 
            die($json->fail()->message("Invalid action parameter. Please login again.")->create());
    }

    #region Reset prev data
        $personalInfo["name"]=null;
        $personalInfo["father"]=null;
        $personalInfo["mother"]=null;
        $personalInfo["dob"]=null;
        $personalInfo["gender"]=null;
        $personalInfo["nationality"]=null;
        $personalInfo["birthReg"]=null;
        $personalInfo["nid"]=null;
        $personalInfo["passport"]=null;
        $personalInfo["mobile"]=null;
        $personalInfo["email"]=null;
    #endregion

    #region Validate form 
        try {
            $personalInfo["name"] = strtoupper($validator->label("Applicant's name")->post("name")->required()->asString(true)->maxLen(50)->validate());
            $personalInfo["father"] = strtoupper($validator->label("Father's name")->post("father")->required()->asString(true)->maxLen(50)->validate());
            $personalInfo["mother"] = strtoupper($validator->label("Mother's name")->post("mother")->required()->asString(true)->maxLen(50)->validate());

            $dateOfBirth = $validator->label("Date of birth")->post("dob")->required()->asDate()->validate();
            $personalInfo["dob"] = $dateOfBirth->format('Y-m-d');
            
            $personalInfo["gender"] = $validator->label("Gender")->post("gender")->required()->asString(true)->maxLen(6)->validate();
            $personalInfo["nationality"] = $validator->label("Nationality")->post("nationality")->required()->asString(true)->maxLen(50)->validate();

            $personalInfo["mobile"] = $validator->label("Mobile No.")->post("mobile")->required()->asMobile()->validate();
            $personalInfo["email"] = $validator->label("Email")->post("email")->optional()->asEmail()->validate();
            
            $personalInfo["birthReg"] = $validator->label("Birth Certificate No.")->post("birthReg")->optional()->asNumeric()->maxLen(30)->default(NULL)->validate();
            $personalInfo["nid"]  = $validator->label("NID No.")->post("nid")->optional()->asNumeric()->maxLen(30)->default(NULL)->validate();
            $personalInfo["passport"]  = $validator->label("Passport No.")->post("passport")->optional()->asString(true)->maxLen(30)->default(NULL)->validate();
            
            if($personalInfo["birthReg"] == NULL && $personalInfo["nid"] == NULL && $personalInfo["passport"] == NULL)
                die($json->fail()->message("Birth Certificate or NID or Passport No. required.")->create());

            $nextStep  = $validator->label("Next step")->post("next")->required()->asString(false)->validate();

        } catch (\ValidationException $ve) {
            die($json->fail()->message($ve->getMessage())->create());
        }
        catch (\Exception $exp) {
            $logger->createLog($exp->getMessage());
            die($json->fail()->message($exp->getMessage())->create());
        }
    #endregion

    #region Save personal info
        try{
            if($action=="create" && !isset($registrationId)){
                $personalInfo["draftExpireDatetime"] =  $session->getData("draftExpires");
                $personalInfo["draftId"] =  $session->getData("draftId");
                $personalInfo["postConfigId"] =  $configId;
                $personalInfo["registrationId"] = $db->insert( "lc_registration_cinfo", $personalInfo);  
                $session->setData("registrationId", $personalInfo["registrationId"]);
            }

            if($action=="update" && isset($registrationId)){
                $whereSQL = "registrationId=:registrationId";
                $whereParams = array("registrationId"=>$registrationId);
                $db->update("lc_registration_cinfo", $personalInfo, $whereSQL, $whereParams);  
            }
        }
        catch (\Exception $exp) {
            if($db->inTransaction())
                $db->rollBack();

            $logger->createLog($exp->getMessage());
            die($json->fail()->message("Problem in saving data. Please try again.")->create());
        }  
    #endregion
  
    #region Check Contact Info exists or not
        if($nextStep == "continue"){
            if($action=="create" && !isset($registrationId)){
                //if current action= create, then there must not be any address information.
                $redirectUrl = BASE_URL . "/app/application/address/address.php?session-id=$encSessionId&action=" . $crypto->encrypt("create");
            }

            if($action=="update" && isset($registrationId)){
               $sql = "SELECT hasAddress FROM lc_registration_cinfo WHERE registrationId = $registrationId";
               $hasAddress = ($db->selectSingle($sql))->hasAddress;

               if($hasAddress){
                    $redirectUrl = BASE_URL . "/app/application/address/address.php?session-id=$encSessionId&action=" . $crypto->encrypt("preview");
                }
                else{
                    $redirectUrl = BASE_URL . "/app/application/address/address.php?session-id=$encSessionId&action=" . $crypto->encrypt("create");
                }
            }
        }
        else{
            //return to exit url.
            $session->close();
            $redirectUrl = BASE_URL . "/app/application/save-exit.php";
        }
    #endregion

    exit($json->success()->redirecturl($redirectUrl)->create());
?>