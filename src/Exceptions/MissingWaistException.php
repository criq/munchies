<?php

namespace Fatty\Exceptions;

class MissingWaistException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící obvod pasu.";
		$this->names = ['waist'];
	}
}
