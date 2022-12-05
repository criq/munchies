<?php

namespace Fatty\Strategies;

use Fatty\Calculator;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\QuantityMetric;
use Fatty\Strategy;

class Zivot20 extends Strategy
{
	const WEIGHT_GOAL_QUOTIENT = null;

	public function calcWeightGoalQuotient(Calculator $calculator): AmountMetric
	{
		return $calculator->getGoal()->getVector()->calcWeightGoalQuotient($calculator);
	}

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetric
	{
		if (!$calculator->getDiet()->getApproach()) {
			throw new \Fatty\Exceptions\MissingDietApproachException;
		}

		return $calculator->getDiet()->getApproach()->calcWeightGoalEnergyExpenditure($calculator);
	}
}
