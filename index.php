<?php
namespace App;
    /*timetable_id 2473 => roode, 8306 => stoter
    user_id = jelmer => U1DG93TFV, maarten => U0XMXB9SM
    */

//header("Content-type: application/json");

$_POST = [ 'text' => 'rooster ', 'user_id' => 'U1DG93TFV']; //Jelmer
//$_POST = [ 'text' => 'rooster ', 'user_id' => 'U0XMXB9SM']; //Maarten




require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Client as GuzzleClient;
use MaartenGDev\Cache;
use MaartenGDev\LocalDriver;

$dotenv = new Dotenv(__DIR__);
$dotenv->load();


$guzzle = new GuzzleClient();
$parser = new MyAventusParser();
$slackClient = new SlackClient();

$dir = $_SERVER['DOCUMENT_ROOT'] . '/cache/';
$storage = new LocalDriver($dir);

$cache = new Cache($storage, 15);
$client = new Client($guzzle, $parser, $cache);

$text = isset($_POST['text']) ? $_POST['text'] : '';
$dayAndWeek= $slackClient->parseDayAndWeek($text);

$week = $dayAndWeek->isWeek ?
    $client->getWeek($dayAndWeek->week) :
    $client->getDay($dayAndWeek->day, $dayAndWeek->week);


header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

echo json_encode($slackClient->parse($week));
