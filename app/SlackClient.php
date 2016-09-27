<?php

namespace app;


class SlackClient
{
    protected $days = [];

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

            $day = date('l', strtotime($lesson->start_date));

            $pretext = null;
            $title = null;

            if (!in_array($day, $this->days)) {
                $pretext = $day;
                $this->days[] = $day;
            }

            $firstTeacher = $lesson->lecturers ? $lesson->lecturers[0] : 'Empty';
            $firstRoom = $lesson->locations ? $lesson->locations[0]->building : 'Empty';

            $title = $lesson->long_name . ' - ' . $firstTeacher . ' - ' . $firstRoom;

            $color = $colors[array_search($day, $this->days)];

            $times = date('H:i', strtotime($lesson->start_date)) . ' ' . date('H:i', strtotime($lesson->end_date));

            return (object)[
                'fallback' => 'Required plain-text summary of the attachment.',
                'color' => $color,
                'pretext' => $pretext,
                'text' => '-',
                'fields' => [
                    'title' => $title,
                    'value' => $times,
                    'short' => false,
                ]
            ];
        }, $lessons);


        return ['attachments' => $message];
    }
}