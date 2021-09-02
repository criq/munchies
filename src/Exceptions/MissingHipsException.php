<?php

namespace Fatty\Exceptions;

class MissingHipsException extends FattyException
{
	public function __construct()
	{
		$this->message = "Chybějící obvod boků.";
		$this->names = ['proportions_hips'];
	}
}
