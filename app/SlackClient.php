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
    public function parseDayAndWeek($description)
    {
        $isWeek = false;
        $week = date('W');
        $description = trim(strtolower($description));

        if(count(explode(' ', $description)) === 2){
            $isWeek = true;
            $week = (int) explode(' ', $description)[1];
        }

        $dayName = $isWeek ? $description : explode(' ', $description)[0];
        $today = date('l');
        $tomorrow = date('l', strtotime('+1 day'));
        $dayAfterTomorrow = date('l', strtotime('+2 day'));

        $days = [
            '' => 'all',
            'all' => 'all',
            'week' => 'week',
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Monday',
            'sunday' => 'Monday',
            'maandag' => 'Monday',
            'dinsdag' => 'Tuesday',
            'woensdag' => 'Wednesday',
            'donderdag' => 'Thursday',
            'vrijdag' => 'Friday',
            'zaterdag' => 'Monday',
            'zondag' => 'Monday',
            'vandaag' => $today,
            'morgen' => $tomorrow,
            'morgu' => $tomorrow,
            'morge' => $tomorrow,
            'overmorgen' => $dayAfterTomorrow,
            'overmorge' => $dayAfterTomorrow,
            'today' => $today,
            'tomorrow' => $tomorrow,
            'overtomorrow' => $dayAfterTomorrow,
            'mendei' => 'Monday',
            'moandei' => 'Monday',
            'moanje' => 'Monday',
            'tiisdei' => 'Tuesday',
            'wansdy' => 'Wednesday',
            'woansdei' => 'Wednesday',
            'tongersdei' => 'Thursday',
            'freed' => 'Friday',
            'sneon' => 'Monday',
            'saterje' => 'Monday',
            'snein' => 'Monday',
            'hjoed' => $today,
            'moarn' => $tomorrow,
            'oermoarn' => $dayAfterTomorrow
        ];
        $day = array_key_exists($dayName, $days) ? $days[$dayName] : 'Monday';
        return (object) ['day' => $day,'week' => $week,'isWeek' => $isWeek];
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


        return [
            'attachments' => $message,
            'response_type' => 'Ephemeral',
            'text' => ''
        ];
    }
}