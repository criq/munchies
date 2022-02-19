<?php

namespace Fatty\Vectors;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Metrics\AmountWithUnitMetric;

class SlowLoose extends Loose
{
	const CODE = "SLOW_LOOSE";
	const LABEL_INFINITIVE = "pomalu zhubnout";
	const WEIGHT_CHANGE_PER_WEEK = -.5;

	public function calcTdeeChangePerDay(Calculator $calculator): AmountWithUnitMetric
	{
		$weightGoalEnergyExpenditureValue = $calculator->calcWeightGoalEnergyExpenditure()->getResult()->getInUnit("kcal")->getAmount()->getValue();
		$basalMetabolicRateValue = $calculator->calcBasalMetabolicRate()->getResult()->getInUnit("kcal")->getAmount()->getValue();

		$result = new \Fatty\Energy(
			new Amount(
				($weightGoalEnergyExpenditureValue - $basalMetabolicRateValue) * -.35
			),
			"kcal",
		);

		return new AmountWithUnitMetric("tdeeChangePerDay", $result);
	}
}
