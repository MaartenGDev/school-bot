<?php
namespace App;
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use GuzzleHttp\Client as GuzzleClient;
use MaartenGDev\Cache;
use MaartenGDev\LocalDriver;

$dotenv = new Dotenv(__DIR__);
$dotenv->load();


$guzzle = new GuzzleClient();
$slackClient = new SlackClient();


$dir = $_SERVER['DOCUMENT_ROOT'] . '/cache/';
$storage = new LocalDriver($dir);

$cache = new Cache($storage, 15);
$client = new Client($guzzle, $slackClient, $cache);

$text = isset($_POST['text']) ? $_POST['text'] : '';
$dayAndWeek= $slackClient->parseDayAndWeek($text);

$week = $dayAndWeek->isWeek ?
    $client->getWeek($dayAndWeek->week) :
    $client->getDay($dayAndWeek->day, $dayAndWeek->week);


header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

echo json_encode($week);
