<?php

namespace Fatty\Vectors;

class SlowGain extends Gain
{
	const CODE = "SLOW_GAIN";
	const LABEL_INFINITIVE = "pomalu přibrat";
	const WEIGHT_CHANGE_PER_WEEK = .05;
	const WEIGHT_GOAL_QUOTIENT = 1.1;
}
