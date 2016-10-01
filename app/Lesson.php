<?php
namespace App;

class Lesson
{
	public $color = '';
	public $fields;

	protected $lesson;

	public function __construct($lesson, $color, $pretext)
	{
		$this->color = $color;
		$this->lesson = $lesson;

		$this->fields = collect([ (object)[
			'title' => $this->getTitle(),
			'value' => $this->getValue(),
			'short' => false
		]]);
	}

	public function hasPretext()
	{
		$this->pretext = date('l d-m-Y', strtotime($this->lesson->start_date));

		return $this;
	}

	public function getTitle()
	{
		return implode([
			$this->lesson->long_name,
			$this->lesson->lecturers[0] ?? '-',
			($this->lesson->locations[0] ?? (object)[])->building
		], ' - ');
	}

	public function getValue()
	{
		return implode([
			date('H:i', strtotime($this->lesson->start_date)),
			date('H:i', strtotime($this->lesson->end_date))
		], ' ');
	}
}
