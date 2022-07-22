<?php

declare(strict_types=1);

#region Import libraries
require_once("../../Required.php");
Required::Logger()
    ->Database()->DbSession()
    ->DataValidator()
    ->Cryptographer()
    ->HttpHeader()
    ->Clock();
#endregion

#region Variable declaration & initialization
$logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
$crypto = new Cryptographer(SECRET_KEY);
$db = new Database(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
$clock = new Clock();
$validable = new DataValidator();

#endregion

#region Validate query string
try {
    // //'session-id' parameter must be present in query string-
    $encSessionId = $validable->title("Request")->get("session-id")->required()->validate();
} catch (\ValidationException $exp) {
    HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid request.");
}

$sessionId = $crypto->decrypt($encSessionId);
if (!$sessionId) {
    HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Session parameter is invalid.");
}
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
        HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid session.");
} catch (\SessionException $th) {
    HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid session.");
}
#endregion


    $devopers = $db->selectMany("select developerId, fullName from developers order by fullName");
    $priorities = $db->selectMany("select priorityId, name from priorities order by priorityId");

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Create Task || <?= ORGANIZATION_SHORT_NAME ?></title>
        <?php
        Required::metaTags()->omnicss()->griddle()->bootstrapGrid()->sweetModalCSS()->airDatePickerCSS();
        ?>

      

        <style>
            .sweet-modal-content {
                color: black;
            }

            /* 34 39 46 */

            .sweet-modal-overlay {
                /* background: radial-gradient(at center, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.7) 100%); */
                background: radial-gradient(at center, rgba(34, 39, 46, 0.6) 0%, rgba(34, 39, 46, 0.7) 100%);
            }

            .error {
                border-color: red !important;
            }
        </style>

    </head>

    <body>
        <div class="master-wrapper">
            <header class="header">
              
            </header>

            <main class="main">
            <div class="container-fluid flex flex-wrap">
                

                <nav class="left-nav">
                        <?php
                            require_once(ROOT_DIRECTORY . '/inc/AdminLeftNav.php');
                            echo AdminLeftNav::CreateFor("superadmin", BASE_URL, $encSessionId);
                        ?>
                        </nav> 

                  

                    <div class="content">

                    <div>
                        <a class="fg-muted flex ai-center jc-end" href="<?= BASE_URL ?>/logout.php?session-id=<?= $encSessionId ?>">
                            <span class="m-icons">logout</span> Logout
                        </a>
                    </div>

                        <div class="card">
                            <p class="steps fg-muted">New Task</p>
                            <form action="create-task-processor.php?session-id=<?= $encSessionId ?>" method="post" enctype="multipart/form-data">
                                
                                <input type="text" name="title" placeholder="title">
                                <textarea name="description" placeholder="description" cols="30" rows="10"></textarea>
                                <select name="assignedTo">
                                        <option value="">Assign to</option>
                                        <?php
                                            foreach ($devopers as $dev) {
                                        ?>
                                            <option value="<?=$dev->developerId?>"><?=$dev->fullName?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>

                                    <select name="priorityId">
                                        <option value="">priority</option>
                                        <?php
                                            foreach ($priorities as $prio) {
                                        ?>
                                            <option value="<?=$prio->priorityId?>"><?=$prio->name?></option>
                                        <?php
                                            }
                                        ?>
                                    </select>
                                    <a style="color: white;" href="https://pasteboard.co/" target="_blank" rel="noopener noreferrer">pasteboard.co</a>
                                    <a style="color: white;" href="https://imgbb.com/upload" target="_blank" rel="noopener noreferrer">imgbb.com</a>
                                    <input type="text" name="attachments" placeholder="separated by comma if multiple">
                                    <select name="attachmentsType">
                                        <option value="">Image type</option>
                                        <option value="link">Link</option>
                                        <option value="file">File (.jpg/.png/.pdf etc.)</option>
                                    </select>
                                <div class="mt300">
                                    <input class="form-submit-button" type="submit" value="Submit">
                                </div>
                            </form>
                        </div>
                    </div><!-- .content// -->

                    <!-- 
                        <aside style="display: flex; flex-direction: column;">
                            asdsdaf
                        </aside> 
                    -->
                </div><!-- .container// -->
            </main>
            <footer class="footer">
               
            </footer>
        </div>

        <script>
            var baseUrl = '<?php echo BASE_URL; ?>';
        </script>
        <?php
        Required::jquery()->hamburgerMenu()->sweetModalJS()->airDatePickerJS()->moment()->swiftSubmit()->SwiftNumeric()->leftNavJS();
        ?>
        <script src="<?= BASE_URL ?>/assets/plugins/jquery-ui/jquery-ui.min.js" ;></script>
        <script src="photo-sign.js?v=<?= time() ?>"></script>

    </body>
</html>