<?php
$text = urlencode("hello BULKSMS");

// $smsresult = file_get_contents("http://66.45.237.70/api.php?username=skpaul&password=New@2022$2022&number=8801711781878&message=$text");




$from = 'meeting@winbip.com';
// To send HTML mail, the Content-type header must be set
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
// Create email headers
$headers .= 'From: '.$from."\r\n".
            'Reply-To: '.$from."\r\n" .
            'X-Mailer: PHP/' . phpversion();

mail("skpaul@gmail.com", "Test", "This is body", $headers);


?>