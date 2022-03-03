<?php

namespace Fatty\Vectors;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountWithUnitMetric;

class Maintain extends \Fatty\Vector
{
	const CODE = "MAINTAIN";
	const LABEL_INFINITIVE = "udržovat hmotnost";
	const TDEE_QUOTIENT = 1;
	const WEIGHT_CHANGE_PER_WEEK = 0;

	// public function calcTdeeChangePerDay(Calculator $calculator): AmountWithUnitMetric
	// {
	// 	return new AmountWithUnitMetric("tdeeChangePerDay", new Energy(new Amount(0), Energy::getBaseUnit()));
	// }

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): AmountWithUnitMetric
	{
		return $calculator->calcWeightGoalEnergyExpenditure();
	}
}
