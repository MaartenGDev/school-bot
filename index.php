<?php
namespace App;

//header("Content-type: application/json");
$data = ['token' => "xoxp-31730314276-47553129539-85993043030-80e5d3b86526db733447a3d53eb8856f"];
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, "https://slack.com/api/groups.list");
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $data,
    CURLOPT_RETURNTRANSFER => true
]);
$result = curl_exec($curl);
//echo $result;

$roosterGroepen = json_decode($result);
$roosterGroepen = $roosterGroepen->groups;
foreach ($roosterGroepen as $key => $value) {
    if(!preg_match("`ltb`", $value->name)){
        unset($roosterGroepen[$key]);
    }
    echo $key."<br >";
}
var_dump($roosterGroepen);
//
//if (in_array($_POST['user_id'], $roosterGroepen)){
//
//}
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

