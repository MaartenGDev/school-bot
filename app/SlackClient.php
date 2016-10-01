<?php

namespace app;

use App\Lesson;

class SlackClient
{
    protected $colors;

    public function __construct() {
        $this->translations = collect([
            'Monday' => ['monday', 'sunday', 'saturday', 'maandag', 'zaterdag', 'zondag', 'mendei', 'moandei', 'moanje', 'sneon', 'snein'],
            'Tuesday' => ['tuesday', 'dinsdag', 'tiisdei'],
            'Wednesday' => ['wednesday', 'woensdag', 'wansdy', 'woansdei'],
            'Thursday' => ['thursday', 'donderdag', 'tongersdei'],
            'Friday' => ['friday', 'vrijdag', 'freed'],
            'Today' => ['vandaag', 'today', 'hjoed'],
            '+1 day' => ['morgen', 'morge', 'morgu', 'morguh', 'moarn', 'tomorrow'],
            '+2 day' => ['overtomorrow', 'overmorgen', 'overmorge', 'overmorguh', 'oermoarn']
        ]);

        $this->week = collect(['all', 'week']);

        $this->translations->transform('collect');
    }

    public function resetColors()
    {
        $this->colors = collect([
            '#1abc9c',
            '#2ecc71',
            '#3498db',
            '#9b59b6',
            '#34495e',
            '#16a085',
            '#27ae60',
            '#16a085',
            '#2980b9',
            '#8e44ad',
            '#2c3e50',
            '#f1c40f'
        ]);
    }

    /**
     * Parses the name and returns
     * the correct value with fallback.
     *
     * @param string $dayName The name of the day.
     *
     * @return string
     */
    public function parseDayAndWeek($description)
    {
        $week = date('W');
        $today = date('l');

        $dayAndWeek = explode(' ', trim(strtolower($description)));

        $day = $dayAndWeek[0];
        if (count($dayAndWeek) === 2) {
            $week = (int)$dayAndWeek[1];
        }

        $isWeek = $this->week->search($day) === true || $day === '';

        $relativeTimes = collect(['Today' => $today]);

        $day = $this->translations->search(function($translations) use ($day) {
            return !($translations->search($day) === false);
        }) ?: 'Monday';

        $day = $relativeTimes->get($day, $day);

        return (object)['day' => $day, 'week' => $week, 'isWeek' => $isWeek];
    }

    /**
     * Parses the week and returns the items in the slack requested format
     *
     * @param  string $week JSON string
     *
     * @return array
     */
    public function parse($week)
    {
        $message = collect(json_decode($week))->groupBy(function ($lesson) {
            return date('d-m', strtotime($lesson->start_date));
        })->map(function ($lessons) {
            $this->resetColors();

            return $lessons->groupBy('long_name')->map(function ($lessons) {
                return $lessons->map(function ($lesson) {
                    return new Lesson($lesson, $this->colors->shift(), false);
                });
            })->flatten();
        })->map(function ($lessons) {
            return $lessons->put(0, $lessons->first()->hasPretext());
        })->flatten();

        return [
            'attachments' => $message->values(),
            'response_type' => 'Ephemeral',
            'text' => ''
        ];
    }
}
