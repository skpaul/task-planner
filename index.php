<?php
  

    #region Import libraries
        require_once("Required.php");


        Required::Logger()->Cryptographer()
            ->Database()
            ->JSON()
            ->Clock();
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
?>

<!DOCTYPE html>
<html>
    <head>
        <title><?= ORGANIZATION_FULL_NAME ?></title>
        <?php
            Required::html5shiv()->omnicss()->sweetModalCSS();
        ?>
      
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
                       <div class="card">
                            <form action="app/validate-login.php" method="post">
                                <div class="field">
                                    <label for="">Login Name</label>
                                    <input type="text" name="loginName" class="validate" data-required="required">
                                </div>
                                <div class="field">
                                    <label for="">Password</label>
                                    <input type="password" name="loginPassword" class="validate" data-required="required">
                                </div>
                                
                                <input type="submit" value="Submit">
                            </form>
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
              
            </footer>
        </div>
        <?php
            Required::jquery()->sweetModalJS()->swiftSubmit();
        ?>
        <script>
            var base_url = '<?php echo BASE_URL; ?>';
            $(function() {
                $('form').swiftSubmit({}, null, null, null, null, null);

            }) //document.ready ends.
        </script>

    </body>

</html>