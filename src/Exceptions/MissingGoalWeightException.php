<?php

namespace Fatty\Exceptions;

class MissingGoalWeightException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící cílová hmotnost.";
		$this->paramKeys = ["goal_weight"];
	}
}
