<?php

namespace Fatty\Vectors;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountWithUnitMetric;

class Loose extends \Fatty\Vector
{
	const CODE = "LOOSE";
	const LABEL_INFINITIVE = "zhubnout";
	const TDEE_QUOTIENT = 0.9;
	const WEIGHT_CHANGE_PER_WEEK = -.8;

	// public function calcTdeeChangePerDay(Calculator $calculator): AmountWithUnitMetric
	// {
	// 	$weightGoalEnergyExpenditureValue = $calculator->calcWeightGoalEnergyExpenditure()->getResult()->getInUnit("kcal")->getAmount()->getValue();
	// 	$basalMetabolicRateValue = $calculator->calcBasalMetabolicRate()->getResult()->getInUnit("kcal")->getAmount()->getValue();

	// 	$result = new Energy(
	// 		new Amount(
	// 			($weightGoalEnergyExpenditureValue - ($basalMetabolicRateValue + 50)) * -1
	// 		),
	// 		"kcal",
	// 	);

	// 	return new AmountWithUnitMetric("tdeeChangePerDay", $result);
	// }
}
