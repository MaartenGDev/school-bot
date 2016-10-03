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

    public function __construct(ClientInterface $client, ParserInterface $parser, CacheInterface $cache)
    {
        date_default_timezone_set('Europe/Amsterdam');

        $this->baseUrl = getenv('APP_SITE');
        $this->client = $client;
        $this->parser = $parser;
        $this->cache = $cache;
    }

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
                'end_date' => $this->endDate,
            ]
        );

        return $this->baseUrl . '?' . $query;
    }


    /**
     * Checks if there is already a cache entry
     * and gets the data if isn't a cache entry.
     *
     * @param array $data The form data
     * @param int $week The week number
     * @return $this
     */
    protected function getData($week)
    {

        $key = 'rooster' . $week;

        $cache = $this->cache->has($key, function ($cache) use ($key) {
            return $this->result = $cache->get($key);
        });

        if ($cache) {
            return $this;
        }

        $data = $this->client->request(
            'GET',
            $this->getApi(),
            [
                'headers' => [
                    'Cookie' => 'laravel_session=' . getenv('LOGIN_SESSION'),
                    'Cookie2' => '$Version=1',
                    'accessToken' => getenv('ACCESS_TOKEN'),
                    'language' => getenv('LANGUAGE'),
                    'clientToken' => getenv('CLIENT_TOKEN'),
                    'timetable_id' => $this->selectRooster()
                ]
            ]
        )->getBody();

        $this->cache->store($key, $data);

        $this->result = $this->cache->get('rooster' . $week);
        return $this;
    }

    /**
     * Parse the html page returned using the parser.
     *
     * @return array
     */
    protected function parse()
    {
        return $this->parser->parse($this->result);
    }

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
        return ['start' => $start, 'end' => $end];
    }

    /**
     * Get the week by sending a post request.
     *
     * @param integer $week The week number
     * @param string $year The year of the week.
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
        $this->endDate = $dateRange['end'];

        return $this->getData($week)->parse();

    }

    public function getDay($day, $week, $year = null)
    {

        $data = $this->getWeek($week, $year);

        $weeks = array_filter(json_decode($data), function ($lesson) use ($day) {
            $dayName = date('l', strtotime($lesson->start_date));
            return strtolower($dayName) === strtolower($day);
        });

        $this->result = json_encode(array_values($weeks));
        return $this->parse();
    }

    /**
     * Get the timetable id by slack group
     *
     * @return bool|string
     */
    public function selectRooster()
    {
        $jsonGroups = $this->client->request('GET', 'https://slack.com/api/groups.list', [
            'form_data' => [
                'token' => getenv('API_TOKEN')
            ]
        ]);

        $roosterGroups = json_decode($jsonGroups->getBody());
        $roosterGroups = $roosterGroups->groups;

        foreach ($roosterGroups as $key => $value) {
            $teacher = preg_match("`stoter`", $value->name) ? 'stoter' : 'roode';

            if (is_string($roosterId = $this->determineRoosterId($value->members, $teacher))) {
                return $roosterId;
            }
        }
    }

    /**
     * Check if the current user is in the group
     *
     * @param array $key Array with the group users
     * @param string $ltb The current LTB to evaluate
     *
     * @return bool|string
     */
    public function determineRoosterId($key, $ltb)
    {
        if (in_array($_POST['user_id'], $key)) {
            return $ltb == 'stoter' ? '8306' : '2473';
        }

        return false;
    }
}
