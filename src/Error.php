<?php

namespace Fatty;

class Error extends \Katu\Errors\Error
{
	const MESSAGE = "";

	public function getMessage(): ?string
	{
		return static::MESSAGE;
	}
}
