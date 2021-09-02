<?php

namespace Fatty\Exceptions;

class MissingGoalWeightException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící cílová hmotnost.";
		$this->names = ['goal_weight'];
	}
}
