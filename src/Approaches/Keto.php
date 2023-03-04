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
use Fatty\Nutrients\Fats;

class Keto extends \Fatty\Approach
{
	const CARBS_DEFAULT = 40;
	const CARBS_MAX = 50;
	const CARBS_MIN = 0;
	const CODE = "KETO";
	const LABEL_DECLINATED = "keto dietu";

	public function calcGoalNutrients(Calculator $calculator): MetricResultCollection
	{
		$carbsResult = new QuantityMetricResult(new GoalNutrientsCarbsMetric);
		$fatsResult = new QuantityMetricResult(new GoalNutrientsFatsMetric);
		$proteinsResult = $this->calcGoalNutrientsProteins($calculator);

		$wgeeResult = $calculator->calcWeightGoalEnergyExpenditure();
		$fatsResult->addErrors($wgeeResult->getErrors());

		if (!$carbsResult->hasErrors() && !$fatsResult->hasErrors() && !$proteinsResult->hasErrors()) {
			$nutrients = new Nutrients;

			$carbs = $calculator->getDiet()->getCarbs();
			$nutrients->setCarbs($carbs);

			$nutrients->setProteins($proteinsResult->getResult());

			$fats = Fats::createFromEnergy(
				new Energy(
					new Amount(
						$wgeeResult->getResult()->getInUnit(Energy::getBaseUnit())->getNumericalValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
					),
					Energy::getBaseUnit(),
				),
			);
			$nutrients->setFats($fats);

			$carbsResult->setResult($nutrients->getCarbs());
			$fatsResult->setResult($nutrients->getFats());
			$proteinsResult->setResult($nutrients->getProteins());
		}

		return new MetricResultCollection([
			$carbsResult,
			$fatsResult,
			$proteinsResult,
		]);
	}
}
