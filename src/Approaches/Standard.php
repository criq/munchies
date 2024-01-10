<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\GoalNutrientsCarbsMetric;
use Fatty\Metrics\GoalNutrientsFatsMetric;
use Fatty\Metrics\MetricResultCollection;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Nutrients;
use Fatty\Nutrients\Carbs;
use Fatty\Nutrients\Fats;
use Fatty\SportDuration;

class Standard extends \Fatty\Approach
{
	const CARBS_DEFAULT = 120;
	const CODE = "STANDARD";
	const LABEL_DECLINATED = "standardní dietu";

	public function calcGoalNutrients(Calculator $calculator): MetricResultCollection
	{
		$carbsResult = new QuantityMetricResult(new GoalNutrientsCarbsMetric);
		$fatsResult = new QuantityMetricResult(new GoalNutrientsFatsMetric);
		$proteinsResult = $this->calcGoalNutrientsProteins($calculator);

		$rdiResult = $calculator->calcReferenceDailyIntake();
		$carbsResult->addErrors($rdiResult->getErrors());
		$fatsResult->addErrors($rdiResult->getErrors());

		$energyBaseUnit = Energy::getBaseUnit();

		if (!$carbsResult->hasErrors() && !$fatsResult->hasErrors() && !$proteinsResult->hasErrors()) {
			$nutrients = new Nutrients;
			$nutrients->setProteins($proteinsResult->getResult());

			if ($calculator->getSportDurations()->getAnaerobic() instanceof SportDuration && $calculator->getSportDurations()->getAnaerobic()->getAmount()->getValue() >= 100) {
				$carbsEnergy = new Energy(new Amount($rdiResult->getResult()->getInUnit($energyBaseUnit)->getNumericalValue() * .58), $energyBaseUnit);
				$carbs = Carbs::createFromEnergy($carbsEnergy);
				$nutrients->setCarbs($carbs);

				$fats = Fats::createFromEnergy(
					new Energy(
						new Amount(
							$rdiResult->getResult()->getInUnit($energyBaseUnit)->getNumericalValue() - $nutrients->getEnergy()->getInUnit($energyBaseUnit)->getAmount()->getValue(),
						),
						$energyBaseUnit,
					),
				);
				$nutrients->setFats($fats);
			} else {
				$carbs = Carbs::createFromEnergy(
					new Energy(
						new Amount(
							$rdiResult->getResult()->getInUnit($energyBaseUnit)->getNumericalValue() * .55
						),
						$energyBaseUnit,
					),
				);
				$nutrients->setCarbs($carbs);

				$fats = Fats::createFromEnergy(
					new Energy(
						new Amount(
							$rdiResult->getResult()->getInUnit($energyBaseUnit)->getNumericalValue() - $nutrients->getEnergy()->getInUnit($energyBaseUnit)->getAmount()->getValue()
						),
						$energyBaseUnit,
					),
				);
				$nutrients->setFats($fats);
			}

			$carbsResult->setResult($nutrients->getCarbs());
			$fatsResult->setResult($nutrients->getFats());
			$proteinsResult->setResult($proteinsResult->getResult());
		}

		return new MetricResultCollection([
			$carbsResult,
			$fatsResult,
			$proteinsResult,
		]);
	}
}
