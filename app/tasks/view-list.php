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
                tasks.images, 
                tasks.imagesType, 
                tasks.createdOn, 
                priorities.`name` AS priority
            FROM
                tasks
                INNER JOIN
                priorities
                ON 
		        tasks.priorityId = priorities.priorityId
         where assignedTo = $developerId AND taskStatusId <> 3 order by tasks.priorityId DESC, tasks.taskId ASC";
    $tasks = $db->selectMany($sql);

    $statuses = $db->selectMany("select * from task_statuses order by statusId");
    $developer = $db->selectSingle("select * from developers where developerId=$developerId");
?>

<!DOCTYPE html>
<html>

    <head>
        <title>Tasks List || <?= ORGANIZATION_SHORT_NAME ?></title>
        <?php
            Required::omnicss()->griddle()->sweetModalCSS()->airDatePickerCSS();
        ?>

        <style>
            .task-card{
                font-size: 14px;
                border: 1px solid #353b44;
                border-radius: 5px;
                padding: 10px;
                padding-bottom: 5px;
                margin-bottom: 20px;
            }
            .task-title{
                font-weight: 800;
                letter-spacing: 0.01247rem;
                /* font-size: 16px; */
            }

            .task-description{
                font-size: 14px;
                line-height: 1.5;
                padding-bottom: 9px;
            }

            .attachments{
                border-bottom: 1px solid #353b44;
                color:#353b44;
                font-size: 12px;
                padding-bottom: 9px;
                margin-bottom: 2px;
            }
            .attachments a, .attachments ol{
                color:#adbac7;
            }

            div.priority{
                font-size: 12px;
                display: flex;
                align-items: center;
            }

            .discussionForm, .statusForm{
                display: flex;
                align-items: center;
            }

            .priority-star{
                color: #f2982f;
                margin-left: 5px;
                font-size: 14px;
                line-height: 1px;
            }

            select{
                font-size: 12px !important;
                padding: 0px !important;
                margin-top: 0 !important;
                border: none !important;
                width: auto !important;
            }

            label{
                font-weight: normal !important;
                font-size: 12px !important;
            }
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

                <?php
                    if($developer->role == "admin"){
                        echo ' <a href="'.BASE_URL.'/app/tasks/progress.php?session-id='.$encSessionId .'">Show Progress</a>';
                    }
                ?>
                
               

                <div>
                    <a class="fg-muted flex align-items-center justify-content-end" href="<?= BASE_URL ?>/logout.php?session-id=<?= $encSessionId ?>">
                        <span class="m-icons">logout</span> Logout
                    </a>
                </div>

                    <div class="content">
                        <?php
                            foreach ($tasks as $task) {
                        ?>
                            <div class="task-card">
                                <div class="task-title"><?=$task->title?></div>
                                <div class="task-description"><?=nl2br($task->description)?></div>
                                <div class="attachments">
                                    <?php
                                        if(isset($task->images) && !empty($task->images)){
                                            $images = explode(',', $task->images);
                                            //imagesType
                                            if($task->imagesType == "link"){
                                                $sl=1;
                                                echo '<ol>';
                                                foreach ($images as $photo) {
                                                    echo '<li>';
                                                    echo '<a href="'. trim($photo).'" target="_blank">Attachment- '. $sl++ .'</a>';
                                                    echo '</li>';
                                                }
                                                echo '</ol>';
                                            }
                                            else{
                                                foreach ($images as $photo) {
                                                    echo ' <img src="'. trim($photo).'">';
                                                }
                                                
                                            }
                                        }
                                    ?>
                                </div>
                               
                                <div class="grid fr4-lg fr1-sm" style="font-size: 12px !important;">
                                    <div class="priority">
                                        Priority:  <span class="priority-star"><?=str_replace("*", "&bigstar;", $task->priority)?></span>
                                    </div>
                                    <div class="createdOn">
                                        <?php
                                            $created = $clock->toDate($task->createdOn);
                                            $now = $clock->toDate("now");
                                            $diff = $created->diff($now);
                                            // $diff = $diff->i;
                                            if($diff->y > 0){
                                                $created = "{$diff->y} years ago.";
                                            }
                                            else{
                                                if($diff->m > 0){
                                                    $created = "{$diff->m} months ago.";
                                                }
                                                else{
                                                    if($diff->d > 0){
                                                        $created = "{$diff->d} days ago.";
                                                    }
                                                    else{
                                                        if($diff->h > 0){
                                                            $created = "{$diff->h} hours ago.";
                                                        }
                                                        else{
                                                            if($diff->i > 0){
                                                                $created = "{$diff->i} minutes ago.";
                                                            }
                                                            else{
                                                                $created = "{$diff->s} seconds ago.";
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        ?>
                                        Created: <?=$created?>
                                    </div>
                                    <form class="discussionForm" action="<?=BASE_URL?>/app/tasks/set-discussion.php?session-id=<?=$encSessionId?>" method="post">
                                        <input type="hidden" name="taskId" value="<?=$crypto->encrypt((string) $task->taskId)?>">  
                                        <label for="">
                                            <input type="checkbox" name="isDiscussionRequired" value="1" <?=$task->isDiscussionRequired==1? "checked": "" ?> >&nbsp;Discussion required
                                        </label>  
                                    </form>
                                    <form class="statusForm" action="<?=BASE_URL?>/app/tasks/update-status.php?session-id=<?=$encSessionId?>" method="post">
                                        <input type="hidden" name="taskId" value="<?=$crypto->encrypt((string) $task->taskId)?>">
                                        <select style=" height: auto !important;" name="status" title="Current status of this task">
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
        <?php Required::jquery()->hamburgerMenu()->sweetModalJS()->airDatePickerJS()->moment()->swiftSubmit();?>

        <script src="<?= BASE_URL ?>/assets/plugins/jquery-ui/jquery-ui.min.js" ;></script>
       
        <script>
            $(document).ready(function(){
                let statusForm =  $('.statusForm');
                statusForm.swiftSubmit({},null,null, null, null, null);

                let discussionForm =  $('.discussionForm');
                discussionForm.swiftSubmit({},null,null, null, null, null);

                $('select[name="status"]').change(function(){
                   $(this).closest('form').submit();
                });

                $('input[name="isDiscussionRequired"]').change(function(){
                   $(this).closest('form').submit();
                });
            });
        </script>
    </body>
</html>