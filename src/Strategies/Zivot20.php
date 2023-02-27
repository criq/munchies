<?php

namespace Fatty\Strategies;

use Fatty\Calculator;
use Fatty\Metrics\AmountMetricResult;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Strategy;

class Zivot20 extends Strategy
{
	const WEIGHT_GOAL_QUOTIENT = null;

	public function calcWeightGoalQuotient(Calculator $calculator): AmountMetricResult
	{
		return $calculator->getGoal()->getVector()->calcWeightGoalQuotient($calculator);
	}

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetricResult
	{
		if (!$calculator->getDiet()->getApproach()) {
			throw new \Fatty\Exceptions\MissingDietApproachException;
		}

		return $calculator->getDiet()->getApproach()->calcWeightGoalEnergyExpenditure($calculator);
	}
}
