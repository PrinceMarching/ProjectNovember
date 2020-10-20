
<?php
// this currently installed at
// https://onehouronelife.com/testCert/test.php
// amazon doesn't like letsencrypt SSL certificates


$fullRequest = file_get_contents( 'php://input' );



$httpArray = array(
                'header'  =>
                "Connection: close\r\n".
                "Content-type: application/json\r\n".
                "Content-Length: " . strlen($fullRequest) . "\r\n",
                'method'  => 'POST',
                'protocol_version' => 1.1,
                'content' => $fullRequest );

$options = array( 'http' => $httpArray );

$context  = stream_context_create( $options );

$result = file_get_contents( "https://projectdecember.net/novemberServer/server.php?action=alexa_chat",
                             false, $context );

echo $result;
?>