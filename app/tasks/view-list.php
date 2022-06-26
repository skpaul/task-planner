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

    #region Validate query string
        try {
          
            $encSessionId = $validator->title("Session parameter")->get("session-id")->required()->validate();
        } catch (\ValidationException $exp) {
            die($json->fail()->message($exp->getMessage(). " Please login again.")->create());
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
            $session->continue((int) $sessionId);
           $developerId = $session->getData("devId");
        } catch (\SessionException $th) {
            die($json->fail()->message("Invalid session. Please login again.")->create());
        } catch (\Exception $exp) {
            die($json->fail()->message("Invalid session. Please login again.")->create());
        }
    #endregion

    $sql = "SELECT * from tasks where assignedTo = $developerId";
    $tasks = $db->selectMany($sql);

?>

<!DOCTYPE html>
<html>

    <head>
        <title><?= ORGANIZATION_SHORT_NAME ?></title>
        <?php
        Required::omnicss()->griddle()->sweetModalCSS()->airDatePickerCSS();
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
                    <a class="fg-muted flex align-items-center justify-content-end" href="<?= BASE_URL ?>/logout.php?session-id=<?= $encSessionId ?>">
                        <span class="m-icons">logout</span> Logout
                    </a>
                </div>

                    <div class="content">
                        <?php
                            foreach ($tasks as $task) {
                        ?>
                            <div class="card">
                                <h2><?=$task->title?></h2>
                                <p><?=$task->description?></p>
                                <img src="<?=BASE_URL?>/screenshots/<?=$task->imageName?>" alt="" srcset="">
                            </div><!-- card/ -->
                        <?php
                            }
                        ?>
                       
                    </div><!-- .content/ -->

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
        Required::jquery()->hamburgerMenu()->sweetModalJS()->airDatePickerJS()->moment()->swiftSubmit();
        
        ?>
        <script src="<?= BASE_URL ?>/assets/plugins/jquery-ui/jquery-ui.min.js" ;></script>
       

    </body>

</html>