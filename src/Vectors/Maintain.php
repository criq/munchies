<?php

namespace Fatty\Vectors;

use Fatty\Calculator;
use Fatty\Metrics\QuantityMetricResult;

class Maintain extends \Fatty\Vector
{
	const CODE = "MAINTAIN";
	const LABEL_INFINITIVE = "udržovat hmotnost";
	const WEIGHT_CHANGE_PER_WEEK = 0;
	const WEIGHT_GOAL_QUOTIENT = 1;

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetricResult
	{
		return $calculator->calcWeightGoalEnergyExpenditure();
	}
}
