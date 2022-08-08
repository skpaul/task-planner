<?php

    declare(strict_types=1);

    //Import PHPMailer classes into the global namespace
    //These must be at the top of your script, not inside a function
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    //Load Composer's autoloader
    require '../../vendor/autoload.php';

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
        $task["title"] = $_POST["title"];
        $description = $_POST["description"];
        // $description = str_replace('<br />', PHP_EOL, $description);
        // $description = str_replace('<br />', "\n", $description);
        // $description = str_replace("\n", '<br />',  $description);
        $task["description"] =  $description;  //$_POST["description"];
        $task["assignedTo"] = $_POST["assignedTo"];
        $task["priorityId"] = $_POST["priorityId"];
        $task["attachments"] = $_POST["attachments"];
        $task["attachmentsType"] = $_POST["attachmentsType"];
        $task["taskStatusId"] = 1;
        $task["createdOn"] =  $now;

        $developer = $db->selectSingle("SELECT * FROM developers WHERE developerId={$task["assignedTo"]}");
        $id = $db->insert("tasks", $task);
        $text = urlencode("You have got a new task. Please visit task-planner");
        $number = $developer->mobile;
        $smsresult = file_get_contents("http://66.45.237.70/api.php?username=skpaul&password=New@2022$2022&number=$number&message=$text");
    

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'winbip.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'info@winbip.com';                     //SMTP username
        $mail->Password   = 'ParkAvenue$1978';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom('info@winbip.com', 'Winbip Solutions');
        $mail->addReplyTo('info@winbip.com', 'Winbip Solutions');
        $mail->addAddress($developer->email1);
        $mail->addCC($developer->email2);

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'New Task';
        $mail->Body    = 'You have been assigned a new task. Please visit task-planner';
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();

    } catch (\Exception $exp) {
        $logger->createLog($exp->getMessage());
        $msg =$json->fail()->message($exp->getMessage())->create();
        die($msg);
    }
    #endregion

    exit($json->success()->message("Success")->create());
?>