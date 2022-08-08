<?php
    $text = urlencode("hello BULKSMS");
    $smsresult = file_get_contents("http://66.45.237.70/api.php?username=skpaul&password=New@2022$2022&number=8801711781878&message=$text");

?>