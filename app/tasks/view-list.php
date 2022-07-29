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
                tasks.createdOn, 
                priorities.`name` AS priority
            FROM
                tasks
                INNER JOIN
                priorities
                ON 
		        tasks.priorityId = priorities.priorityId
         where assignedTo = $developerId AND isApproved=0 order by tasks.priorityId DESC, tasks.taskId ASC";
    $tasks = $db->selectMany($sql);

    $statuses = $db->selectMany("select * from task_statuses order by statusId");
    $developer = $db->selectSingle("select * from developers where developerId=$developerId");
?>

<!DOCTYPE html>
<html>

    <head>
        <title>Tasks List || <?= ORGANIZATION_SHORT_NAME ?></title>
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

            .discuss-not-required{
                color:#7d8591;
            }

            .discuss-required{
                color:#47b062;
            }

            .working{
                /* display: inline-block; */
                visibility: visible;
            }

            .not-working{
                /* display: inline-block; */
                visibility: hidden;
            }


            span.label.show-discuss-label{
                visibility: visible;
            }
            span.label.hide-discuss-label{
                visibility: hidden;
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
                    echo ApplicantHeaderNav::prepare(array("baseUrl"=>BASE_URL, "role"=>$developer->role, "sid"=>$encSessionId ));
                ?>
            </header>

            <main class="main">
                <div class="container">
                    <div class="content">
                        
                        <?php
                            if(count($tasks) == 0){
                                echo '<img src="../../assets/images/ship.png" alt="">';
                            }
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
                                    <div class="priority">
                                        Priority:  <span class="priority-star"><?=str_replace("*", "&bigstar;", $task->priority)?></span>
                                    </div>
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
                                                    if($diff->m == 1){
                                                        $created = "{$diff->m} month ago.";
                                                    }
                                                    else{
                                                        $created = "{$diff->m} months ago.";
                                                    }
                                                }
                                                else{
                                                    if($diff->d > 0){
                                                        if($diff->d == 1){
                                                            $created = "{$diff->d} day ago.";
                                                        }
                                                        else{
                                                            $created = "{$diff->d} days ago.";
                                                        }
                                                    }
                                                    else{
                                                        if($diff->h > 0){
                                                            if($diff->h == 1){
                                                                $created = "{$diff->h} hour ago.";
                                                            }
                                                            else{
                                                                $created = "{$diff->h} hours ago.";
                                                            }
                                                        }
                                                        else{
                                                            if($diff->i > 0){
                                                                if($diff->i == 0){
                                                                    $created = "{$diff->i} minute ago.";
                                                                }
                                                                else{
                                                                    $created = "{$diff->i} minutes ago.";
                                                                }

                                                            }
                                                            else{
                                                                $created = "{$diff->s} seconds ago.";
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        ?>
                                    <div class="createdOn flex ai-center" title="Created on">
                                        <span class="m-icons">schedule</span> <?=$created?>
                                    </div>
                                    <form class="discussionForm" action="<?=BASE_URL?>/app/tasks/set-discussion.php?session-id=<?=$encSessionId?>" method="post">
                                        <input type="hidden" name="taskId" value="<?=$crypto->encrypt((string) $task->taskId)?>">  
                                            <?php
                                                if($task->isDiscussionRequired==1){
                                                    $iconCss = "discuss-required";
                                                    $labelCss = "show-discuss-label";
                                                }
                                                else{
                                                    $iconCss = "discuss-not-required";
                                                    $labelCss = "hide-discuss-label";
                                                }
                                            ?>
                                        <label class="flex ai-center" style="cursor: pointer ;" title="Required any discussion ?">
                                            <span class="m-icons <?=$iconCss?>">contact_support</span>
                                            <input style="display: none;" type="checkbox" name="isDiscussionRequired" value="1" <?=$task->isDiscussionRequired==1? "checked": "" ?>><span class="label <?=$labelCss?>">Discussion required</span>
                                        </label>  
                                    </form>
                                    <form class="statusForm" action="<?=BASE_URL?>/app/tasks/update-status.php?session-id=<?=$encSessionId?>" method="post">
                                                <?php
                                                 if($task->taskStatusId == 2){
                                                    $iconCss = "working";
                                                 }
                                                 else{
                                                    $iconCss = "not-working";
                                                 }
                                                ?>
                                        <img class="workingIcon <?=$iconCss?>" src="working.gif" style="height: 18px;">
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

                setTimeout(function(){
                    location.reload();
                }, 1000 * 15); //refresh every 15 minutes.

                function onSuccess(response) {
                    //do nothing.
                }
                let statusForm =  $('.statusForm');
                statusForm.swiftSubmit({},null,null, onSuccess, null, null);

                let discussionForm =  $('.discussionForm');
                discussionForm.swiftSubmit({},null,null, onSuccess, null, null);

                $('select[name="status"]').change(function(){
                   $(this).closest('form').submit();
                   let selectedVal = $(this).val();
                   if(selectedVal == 2){
                        $(this).closest('form').find('.workingIcon').removeClass("not-working").addClass("working");
                   }
                   else{
                        $(this).closest('form').find('.workingIcon').removeClass("working").addClass("not-working");
                   }
                });


                $('input[name="isDiscussionRequired"]').change(function(){
                   $(this).closest('form').submit();
                   let isChecked = $(this).is(':checked');
                   if(isChecked){
                        $(this).closest("label").find("span.m-icons").removeClass("discuss-not-required").addClass("discuss-required");
                        $(this).closest("label").find("span.label").removeClass("hide-discuss-label").addClass("show-discuss-label");
                   }
                   else{
                        $(this).closest("label").find("span.m-icons").removeClass("discuss-required").addClass("discuss-not-required");
                        $(this).closest("label").find("span.label").removeClass("show-discuss-label").addClass("hide-discuss-label");
                   }
                });
            });
        </script>
    </body>
</html>