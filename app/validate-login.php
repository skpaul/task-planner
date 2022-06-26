<?php 
    require_once("../Required.php");

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

    #region Database connection
        $db->connect(); $db->fetchAsObject();
    #endregion

    #region Validate form 
        try {
            $loginName = $validator->label("Login Name")->post("loginName")->required()->asString(false)->maxLen(15)->validate();
            $loginPassword = $validator->label("Login Password")->post("loginPassword")->required()->asString(false)->maxLen(15)->validate();
        }
        catch (\Exception $exp) {
            $logger->createLog($exp->getMessage());
            die($json->fail()->message($exp->getMessage())->create());
        }
    #endregion


        try{
           $sql = "SELECT developerId from developers where loginName=:loginName AND loginPassword=:loginPassword";
           $developer = $db->selectSingleOrNull($sql, array("loginName"=>$loginName, "loginPassword"=>$loginPassword));
        }
        catch (\Exception $exp) {
            $logger->createLog($exp->getMessage());
            die($json->fail()->message("Problem found. Please try again.")->create());
        }  

        if($developer == null){
            die($json->fail()->message("User not found.")->create());
        }
  
        $session = new DbSession($db, SESSION_TABLE);
        $session->startNew($developer->developerId);
        $session->setData("devId", $developer->developerId);
        $sessionId = $session->getSessionId();
        $redirectUrl = BASE_URL . "/app/developer/tasks/view-list.php?session-id=" . $crypto->encrypt($sessionId);

    exit($json->success()->redirecturl($redirectUrl)->create());
?>