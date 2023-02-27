<?php

namespace Fatty\Errors;

use Fatty\Error;

class MissingGoalVectorError extends Error
{
	const MESSAGE = "Chybějící cílový stav.";
}
