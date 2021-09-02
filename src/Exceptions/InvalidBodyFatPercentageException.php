<?php

namespace Fatty\Exceptions;

class InvalidBodyFatPercentageException extends FattyException
{
	public function __construct()
	{
		$this->message = "Neplatné procento tělesného tuku.";
		$this->names = ['bodyFatPercentage'];
	}
}
