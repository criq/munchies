<?php

namespace Fatty\Exceptions;

class MissingGoalVectorException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící cílový stav.";
		$this->names = ['goal_vector'];
	}
}
