<?php
    declare(strict_types=1);

    require_once("../../Required.php");

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

    #region Validate query string
        try {
            $encSessionId = $validator->title("Session parameter")->get("session-id")->required()->validate();
            $sessionId = $crypto->decrypt($encSessionId);
            if (!$sessionId) {
                die($json->fail()->message("Invalid session parameter. Please login again.")->create());
            }

            $taskId = $validator->label("Task ID")->post("taskId")->required()->validate();
            $taskId = $crypto->decrypt($taskId);
            if (!$taskId) {
                die($json->fail()->message("Task ID invalid.")->create());
            }

            if(isset($_POST["isApproved"])){
                $isApproved = 1;
                $now = "'". $clock->toString("now", DatetimeFormat::MySqlDatetime()) . "'";
            }
            else{
                $isApproved = 0;
                $now = 'null';
            }

        } catch (\ValidationException $exp) {
            die($json->fail()->message($exp->getMessage())->create());
        }

        #region check session
            try {
                //DbSession() constructor requires a connected Database instance.
                $session = new DbSession($db, SESSION_TABLE);
                $session->continue((int) $sessionId);
                $developerId = $session->getData("devId");
            } catch (\SessionException $th) {
                die($json->fail()->message("Invalid session. Please login again.")->create());
            } catch (\Exception $exp) {
                die($json->fail()->message("Invalid session. Please login again.")->create());
            }
        #endregion

        try {
            $result = $db->update("UPDATE tasks set isApproved=$isApproved, approvedOn=$now  WHERE taskId=$taskId",array());

            $photoDirectory = ROOT_DIRECTORY . "/screenshots";
            $photoPath = $photoDirectory . "/" . $taskId . '.jpg';
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }

        } catch (\Throwable $th) {
            $logger->createLog($th->getMessage());
            die($json->fail()->message("Failed to execute.")->create());
        }
       
        die($json->success()->message("Done.")->create());
    #endregion
?>