<?php 

    declare(strict_types=1);

    require_once("../../Required.php");

    Required::Logger()
        ->Database()->DbSession()->headerBrand()->applicantHeaderNav()
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
                tasks.attachments, 
                tasks.attachmentsType, 
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
            where isApproved=0 order by tasks.taskId DESC";
    $tasks = $db->selectMany($sql);
    $statuses = $db->selectMany("select * from task_statuses order by statusId");
?>

<!DOCTYPE html>
<html>

    <head>
        <title>Progress List || <?= ORGANIZATION_SHORT_NAME ?></title>
        <?php
            Required::omnicss()->griddle()->sweetModalCSS()->airDatePickerCSS()->favicon();
        ?>


        <style>
            .task-card{
                font-size: 14px;
                border: 1px solid #353b44;
                border-radius: 5px;
                padding: 10px;
                padding-bottom: 5px;
                margin-bottom: 50px;
                background-color: #222730;
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
                <?php
                    echo HeaderBrand::prepare(array("baseUrl"=>BASE_URL, "hambMenu"=>true));
                    echo ApplicantHeaderNav::prepare(array("baseUrl"=>BASE_URL, "role"=>"admin", "sid"=>$encSessionId ));
                ?>
            </header>

            <main class="main">
                <div class="container flex flex-wrap">
                    <div class="content">
                        Showing only un-approved tasks from newer to older
                        <?php
                            foreach ($tasks as $task) {
                        ?>

                            <div class="task-card">
                                <div class="task-title"><?=$task->title?></div>
                                <div class="task-description"><?=nl2br($task->description)?></div>
                                <div class="attachments">
                                    <?php
                                        if(isset($task->attachments) && !empty($task->attachments)){
                                            $attachments = explode(',', $task->attachments);
                                            //attachmentsType
                                            if($task->attachmentsType == "link"){
                                                $sl=1;
                                                echo '<ol>';
                                                foreach ($attachments as $attachment) {
                                                    echo '<li>';
                                                    echo '<a href="'. trim($attachment).'" target="_blank">Attachment- '. $sl++ .'</a>';
                                                    echo '</li>';
                                                }
                                                echo '</ol>';
                                            }
                                            else{
                                                foreach ($attachments as $attachment) {
                                                    echo ' <img src="'. trim($attachment).'">';
                                                }
                                                
                                            }
                                        }
                                    ?>
                                </div>
                               
                                <div class="grid fr4-lg fr1-sm" style="font-size: 12px !important;">
                                    <div>
                                        Assigned to: <?=$task->fullName?>
                                    </div>
                                    <div class="priority">
                                        Priority:  <span class="priority-star"><?=str_replace("*", "&bigstar;", $task->priority)?></span>
                                    </div>
                                    <form class="discussionForm" action="<?=BASE_URL?>/app/tasks/set-discussion.php?session-id=<?=$encSessionId?>" method="post">
                                        <input type="hidden" name="taskId" value="<?=$crypto->encrypt((string) $task->taskId)?>">  
                                        <label>
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
                                    <div>
                                        <?php
                                            $now = $clock->toString("now", DatetimeFormat::MySqlDate());
                                            if(isset($task->startedOn) && !empty($task->startedOn)){
                                                $startedOn = $clock->toString($task->startedOn, DatetimeFormat::MySqlDate());
                                                if($now == $startedOn){
                                                    //if both are on same date, show only time.
                                                    $startedOn = $clock->toString($task->startedOn, DatetimeFormat::BdTime());
                                                }
                                                else{
                                                    $startedOn = $clock->toString($task->startedOn, DatetimeFormat::BdDatetime());
                                                }
                                            }
                                            else{
                                                $startedOn = "";
                                            }

                                            if(isset($task->finishedOn) && !empty($task->finishedOn)){
                                                $finishedOn = $clock->toString($task->finishedOn, DatetimeFormat::MySqlDate());
                                                if($now == $finishedOn){
                                                    //if both are on same date, show only time.
                                                    $finishedOn = $clock->toString($task->finishedOn, DatetimeFormat::BdTime());
                                                }
                                                else{
                                                    $finishedOn = $clock->toString($task->finishedOn, DatetimeFormat::BdDatetime());
                                                }
                                            }
                                            else{
                                                $finishedOn = "";
                                            }
                                        ?>
                                        Started: <?=$startedOn?>
                                    </div>
                                    <div>
                                        Finished: <?=$finishedOn?>
                                    </div>
                                    <div>
                                        <form class="approvalForm" action="<?=BASE_URL?>/app/tasks/approve.php?session-id=<?=$encSessionId?>" method="post">
                                            <input type="hidden" name="taskId" value="<?=$crypto->encrypt((string) $task->taskId)?>">  
                                            <label>
                                                <input type="checkbox" name="isApproved" value="1" <?=$task->isApproved==1? "checked": "" ?> >&nbsp;Approve
                                            </label>  
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

        <?php Required::jquery()->sweetModalJS()->airDatePickerJS()->moment()->swiftSubmit()->hamburgerMenu(); ?>

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