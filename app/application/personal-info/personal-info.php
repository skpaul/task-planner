<?php
    declare(strict_types=1);

    #region Import libraries
        require_once("../../../Required.php");
        Required::Logger()
            ->Database()->DbSession()
            ->DataValidator()
            ->Cryptographer()
            ->HttpHeader()
            ->ExclusivePermission()->Clock()->headerBrand()->applicantHeaderNav()->footer()->Helpers();
    #endregion

    #region Variable declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);  //This must be in first position.
        $crypto = new Cryptographer(SECRET_KEY);
        $db = new Database(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
        $clock = new Clock();
        $validable = new DataValidator();
        $hasExclusivePermission = ExclusivePermission::hasPermission();
        $pageTitle = "Personal Information";
        $proceedAnyWayQueryString = "";
        if (isset($_GET[ExclusivePermission::$propName]) && !empty($_GET[ExclusivePermission::$propName])) {
            $proceedAnyWayQueryString = "&" . ExclusivePermission::$propName . "=" . ExclusivePermission::$propValue;
        }
    #endregion

    #region Validate query string
        try {
            //'action' parameter must be present in query string-
            $encAction = $validable->title("Request")->get("action")->required()->validate();
            //'session-id' parameter must be present in query string-
            $encSessionId = $validable->title("Request")->get("session-id")->required()->validate();
        } catch (\ValidationException $exp) {
            HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid request.");
        }

        $action = $crypto->decrypt($encAction);
        if (!$action) {
            HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Action parameter is invalid.");
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
            $registrationId = $session->getData("registrationId");
            $draftId =  $session->getData("draftId");
            $configId = $session->getData("configId");
        } catch (\SessionException $th) {
            HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid session.");
        }
    #endregion

    #region Configuration
        $sql = "SELECT title, circularFileName, applicationStartDatetime, applicationEndDatetime 
        FROM `post_configurations` 
        WHERE isActive = 1 AND configId=$configId";
    
        $postConfig = $db->selectSingle($sql);
    #endregion


    if(isset($registrationId)){
        //action must be preview/update
        if ($action !== "preview") {
            if ($action !== "update") {
                HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid action parameter.");
            }
        }
    }
    else{
        if ($action !== "create") {
            HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Invalid action parameter.");
        }
    }

    //Show existing personalInfo only if action=preview -   
    if ($action == "preview") {
        $sql = "SELECT `name`, father, mother, gender, dob, nationality, mobile, email, birthReg, nid, passport, hasAddress from lc_registration_cinfo WHERE registrationId=$registrationId";
        $personalInfo = $db->selectSingle($sql);
    }

    if ($action === "preview" || $action === "update") {
        $sql = "SELECT draftExpireDatetime, hasSubmittedFinally from lc_registration_cinfo WHERE registrationId=$registrationId";
        $result = $db->selectSingle($sql);
        $draftExpireDatetime = $clock->toDate($result->draftExpireDatetime);
        $now = $clock->toDate("now");
        if($now >  $draftExpireDatetime){
            HttpHeader::redirect(BASE_URL . "/sorry.php?msg=Draft validity expires. Please submit a new application.");
            die();
        }
        //prevent to go forward if finally submitted the application earlier-
        if($result->hasSubmittedFinally == 1){
            HttpHeader::redirect(BASE_URL . "/sorry.php?msg=You already submitted this application. ");
        }
    }

?>

<!DOCTYPE html>
<html>

    <head>
        <title><?= $pageTitle ?> - <?= ORGANIZATION_SHORT_NAME ?></title>
        <?php
        Required::gtag()->metaTags()->favicon()->omnicss()->griddle()->bootstrapGrid()->sweetModalCSS()->airDatePickerCSS();
        ?>

        <link href="<?= BASE_URL ?>/assets/plugins/jquery-ui/jquery-ui.min.css" rel="stylesheet">
        <link href="<?= BASE_URL ?>/assets/plugins/jquery-ui/jquery-ui.structure.min.css" rel="stylesheet">
        <link href="<?= BASE_URL ?>/assets/plugins/jquery-ui/jquery-ui.theme.min.css" rel="stylesheet">

        <style>
            .sweet-modal-content {
                color: black;
            }

            /* 34 39 46 */

            .sweet-modal-overlay {
                /* background: radial-gradient(at center, rgba(0,0,0,0.6) 0%, rgba(0,0,0,0.7) 100%); */
                background: radial-gradient(at center, rgba(34, 39, 46, 0.6) 0%, rgba(34, 39, 46, 0.7) 100%);
            }

            .error{
                border-color: red !important;
            }
        </style>

    </head>

    <body>
        <div class="master-wrapper">
            <header class="header">
                <?php
                echo HeaderBrand::prepare(array("baseUrl" => BASE_URL, "hambMenu" => true));
                echo ApplicantHeaderNav::prepare(array("baseUrl" => BASE_URL, "sid"=>$encSessionId));
                ?>
            </header>

            <main class="main">
                <div class="container">
                    <h1 class="page-title mt150"><?= $postConfig->title ?></h1>
                    <h2 class="fg-muted mt100 mb150"><?= $pageTitle ?></h2>

                  

                    <!-- 
                            <nav class="left-nav">
                            <?php
                            // echo AdminLeftNav::CreateFor($roleCode, BASE_URL, $encSessionId);
                            ?>
                            </nav> 
                            -->

                   
                <div>
                    <a class="fg-muted flex align-items-center justify-content-end" href="<?= BASE_URL ?>/logout.php?session-id=<?= $encSessionId ?>">
                        <span class="m-icons">logout</span> Logout
                    </a>
                </div>

                    <div class="content">
                        <div class="card">
                            <p class="steps fg-muted">Step 1 of 6</p>
                            <!-- Preview mode starts -->
                                <?php
                                if ($action === "preview") {
                                
                                    $editUrl = BASE_URL . "/app/application/personal-info/personal-info.php?session-id=$encSessionId&action=" . $crypto->encrypt("update");

                                    if($personalInfo->hasAddress){
                                        //If contact info already exists, "Continue" button sets to 'preview' mode
                                        $nextUrl = BASE_URL . "/app/application/address/address.php?session-id=$encSessionId&action=" . $crypto->encrypt("preview");
                                    }
                                    else{
                                        $nextUrl = BASE_URL . "/app/application/address/address.php?session-id=$encSessionId&action=" . $crypto->encrypt("create");
                                    }
                                ?>
                                    <section class="form">
                                        <div class="grid fr3-lg fr1-sm">
                                            <div class="field">
                                                <label>Name</label>
                                                <div class="textbox">
                                                    <?= $personalInfo->name ?>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label>Father</label>
                                                <div class="textbox">
                                                    <?= $personalInfo->father ?>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label>Mother</label>
                                                <div class="textbox">
                                                    <?= $personalInfo->mother ?>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label>Gender</label>
                                                <div class="textbox">
                                                    <?= $personalInfo->gender ?>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label>Date of Birth</label>
                                                <div class="textbox">
                                                    <?= $personalInfo->dob ?>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label>Nationality</label>
                                                <div class="textbox">
                                                    <?= $personalInfo->nationality ?>
                                                </div>
                                            </div>
                                            <div class="field">
                                                <label>Mobile No.</label>
                                                <div class="textbox">
                                                    <?= $personalInfo->mobile ?>
                                                </div>
                                            </div>
                                        
                                            <div class="field">
                                                <label>Email</label>
                                                <div class="textbox">
                                                    <?= $personalInfo->email ?>
                                                </div>
                                            </div>

                                            <div class="field">
                                                <label>Birth Reg.</label>
                                                <div class="textbox">
                                                    <?= $personalInfo->birthReg ?>
                                                </div>
                                            </div>

                                            <div class="field">
                                                <label>NID</label>
                                                <div class="textbox">
                                                    <?= $personalInfo->nid ?>
                                                </div>
                                            </div>

                                            <div class="field">
                                                <label>Passport</label>
                                                <div class="textbox">
                                                    <?= $personalInfo->passport ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt150">
                                            <a class="btn outline ph150" href="<?=$editUrl?>">Update <span class="fg-muted">Personal info</span></a>
                                            <a class="btn ph150 ml050" href="<?=$nextUrl?>">Next <span class="fg-muted">Contact info</span></a>
                                        </div>
                                    </section>
                                <?php
                                }
                                ?>
                            <!-- Preview mode ends //-->

                            <!-- Create/Update starts -->
                                <?php
                                if ($action == "create" || $action == "update") {
                                ?>
                                    
                                    <form action="personal-info-processor.php?action=<?=$encAction?>&session-id=<?= $encSessionId ?><?= $proceedAnyWayQueryString ?>" method="post" enctype="multipart/form-data">
                                        <section>
                                            <div class="grid fr1-sm">
                                                <div class="field fr4">
                                                    <label class="required">Name</label>
                                                    <input name="name" class="validate formControl ucase" type="text" value="" data-title="Name" data-required="required" data-lang="english" data-maxlen="50">
                                                </div><!-- Name // -->
                                                <div class="field fr4">
                                                    <label class="required">Father's Name</label>
                                                    <input name="father" class="validate formControl ucase" type="text" value="" data-title="Father's Name" data-required="required" data-lang="english" data-maxlen="50">
                                                </div><!-- Father Name // -->
                                                <div class="field fr4">
                                                    <label class="required">Mother's Name</label>
                                                    <input name="mother" class="validate formControl ucase" type="text" value="" data-required="required" data-lang="english" data-maxlen="50">
                                                </div>
                                                <div class="field fr4">
                                                    <label class="required">Gender</label>
                                                    <select name="gender" class="validate formControl" data-required="required">
                                                        <option value=""></option>
                                                        <option value="Male">Male</option>
                                                        <option value="Female">Female</option>
                                                    </select>
                                                </div>
                                                <div class="field fr4">
                                                    <label class="required">Date of Birth</label>
                                                    <input autocomplete="off" name="dob" class="validate swiftDate formControl" data-title="Date of Birth" data-required="required" data-datatype="date" type="text" autocomplete="off" value="">
                                                </div>

                                                <div class="field fr4">
                                                    <label class="required">Nationality</label>
                                                    <input name="nationality" class="validate formControl" data-title="Nationality" data-required="required" type="text" value="Bangladeshi" readonly>
                                                </div>

                                                <div class="field fr6">
                                                    <label class="required">Mobile No.</label>
                                                    <input name="mobile" class="validate swiftInteger" data-required="required" type="text" data-datatype="mobile">
                                                </div>

                                                <div class="field fr6">
                                                    <label class="">Email</label>
                                                    <input name="email" class="validate" type="text" data-datatype="email" data-maxlen="50">
                                                </div>
                                            </div>

                                            <div class="note" style="margin-top: 7px; margin-bottom: 0;">
                                                <strong>Note: </strong>You must provide Birth Certificate No. OR, NID No. OR, Passport No.
                                            </div>

                                            <!-- National identity type and number -->
                                            <div class="grid fr3-lg fr1-sm">
                                                <div class="field">
                                                    <label class="">Birth Certificate No.</label>
                                                    <input name="birthReg" class="validate swiftInteger formControl" type="text" data-required="optional" data-maxlen="20" maxlength="20">
                                                </div>
                                                <div class="field nidNo">
                                                    <label class="">NID No.</label>
                                                    <input name="nid" class="validate swiftInteger formControl" type="text" data-required="optional" data-maxlen="20" maxlength="20">
                                                </div>
                                                <div class="field">
                                                    <label class="">Passport No.</label>
                                                    <input name="passport" class="validate formControl" type="text" data-required="optional" data-maxlen="20" maxlength="20" maxlength="20">
                                                </div>
                                            </div><!-- National identity type and number// -->
                                        </section>
                                    
                                        <section>
                                            <div class="field">
                                                <label class="">&nbsp;</label>
                                                <div class="radio-group grid fr1-lg fr1-sm">
                                                    <label class="radio"><span>Save & go to next step (address)</span>
                                                        <input type="radio" checked="checked" value="continue" name="next">
                                                        <span class="checkmark"></span>
                                                    </label>
                                                    <label class="radio"><span>Save & Exit</span>
                                                        <input type="radio" value="exit" name="next">
                                                        <span class="checkmark"></span>
                                                    </label>
                                                </div>
                                            </div>
                                        </section>

                                        <div class="mt150">
                                            <input type="submit" class="form-submit-button" id="nextbtn" value="Submit">
                                        </div>
                                    </form>
                                    
                                <?php
                                }
                                ?>
                            <!-- Create/Update ends/ -->
                        </div><!-- card/ -->
                    </div><!-- .content/ -->

                    <!-- 
                            <aside style="display: flex; flex-direction: column;">
                                asdsdaf
                            </aside> 
                            -->
                </div><!-- .container// -->
            </main>
            <footer class="footer">
                <?= Footer::prepare() ?>
            </footer>
        </div>

        <script>
            var baseUrl = '<?php echo BASE_URL; ?>';
        </script>
        <?php
        Required::jquery()->hamburgerMenu()->sweetModalJS()->airDatePickerJS()->moment()->swiftSubmit()->SwiftNumeric();
        
        ?>
        <script src="<?= BASE_URL ?>/assets/plugins/jquery-ui/jquery-ui.min.js" ;></script>
        <script src="personal-info.js?v=<?= time() ?>"></script>

    </body>

</html>