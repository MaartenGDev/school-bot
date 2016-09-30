<?php
namespace App;

class Lesson
{
	public $color = '';
	public $pretext;
	public $fields;

	protected $lesson;

	public function __construct($lesson, $color) 
	{
		$this->color = $color;
		$this->lesson = $lesson;

		$this->pretext = date('l d-m-Y', strtotime($lesson->start_date));

		$this->fields->title = $this->getTitle();
		$this->fields->value = $this->getValue();
		$this->fields->short = false;
	}

	public function getTitle()
	{
		return $this->lesson->long_name . ' - ' . $this->lesson->lecturers[0] ?? '-' . ' - ' . ($this->lesson->locations[0] ?? (object)[])->building;
	}

	public function getValue() 
	{
		return date('H:i', strtotime($this->lesson->start_date)) . ' ' . date('H:i', strtotime($this->lesson->end_date));
	}
}
