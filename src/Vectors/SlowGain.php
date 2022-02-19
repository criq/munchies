<?php

namespace Fatty\Vectors;

class SlowGain extends Gain
{
	const CODE = "SLOW_GAIN";
	const LABEL_INFINITIVE = "pomalu přibrat";
	const TDEE_QUOTIENT = 1.1;
	const WEIGHT_CHANGE_PER_WEEK = .05;
}
