<?php

namespace Fatty\Vectors;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountWithUnitMetric;

class Gain extends \Fatty\Vector
{
	const CODE = "GAIN";
	const LABEL_INFINITIVE = "přibrat";
	const TDEE_QUOTIENT = 1.1;
	const WEIGHT_CHANGE_PER_WEEK = .3;

	public function calcTdeeChangePerDay(Calculator $calculator): AmountWithUnitMetric
	{
		$weightGoalEnergyExpenditureValue = $calculator->calcWeightGoalEnergyExpenditure()->getResult()->getInUnit("kcal")->getAmount()->getValue();
		$tdeeQuotientValue = $this->calcTdeeQuotient($calculator)->getResult()->getValue();

		$result = new Energy(
			new Amount($weightGoalEnergyExpenditureValue * ($tdeeQuotientValue - 1)),
			"kcal",
		);

		return new AmountWithUnitMetric("", $result);
	}
}
