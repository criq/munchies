<?php

namespace Fatty\Errors;

use Fatty\Error;

class MissingBirthdayError extends Error
{
	const MESSAGE = "Chybějící datum narození.";
}
