<?php

    require_once("Required.php");

    Required::Logger()->Database()->DbSession()->Cryptographer()->HttpHeader();

    $logger = new Logger(ROOT_DIRECTORY);
    $endecryptor = new Cryptographer(SECRET_KEY);
    $db = new Database(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);

    $db->connect(); $db->fetchAsObject();

    #region check session
        if(!isset($_GET["session-id"]) || empty(trim($_GET["session-id"]))){
            HttpHeader::redirect("sorry.php?msg=No active session found.");
        }

        $encSessionId = trim($_GET["session-id"]);

        try {
            $sessionId = $endecryptor->decrypt($encSessionId);
            $session = new DbSession($db, SESSION_TABLE);
            $session->continue($sessionId);
            $session->close();
            HttpHeader::redirect("index.php");

        } catch (\SessionException $th) {
            HttpHeader::redirect("sorry.php?msg=No active session found.");
        } catch (\Exception $exp) {
            HttpHeader::redirect("sorry.php?msg=Unknown error occured. Error code- 197346.");
        }
    #endregion
?>

