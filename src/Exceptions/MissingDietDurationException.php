<?php

namespace Fatty\Exceptions;

class MissingGoalDurationException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící délka držení diety.";
		$this->paramKeys = ['goal_duration'];
	}
}
