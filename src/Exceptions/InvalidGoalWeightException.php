<?php

namespace Fatty\Exceptions;

class InvalidGoalWeightException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatná cílová hmotnost.";
		$this->paramKeys = ['goal_weight'];
	}
}
