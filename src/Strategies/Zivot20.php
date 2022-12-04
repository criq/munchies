<?php

namespace Fatty\Strategies;

use Fatty\Calculator;
use Fatty\Metrics\QuantityMetric;
use Fatty\Strategy;

class Zivot20 extends Strategy
{
	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetric
	{
		if (!$calculator->getDiet()->getApproach()) {
			throw new \Fatty\Exceptions\MissingDietApproachException;
		}

		return $calculator->getDiet()->getApproach()->calcWeightGoalEnergyExpenditure($calculator);
	}
}
