<?php

namespace Fatty\Vectors;

class SlowLoose extends Loose
{
	const CODE = "SLOW_LOOSE";
	const LABEL_INFINITIVE = "pomalu zhubnout";
	const WEIGHT_CHANGE_PER_WEEK = -.5;
	const WEIGHT_GOAL_QUOTIENT = .9;
}
