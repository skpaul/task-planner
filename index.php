<?php
  

    #region Import libraries
        require_once("Required.php");


        Required::Logger()->Cryptographer()
            ->Database()
            ->JSON()
            ->Clock()->headerBrand()->applicantHeaderNav()->footer();
    #endregion

	#region Library instance declaration & initialization
        $logger = new Logger(ROOT_DIRECTORY);
        $crypto = new Cryptographer(SECRET_KEY);
        $clock = new Clock();
        $db = new Database(DB_SERVER, DB_NAME, DB_USER, DB_PASSWORD);
    #endregion

	#region Database connection
        $db->connect();
        $db->fetchAsObject();
	#endregion

    $sql = "SELECT configId, title, circularFileName, applicationStartDatetime, applicationEndDatetime 
                FROM `post_configurations` 
                WHERE isActive = 1 AND court='". COURT ."' AND applicationType = '". APPLICATION_TYPE ."' 
                ORDER BY configId ASC";
                
    $postConfigs = $db->selectMany($sql);

    $sql = "SELECT * FROM `notice_boards` 
            WHERE isActive = 1 AND court='". COURT ."' AND applicationType = '". APPLICATION_TYPE ."' 
            ORDER BY noticeId DESC";
    $notices = $db->selectMany($sql);

    $pageTitle = "Pupilage Registration";
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?=$pageTitle?> - <?= ORGANIZATION_FULL_NAME ?></title>
        <?php
            Required::gtag()->html5shiv()->metaTags()->favicon()->omnicss()->sweetModalCSS();
        ?>

        <style>
            /* .notice-board {
                display: flex;
                flex-direction: row;
                margin-bottom: 41px;
            }

            .notice-board>.label {
                margin-right: 32px;
                font-weight: 600;
                border: 1px solid #3d4e5e;
                padding: 1px 5px;
                border-radius: 5px;
            }

            #ticker a {
                display: none;
            } */
        </style>

        <style>
            body {
                /* background-color: #F8F9FA; */
                background-image: url('assets/images/corners/corners-4/corner-4-right-bottom.png');
                background-position-x: right;
                background-position-y: bottom;
                background-repeat: no-repeat;
                background-size: contain;
            }

            marquee {
                border-radius: 20px;
                border: 1px solid transparent;
            }

            marquee:hover {
                background-color: #2d333b;
                border: 1px solid #bfbfbf;
            }

            .marquee-items {
                display: flex;
                flex-direction: row;
                list-style-position: inside;
                padding: 10px;
            }

            .marquee-items li {
                margin-right: 20px;
                color:#FFF;
            }

            .marquee-items li>a {
              
                color:#FFF;
            }

            .card-links a:not(:first-child) {
                margin-left: 10px;
            }

            .card-links a:hover {
                background-color: #ededed;
            }
        </style>

        <style>
            /* Override sweet-modal color */
            .sweet-modal-content{
                color: black;
            }

            .sweet-modal-overlay {
                background: radial-gradient(at center, rgba(255, 255, 255, 0.84) 0%, rgba(255, 255, 255, 0.96) 100%);
            }
        </style>
    </head>

    <body>
        <div class="master-wrapper">
            <header>
                <?php
                    echo HeaderBrand::prepare(array("baseUrl"=>BASE_URL, "hambMenu"=>true));
                    echo ApplicantHeaderNav::prepare(array("baseUrl"=>BASE_URL));
                ?>
            </header>
            <main>
                <div class="container">
                    <!-- 
                    <nav class="left-nav">
                    <?php
                        // echo AdminLeftNav::CreateFor($roleCode, BASE_URL, $encSessionId);
                        ?>
                    </nav> 
                    -->

                    <div class="content">
                        <marquee class="mt150" id="marquee" behavior="" direction="left" scrollamount="5">
                            <ul class="marquee-items">
                                <?php
                                if (count($notices) > 0) {
                                    foreach ($notices as $notice) {
                                        if (isset($notice->noticeFileName) && !empty($notice->noticeFileName)) {
                                            $fileUrl = BASE_URL . "/notice-board/notice-files/" . $notice->noticeFileName;
                                            $div = <<<HTML
                                                <li><a href="$fileUrl" target="_blank">$notice->title</a></li>
                                                
                                            HTML;
                                        } else {
                                            $div = <<<HTML
                                                <li>$notice->title</li>
                                            HTML;
                                        }
                                        echo $div;
                                    }
                                }
                                ?>
                            </ul>
                        </marquee>

                        <h1 class="page-title mt150 ta-center"><?= $pageTitle ?></h1>
                        <h2 class="page-subtitle mt050 mb150 ta-center">Home</h2>

                        <div class="ta-center">
                            <?php
                            if (count($postConfigs) == 0) {
                                echo "No application.";
                            }
                            foreach ($postConfigs as $post) {
                            ?>
                                <div class="card ta-center fit-content pa300">
                                    <h3 class="mb150"><?= "Pupilage Registration" ?></h3>
                                    <div class="mb300">
                                        <!-- <span class="fw600">Apply datetime: </span> -->
                                        <?= "" //$clock->toString($post->applicationStartDatetime, DatetimeFormat::Custom("M d, Y h:i A")) ?> 
                                            <!-- to -->
                                        <?= "" // $clock->toString($post->applicationEndDatetime, DatetimeFormat::Custom("M d, Y h:i A")) ?>

                                    </div>
                                    <div>
                                        <a class="fg-default btn outline " href="circular-files/<?= $post->circularFileName ?>" target="_blank">Download Circular</a>
                                        <a class="fg-default btn" href="<?=BASE_URL?>/app/application/welcome.php?config-id=<?= $crypto->encrypt( $post->configId) ?>">Apply</a>
                                    </div>

                                    <!-- <br><br>
                                    How to apply: Bangla Video Tutorial
                                    <iframe width="100%" height="235" src="https://www.youtube.com/embed/ddJG6_VX2Ts" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> -->
                                </div>
                            <?php
                            }
                            ?>


                        </div>
                    </div><!-- .content -->

                    <!-- 
                    <aside style="display: flex; flex-direction: column;">
                        asdsdaf
                    </aside> 
                    -->

                </div><!-- .container -->
            </main>
            <footer>
                <?php
                echo Footer::prepare(array());
                ?>
            </footer>
        </div>
        <?php
            Required::jquery()->hamburgerMenu();
        ?>
        <script>
            var base_url = '<?php echo BASE_URL; ?>';
            $(function() {
                $('#marquee').mouseover(function() {
                    this.setAttribute('scrollamount', 0, 0);
                    $(this).trigger('stop');
                }).mouseout(function() {
                    this.setAttribute('scrollamount', 5, 0);
                    $(this).trigger('start');
                });


                function fades($div, cb) {
                    $div.fadeIn(300, function() {
                        myTimeout = setTimeout(function() {
                            $div.fadeOut(400, function() {
                                var $next = $div.next();
                                if ($next.length > 0) {
                                    fades($next, cb);
                                } else {
                                    // The last element has faded away, call the callback
                                    cb();
                                }
                            }); //fadeout ends


                        }, 2000); //setTimfeout ends
                    });
                }





                function startFading($firstDiv) {
                    fades($firstDiv, function() {
                        startFading($firstDiv);
                    });
                }

                startFading($(".a:first-child"));

            }) //document.ready ends.
        </script>

    </body>

</html>