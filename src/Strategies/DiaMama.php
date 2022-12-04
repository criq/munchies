<?php

namespace Fatty\Strategies;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\QuantityMetric;
use Fatty\Strategy;

class DiaMama extends Strategy
{
	public function calcWeightGoalQuotient(Calculator $calculator): AmountMetric
	{
		$bodyMassIndex = $calculator->calcBodyMassIndex()->getResult()->getValue();
		if ($bodyMassIndex <= 19) {
			// BMI pod 19 včetně => cíl vlastně přibírání, WGEE = TDEE * 1,1
			$weightGoalQuotient = 1.1;
		} elseif ($bodyMassIndex > 19 && $bodyMassIndex < 25) {
			// BMI 19,1 až 24,9 => cíl udržování WGEE = TDEE * 1
			$weightGoalQuotient = 1;
		} elseif ($bodyMassIndex >= 25 && $bodyMassIndex < 30) {
			// BMI 25 až 29,9 => cíl “lehké hubnutí” WGEE = TDEE * 0,93
			$weightGoalQuotient = .93;
		} else {
			// BMI více než 30 => cíl vlastně hubnutí WGEE = TDEE * 0,9
			$weightGoalQuotient = .9;
		}

		return new AmountMetric("weightGoalQuotient", new Amount($weightGoalQuotient));
	}


	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetric
	{
		$weightGoalQuotient = $this->calcWeightGoalQuotient($calculator)->getResult();
		$weightGoalQuotientValue = $weightGoalQuotient->getValue();

		$totalDailyEnergyExpenditure = $calculator->calcTotalDailyEnergyExpenditure()->getResult();
		$totalDailyEnergyExpenditureValue = $totalDailyEnergyExpenditure->getAmount()->getValue();

		$result = (new Energy(
			new Amount($totalDailyEnergyExpenditureValue * $weightGoalQuotientValue),
			$totalDailyEnergyExpenditure->getUnit(),
		))->getInUnit($calculator->getUnits());

		$formula = "
			totalDailyEnergyExpenditure[{$totalDailyEnergyExpenditure}] * weightGoalQuotient[{$weightGoalQuotientValue}]
			= {$result->getInUnit("kcal")->getAmount()->getValue()} kcal
			= {$result->getInUnit("kJ")->getAmount()->getValue()} kJ
		";

		return new QuantityMetric("weightGoalEnergyExpenditure", $result, $formula);
	}
}
