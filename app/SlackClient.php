<?php

namespace app;

use App\Lesson;

class SlackClient
{
    protected $days = [];
    protected $lessons = [];
    protected $colors;

    public function __construct() {
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
        $isWeek = false;

        $week = date('W');
        $today = date('l');
        $tomorrow = date('l', strtotime('+1 day'));
        $dayAfterTomorrow = date('l', strtotime('+2 day'));

        $day = 'Monday';
        $description = trim(strtolower($description));
        $dayName = $description;
        $dayAndWeek = explode(' ', $description);

        if (count($dayAndWeek) === 2) {
            $week = (int)$dayAndWeek[1];
            $dayName = $dayAndWeek[0];
        }

        $allWeekDays = ['all', '', 'week'];

        if (in_array($dayName, $allWeekDays)) {
            $isWeek = true;
        }

        $days = [
            'Monday' => ['monday', 'sunday', 'saturday', 'maandag', 'zaterdag', 'zondag', 'mendei', 'moandei', 'moanje', 'sneon', 'snein'],
            'Tuesday' => ['tuesday', 'dinsdag', 'tiisdei'],
            'Wednesday' => ['wednesday', 'woensdag', 'wansdy', 'woansdei'],
            'Thursday' => ['thursday', 'donderdag', 'tongersdei'],
            'Friday' => ['friday', 'vrijdag', 'freed'],
            'Today' => ['vandaag', 'today', 'hjoed'],
            'Tomorrow' => ['morgen', 'morge', 'morgu', 'morguh', 'moarn', 'tomorrow'],
            'DayAfterTomorrow' => ['overtomorrow', 'overmorgen', 'overmorge', 'overmorguh', 'oermoarn']
        ];

        $relativeTimes = ['Today' => $today, 'Tomorrow' => $tomorrow, 'DayAfterTomorrow' => $dayAfterTomorrow];

        foreach ($days as $key => $value) {
            if (in_array($dayName, $value)) {
                $day = $key;
                break;
            }
        }

        if (in_array($day, $relativeTimes)) {
            $day = $relativeTimes[$day];
        }

        return (object)['day' => $day, 'week' => $week, 'isWeek' => $isWeek];
    }

    public function parse($week)
    {
        $lessons = collect(json_decode($week));

        $message = $lessons->groupBy(function ($lesson) {
            return date('d-m', strtotime($lesson->start_date));
        })->map(function ($lessons) {
            return $lessons->groupBy('long_name')->map(function ($lessons) {
                return $lessons->map(function ($lesson) {
                    return new Lesson($lesson, $this->colors->shift());
                });
            })->flatten();
        });

        return [
            'attachments' => $message,
            'response_type' => 'Ephemeral',
            'text' => ''
        ];
    }
}
