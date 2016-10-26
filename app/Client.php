<?php
namespace App;

use DateInterval;
use GuzzleHttp\ClientInterface;
use MaartenGDev\CacheInterface;

class Client
{
    protected $client;
    protected $parser;

    protected $baseUrl;

    protected $startDate;
    protected $endDate;

    protected $result;
    protected $cache;


    /**
     * Client constructor.
     *
     * @param ClientInterface $client The client interface
     * @param ParserInterface $parser The parser interface
     * @param CacheInterface  $cache  The cache interface
     */
    public function __construct(ClientInterface $client, ParserInterface $parser, CacheInterface $cache)
    {
        date_default_timezone_set('Europe/Amsterdam');

        $this->baseUrl = getenv('APP_SITE');
        $this->client  = $client;
        $this->parser  = $parser;
        $this->cache   = $cache;

    }//end __construct()


    /**
     * Build the website
     * url using the parameters.
     *
     * @return string
     */
    protected function getApi()
    {
        $query = http_build_query(
            [
             'start_date' => $this->startDate,
             'end_date'   => $this->endDate,
            ]
        );

        return $this->baseUrl.'?'.$query;

    }//end getApi()


    /**
     * Checks if there is already a cache entry
     * and gets the data if isn't a cache entry.
     *
     * @param int $week The week number
     *
     * @return $this
     */
    protected function getData($week)
    {

        $key = 'rooster'.$week;

        $cache = $this->cache->has(
            $key,
            function ($cache) use ($key) {
                return $this->result = $cache->get($key);
            }
        );

        if ($cache === true) {
            return $this;
        }

        $data = $this->client->request(
            'GET',
            $this->getApi(),
            [
             'headers' => [
                           'Cookie'       => 'laravel_session='.getenv('LOGIN_SESSION'),
                           'Cookie2'      => '$Version=1',
                           'accessToken'  => getenv('ACCESS_TOKEN'),
                           'language'     => getenv('LANGUAGE'),
                           'clientToken'  => getenv('CLIENT_TOKEN'),
                           'timetable_id' => $this->selectRooster(),
                          ],
            ]
        )->getBody();

        $this->cache->store($key, $data);

        $this->result = $this->cache->get('rooster'.$week);
        return $this;

    }//end getData()


    /**
     * Parse the html page returned using the parser.
     *
     * @return array
     */
    protected function parse()
    {
        return $this->parser->parse($this->result);

    }//end parse()


    /**
     * Gets the start data and end date for inputted week
     *
     * @param int $year The year to select
     * @param int $week The week to check
     *
     * @return array
     */
    protected function getStartAndEndForWeek($year, $week)
    {
        $startDate = new \DateTime();

        $startDate->setISODate(date('Y'), $week);
        $startDate->setTime(00, 46, 0);


        $start = $startDate->getTimestamp();

        $endDate = new \DateTime();
        $endDate->setISODate($year, $week);
        $endDate->setTime(23, 46, 59);
        $endDate->add(DateInterval::createFromDateString('this week this sunday'));

        $end = $endDate->getTimestamp();
        return [
                'start' => $start,
                'end'   => $end,
               ];

    }//end getStartAndEndForWeek()


    /**
     * Get the week by sending a post request.
     *
     * @param integer $week The week number
     * @param string  $year The year of the week.
     *
     * @return array
     */
    public function getWeek($week, $year = null)
    {
        if ($year === null) {
            $year = date('Y');
        }

        $dateRange = $this->getStartAndEndForWeek($year, $week);

        $this->startDate = $dateRange['start'];
        $this->endDate   = $dateRange['end'];

        return $this->getData($week)->parse();

    }//end getWeek()


    /**
     * Get the day
     *
     * @param int      $day  The day
     * @param int      $week The week
     * @param null|int $year The year
     *
     * @return array
     */
    public function getDay($day, $week, $year = null)
    {

        $data = $this->getWeek($week, $year);

        $weeks = array_filter(
            json_decode($data),
            function ($lesson) use ($day) {
                $dayName = date('l', strtotime($lesson->start_date));
                return strtolower($dayName) === strtolower($day);
            }
        );

        $this->result = json_encode(array_values($weeks));
        return $this->parse();

    }//end getDay()


    /**
     * Get the timetable id by slack group
     *
     * @return bool|string
     */
    public function selectRooster()
    {
        $jsonGroups = $this->client->request(
            'GET',
            'https://slack.com/api/groups.list',
            [
             'form_data' => [
                             'token' => getenv('API_TOKEN'),
                            ],
            ]
        );

        $roosterGroups = json_decode($jsonGroups->getBody());
        $roosterGroups = $roosterGroups->groups;

        foreach ($roosterGroups as $value) {
            $teacher = preg_match("`stoter`", $value->name) === true ? 'stoter' : 'roode';

            if (is_string($roosterId = $this->determineRoosterId($value->members, $teacher)) === true) {
                return $roosterId;
            }
        }

    }//end selectRooster()


    /**
     * Check if the current user is in the group
     *
     * @param array  $key Array with the group users
     * @param string $ltb The current LTB to evaluate
     *
     * @return bool|string
     */
    public function determineRoosterId($key, $ltb)
    {
        if (in_array($_POST['user_id'], $key) === true) {
            return $ltb === 'stoter' ? '8306' : '2473';
        }

        return false;

    }//end determineRoosterId()


}//end class
