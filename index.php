<?php
namespace App;

$data = ['token' => "xoxp-31730314276-47553129539-85993043030-80e5d3b86526db733447a3d53eb8856f"];
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://slack.com/api/groups.list");
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERAGENT => 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)']);
$result = curl_exec($curl);
var_dump($result);
/*
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

$week = $dayName === 'All' ?
    $client->getWeek($weekNumber) :
    $client->getDay($dayName, $weekNumber);

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

echo json_encode($slackClient->parse($week));*/

