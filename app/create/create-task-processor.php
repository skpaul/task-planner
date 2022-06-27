<?php

    declare(strict_types=1);

    #region Import libraries
    require_once("../../Required.php");
    Required::Logger()
        ->Database()->DbSession()
        ->DataValidator()
        ->Cryptographer()
        ->Clock()->JSON()->Imaging();
    #endregion

    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new Database(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $clock = new Clock();
        $validator = new DataValidator();
        $json = new JSON();
      
    #endregion

    #region Validate query string
    try {
        //'session-id' parameter must be present in query string-
        $encSessionId = $validator->title("Request")->get("session-id")->required()->validate();
    } catch (\ValidationException $exp) {
        die($json->fail()->message("Invalid request")->create());
    }

    $sessionId = $crypto->decrypt($encSessionId);
    if (!$sessionId) die($json->fail()->message("Invalid session parameter.")->create());

    #endregion

    #region Database connection
    $db->connect();
    $db->fetchAsObject();
    #endregion

    #region Session check and validation
    try {
        //DbSession() constructor requires a connected Database instance.
        $session = new DbSession($db, SESSION_TABLE);
        $session->continue((int)$sessionId);
        $devId = $session->getData("devId");
        if (!isset($devId) || empty($devId))
            die($json->fail()->message("Invalid session. Please start again.")->create());
            
    } catch (\SessionException $th) {
        die($json->fail()->message("Invalid session. Please start again.")->create());
    }
    #endregion



    #region Photo and Signature validation
    try {

        $now = $clock->toString("now", DatetimeFormat::MySqlDatetime());
        $updatedData["title"] = $_POST["title"];
        $updatedData["description"] = $_POST["description"];
        $updatedData["assignedTo"] = $_POST["assignedTo"];
        $updatedData["priorityId"] = $_POST["priorityId"];
        $updatedData["taskStatusId"] = 1;
        $updatedData["createdOn"] =  $now;
        
        $id = $db->insert("tasks", $updatedData);
        $sql = "UPDATE tasks set imageName =:imageName where taskId=$id";
        $result = $db->update($sql, array("imageName"=>"$id.jpg"));

        Imaging::validate("ApplicantPhoto", "Applicant Photo" ,0,0,100);

        $photoDirectory = ROOT_DIRECTORY . "/screenshots";
        if (!file_exists($photoDirectory)) {
            mkdir($photoDirectory, 0777, true);
        }
        $photoPath = $photoDirectory . "/" . $id . '.jpg';
        if (file_exists($photoPath)) {
            unlink($photoPath);
        }

        Imaging:: save("ApplicantPhoto", "Applicant's photo" , $photoPath);

    } catch (\Exception $exp) {
        $logger->createLog($exp->getMessage());
        $msg =$json->fail()->message($exp->getMessage())->create();
        die($msg);
    }
    #endregion

    exit($json->success()->message("Success")->create());
?>