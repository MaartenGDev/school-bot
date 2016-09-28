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
$parser = new MyAventusParser();
$slackClient = new SlackClient();

$dir = $_SERVER['DOCUMENT_ROOT'] . '/cache/';
$storage = new LocalDriver($dir);

$cache = new Cache($storage, 15);
$client = new Client($guzzle, $parser, $cache);

$weekNumber = date('W');

$text = isset($_POST['text']) ? $_POST['text'] : '';
$dayName = $slackClient->parseDay($text);

$week = $dayName === 'all' ?
    $client->getWeek($weekNumber) :
    $client->getDay($dayName, $weekNumber);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

echo json_encode($slackClient->parse($week));