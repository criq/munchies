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

class Mediterranean extends Standard
{
	const CODE = "MEDITERRANEAN";
	const LABEL_DECLINATED = "středomořskou dietu";

	public function calcGoalNutrients(Calculator $calculator): MetricResultCollection
	{
		$carbsResult = new QuantityMetricResult(new GoalNutrientsCarbsMetric);
		$fatsResult = new QuantityMetricResult(new GoalNutrientsFatsMetric);
		$proteinsResult = $this->calcGoalNutrientsProteins($calculator);

		$rdiResult = $calculator->calcReferenceDailyIntake();
		$carbsResult->addErrors($rdiResult->getErrors());
		$fatsResult->addErrors($rdiResult->getErrors());

		if (!$carbsResult->hasErrors() && !$fatsResult->hasErrors() && !$proteinsResult->hasErrors()) {
			$nutrients = new Nutrients;
			$nutrients->setProteins($proteinsResult->getResult());

			$fats = Fats::createFromEnergy(
				new Energy(
					new Amount(
						$rdiResult->getResult()->getInUnit(Energy::getBaseUnit())->getNumericalValue() * .4
					),
					Energy::getBaseUnit(),
				),
			);
			$nutrients->setFats($fats);

			$carbs = Carbs::createFromEnergy(
				new Energy(
					new Amount(
						$rdiResult->getResult()->getInUnit(Energy::getBaseUnit())->getNumericalValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
					),
					Energy::getBaseUnit(),
				),
			);
			$nutrients->setCarbs($carbs);

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
