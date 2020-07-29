#!/usr/bin/php -q
<?php

require __DIR__ . '/vendor/autoload.php';
set_time_limit(60);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
error_reporting(1);


use Pushok\AuthProvider;
use Pushok\Client;
use Pushok\Notification;
use Pushok\Payload;
use Pushok\Payload\Alert;

//change following 
$options = [
    'key_id' => 'XXXXXXXX',
    'team_id' => 'XXXXXXXXXX',
    'app_bundle_id' => 'org.sipco.softphone',
    'private_key_path' => __DIR__ . '/AuthKey_XXXXXX.p8',
    'private_key_secret' => null
];


if (isset($argc)) {

$mysqli = new mysqli("localhost","push_user","push_passwd","pushdb");

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
  exit;
}


$callerdest = $mysqli -> real_escape_string($argv[1]);
$callerid = $mysqli -> real_escape_string($argv[2]);
$callername = $mysqli -> real_escape_string($argv[3]);
$sql = "SELECT p_info,p_device,p_status,p_type,p_updated  FROM pushdb_pushkeys WHERE p_device = $callerdest";

if (!$result = $mysqli->query($sql)) {
exit;

}

if ($result->num_rows === 0) {

exit;
}

while ($pushkey  = $result->fetch_assoc()) {

$pkey =  $pushkey['p_info'];


//
$authProvider = AuthProvider\Token::create($options);

$alert = Alert::create()->setTitle("Call from $callerid");
$alert = $alert->setBody("$callername is Calling");

$payload = Payload::create()->setAlert($alert);


$payload->setSound('louder_ring.caf');

//add custom value to your notification, needs to be customized
//$payload->setCustomValue('key', 'value');

//sreekanth


$deviceTokens = ["$pkey"];

$notifications = [];
foreach ($deviceTokens as $deviceToken) {
    $notifications[] = new Notification($payload,$deviceToken);
}

$client = new Client($authProvider, $production = false);
$client->addNotifications($notifications);



$responses = $client->push(); // returns an array of ApnsResponseInterface (one Response per Notification)

foreach ($responses as $response) {
    $response->getApnsId();
    $response->getStatusCode();
    $response->getReasonPhrase();
    $response->getErrorReason();
    $response->getErrorDescription();
}






}

$result->free();
$mysqli->close();



}


else

{

  exit();
}



?>
