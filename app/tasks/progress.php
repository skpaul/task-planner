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

    $sql = "SELECT
                tasks.taskId,
                tasks.title, 
                tasks.description, 
                tasks.isDiscussionRequired,
                tasks.taskStatusId, 
                tasks.imageName, 
                priorities.`name` AS priority,
                developers.fullName,
                tasks.isApproved, 
                tasks.createdOn, 
                tasks.startedOn, 
                tasks.finishedOn
            FROM
                tasks
                INNER JOIN
                priorities
                ON 
                    tasks.priorityId = priorities.priorityId
                INNER JOIN
                developers
                ON 
		        tasks.assignedTo = developers.developerId
            where isApproved=0 order by tasks.priorityId";
    $tasks = $db->selectMany($sql);
    $statuses = $db->selectMany("select * from task_statuses order by statusId");
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
                <div class="container-fluid flex flex-wrap">
                        <nav class="left-nav">
                            <?php
                                require_once(ROOT_DIRECTORY . '/inc/AdminLeftNav.php');
                                echo AdminLeftNav::CreateFor("superadmin", BASE_URL, $encSessionId);
                            ?>
                        </nav> 

                    <div class="content">
                        <div>
                            <a class="fg-muted flex align-items-center justify-content-end" href="<?= BASE_URL ?>/logout.php?session-id=<?= $encSessionId ?>">
                                <span class="m-icons">logout</span> Logout
                            </a>
                        </div>

                        <?php
                            foreach ($tasks as $task) {
                        ?>
                            <div class="card">
                                <h2><?=$task->title?></h2>
                                <p><?=$task->description?></p>
                               
                               
                                <div class="grid fr4">
                                    <div>
                                        Dev: <?=$task->fullName?>
                                    </div>
                                    <div>
                                        Priority: <?=$task->priority?>
                                    </div>
                                    <form class="discussionForm" action="<?=BASE_URL?>/app/tasks/set-discussion.php?session-id=<?=$encSessionId?>" method="post">
                                        <input type="hidden" name="taskId" value="<?=$crypto->encrypt((string) $task->taskId)?>">    
                                        <input type="checkbox" name="isDiscussionRequired" value="1" <?=$task->isDiscussionRequired==1? "checked": "" ?> > Discussion required
                                    </form>
                                    <form class="statusForm" action="<?=BASE_URL?>/app/tasks/update-status.php?session-id=<?=$encSessionId?>" method="post">
                                        <input type="hidden" name="taskId" value="<?=$crypto->encrypt((string) $task->taskId)?>">
                                        <select name="status" title="Current status of this task">
                                            <?php
                                                foreach ($statuses as $status) {
                                            ?>
                                                <option value="<?=$status->statusId?>" <?=$status->statusId == $task->taskStatusId ? "selected": ""?> ><?=$status->statusName?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>
                                    </form>
                                </div>

                                <div class="grid fr4">
                                    <div>
                                        Created: <?=$task->createdOn?>
                                    </div>
                                    <div>
                                        Started: <?=$task->startedOn?>
                                    </div>
                                    <div>
                                        Finished: <?=$task->finishedOn?>
                                    </div>
                                    <div>
                                        <form class="approvalForm" action="<?=BASE_URL?>/app/tasks/approve.php?session-id=<?=$encSessionId?>" method="post">
                                            <input type="hidden" name="taskId" value="<?=$crypto->encrypt((string) $task->taskId)?>">    
                                            <input type="checkbox" name="isApproved" value="1" <?=$task->isApproved==1? "checked": "" ?> > Approve
                                        </form>
                                    </div>
                                </div>
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

        <?php Required::jquery()->sweetModalJS()->airDatePickerJS()->moment()->swiftSubmit()->leftNavJS(); ?>

        <script src="<?= BASE_URL ?>/assets/plugins/jquery-ui/jquery-ui.min.js";></script>
       
        <script>
            $(document).ready(function(){
                let statusForm =  $('.statusForm');
                statusForm.swiftSubmit({},null,null, null, null, null);

                let discussionForm =  $('.discussionForm');
                discussionForm.swiftSubmit({},null,null, null, null, null);

                let approvalForm =  $('.approvalForm');
                approvalForm.swiftSubmit({},null,null, null, null, null);

                $('select[name="status"]').change(function(){
                   $(this).closest('form').submit();
                });

                $('input[name="isDiscussionRequired"]').change(function(){
                   $(this).closest('form').submit();
                });

                $('input[name="isApproved"]').change(function(){
                   $(this).closest('form').submit();
                });
            });
        </script>
    </body>
</html>