<?php
namespace App;

class Lesson
{

    /**
     * @var string $color Color of next post
     * @var $fields       The fields
     */
    public $color = '';
    public $fields;
    public $pretext;

    protected $lesson;


    /**
     * Lesson constructor.
     *
     * @param object $lesson  A lesson object
     * @param array  $color   A color to use with the current lesson
     * @param string $pretext The pretext
     */
    public function __construct($lesson, $color, $pretext)
    {
        $this->color  = $color;
        $this->lesson = $lesson;

        $this->fields = collect(
            [
             (object) [
                       'title' => $this->getTitle(),
                       'value' => $this->getValue(),
                       'short' => false,
                      ],
            ]
        );

    }//end __construct()


    /**
     * Check if there is a pretext
     *
     * @return $this
     */
    public function hasPretext()
    {
        $this->pretext = date('l d-m-Y', strtotime($this->lesson->start_date));

        return $this;

    }//end hasPretext()


    /**
     * Get the lesson title
     *
     * @return string
     */
    public function getTitle()
    {
        return implode(
            [
             $this->lesson->long_name,
             $this->lesson->lecturers[0] ?? '-',
             ($this->lesson->locations[0] ?? (object) [])->building,
            ],
            ' - '
        );

    }//end getTitle()


    /**
     * Get the value of the lesson
     *
     * @return string
     */
    public function getValue()
    {
        return implode(
            [
             date('H:i', strtotime($this->lesson->start_date)),
             date('H:i', strtotime($this->lesson->end_date)),
            ],
            ' '
        );

    }//end getValue()


}//end class
