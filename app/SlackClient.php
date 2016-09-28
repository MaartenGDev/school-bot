<?php

namespace app;


class SlackClient
{
    protected $days = [];
    protected $lessons = [];

    /**
     * Parses the name and returns
     * the correct value with fallback.
     *
     * @param string $dayName The name of the day.
     *
     * @return string
     */
    public function parseDay($dayName)
    {
        $today = date('l');
        $tomorrow = date('l', strtotime('+1 day'));
        $dayAfterTomorrow = date('l', strtotime('+2 day'));

        $days = [
            'Monday' => 'Monday',
            'Tuesday' => 'Tuesday',
            'Wednesday' => 'Wednesday',
            'Thursday' => 'Thursday',
            'Friday' => 'Friday',
            'Saturday' => 'Monday',
            'Sunday' => 'Monday',
            'Maandag' => 'Monday',
            'Dinsdag' => 'Tuesday',
            'Woensdag' => 'Wednesday',
            'Donderdag' => 'Thursday',
            'Vrijdag' => 'Friday',
            'Zaterdag' => 'Monday',
            'Zondag' => 'Monday',
            'Vandaag' => $today,
            'Morgen' => $tomorrow,
            'Overmorgen' => $dayAfterTomorrow,
            'Today' => $today,
            'Tomorrow' => $tomorrow,
            'Overtomorrow' => $dayAfterTomorrow
        ];
        return array_key_exists($dayName, $days) ? $days[$dayName] : 'Monday';
    }

    public function parse($week)
    {

        $colors = [
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
        ];


        $lessons = json_decode($week);


        $message = array_map(function ($lesson) use ($colors) {

            $day = date('l d-m-Y', strtotime($lesson->start_date));

            $pretext = null;
            $title = null;

            if (!in_array($day, $this->days)) {
                $pretext = $day;
                $this->days[] = $day;
            }

            if (!in_array($lesson->long_name, $this->lessons)) {
                $this->lessons[] = $lesson->long_name;
            }

            $firstTeacher = $lesson->lecturers ? $lesson->lecturers[0] : '-';
            $firstRoom = $lesson->locations ? $lesson->locations[0]->building : '-';

            $title = $lesson->long_name . ' - ' . $firstTeacher . ' - ' . $firstRoom;

            $color = $colors[array_search($lesson->long_name, $this->lessons)];

            $times = date('H:i', strtotime($lesson->start_date)) . ' ' . date('H:i', strtotime($lesson->end_date));

            return (object)[
                'fallback' => 'Required plain-text summary of the attachment.',
                'color' => $color,
                'pretext' => $pretext,
                'text' => '',
                'start_date' => $lesson->start_date,
                'end_date' => $lesson->end_date,
                'fields' => [
                    (object)[
                        'title' => $title,
                        'value' => $times,
                        'short' => false
                    ]
                ]
            ];
        }, $lessons);


        return ['attachments' => $message];
    }
}