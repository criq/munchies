<?php

namespace Fatty\Strategies;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountMetricResult;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\WeightGoalEnergyExpenditureMetric;
use Fatty\Metrics\WeightGoalQuotientMetric;
use Fatty\Strategy;

class DiaMama extends Strategy
{
	public function calcWeightGoalQuotient(Calculator $calculator): AmountMetricResult
	{
		$result = new AmountMetricResult(new WeightGoalQuotientMetric);

		$bodyMassIndexResult = $calculator->calcBodyMassIndex();
		$result->addErrors($bodyMassIndexResult->getErrors());

		if (!$result->hasErrors()) {
			$bodyMassIndexValue = $bodyMassIndexResult->getResult()->getNumericalValue();

			if ($bodyMassIndexValue <= 19) {
				// BMI pod 19 včetně => cíl vlastně přibírání, WGEE = TDEE * 1,1
				$weightGoalQuotient = 1.1;
			} elseif ($bodyMassIndexValue > 19 && $bodyMassIndexValue < 25) {
				// BMI 19,1 až 24,9 => cíl udržování WGEE = TDEE * 1
				$weightGoalQuotient = 1;
			} elseif ($bodyMassIndexValue >= 25 && $bodyMassIndexValue < 30) {
				// BMI 25 až 29,9 => cíl “lehké hubnutí” WGEE = TDEE * 0,93
				$weightGoalQuotient = .93;
			} else {
				// BMI více než 30 => cíl vlastně hubnutí WGEE = TDEE * 0,9
				$weightGoalQuotient = .9;
			}

			$result->setResult(new Amount($weightGoalQuotient));
		}

		return $result;
	}

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new WeightGoalEnergyExpenditureMetric);

		$totalDailyEnergyExpenditureResult = $calculator->calcTotalDailyEnergyExpenditure();
		$result->addErrors($totalDailyEnergyExpenditureResult->getErrors());

		$weightGoalQuotientResult = $this->calcWeightGoalQuotient($calculator);
		$result->addErrors($weightGoalQuotientResult->getErrors());

		if (!$result->hasErrors()) {
			$totalDailyEnergyExpenditureValue = $totalDailyEnergyExpenditureResult->getResult()->getNumericalValue();
			$weightGoalQuotientValue = $weightGoalQuotientResult->getResult()->getNumericalValue();

			$energy = (new Energy(
				new Amount($totalDailyEnergyExpenditureValue * $weightGoalQuotientValue),
				$totalDailyEnergyExpenditureResult->getResult()->getUnit(),
			))->getInUnit($calculator->getUnits());

			$formula = "
				totalDailyEnergyExpenditure[{$totalDailyEnergyExpenditureValue}] * weightGoalQuotient[{$weightGoalQuotientValue}]
				= {$energy->getInUnit("kcal")->getAmount()->getValue()} kcal
				= {$energy->getInUnit("kJ")->getAmount()->getValue()} kJ
			";

			$result->setResult($energy)->setFormula($formula);
		}

		return $result;
	}
}
