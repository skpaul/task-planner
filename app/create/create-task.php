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


?>

<!DOCTYPE html>
<html>
    <head>
        <title><?= ORGANIZATION_SHORT_NAME ?></title>
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
                <div class="container">
                

                    <!-- 
                        <nav class="left-nav">
                        <?php
                        // echo AdminLeftNav::CreateFor($roleCode, BASE_URL, $encSessionId);
                        ?>
                        </nav> 
                    -->

                    <div>
                        <a class="fg-muted flex ai-center jc-end" href="<?= BASE_URL ?>/logout.php?session-id=<?= $encSessionId ?>">
                            <span class="m-icons">logout</span> Logout
                        </a>
                    </div>

                    <div class="content">
                        <div class="card">
                            <p class="steps fg-muted">Step 6 of 6</p>
                            <form action="create-task-processor.php?action=<?= $encAction ?>&session-id=<?= $encSessionId ?>" method="post" enctype="multipart/form-data">
                                
                                <div class="field">
                                    <label for="">Title</label>
                                    <input type="text" name="title">
                                    <input type="text" name="description">
                                    <input type="text" name="assignedTo">
                                </div>
                                <section class="formSection">
                                    <!-- Photo upload starts -->
                                    <?php
                                    $photo_path = BASE_URL . "/assets/images/default-photo.jpg";
                                    $signature_path = BASE_URL . "/assets/images/default-signature.jpg";
                                   
                                    ?>
                                    <div class="field">
                                        <label class="required">Photo</label>
                                        <div class="instruction">Photo dimension must be 300X300 pixels and size less than 100 kilobytes.</div>
                                        
                                        <img name="ApplicantPhoto" id="ApplicantPhotoImage" src="<?= $photo_path; ?>" 
                                        style="width: 150px;" class="hidden">

                                        <label class="btn outline d-block mincontent mt025">
                                            <input type="file" title="Applicant's Photo" name="ApplicantPhoto" id="ApplicantPhoto" class="photo  " data-required="required" data-title="Applicant's Photo" accept="image/jpeg" style="display: none;">
                                        Select a photo
                                        </label>
                                    </div>
                                </section>
                                
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
        Required::jquery()->hamburgerMenu()->sweetModalJS()->airDatePickerJS()->moment()->swiftSubmit()->SwiftNumeric();
        ?>
        <script src="<?= BASE_URL ?>/assets/plugins/jquery-ui/jquery-ui.min.js" ;></script>
        <script src="photo-sign.js?v=<?= time() ?>"></script>

    </body>
</html>