<?php

namespace Fatty\Errors;

use Fatty\Error;

class MissingGoalDurationError extends Error
{
	const MESSAGE = "Chybějící délka držení diety.";
}
