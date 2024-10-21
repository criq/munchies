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

class DIA150 extends \Fatty\Approach
{
	const CARBS_DEFAULT = 150;
	const CODE = "DIA150";
	const LABEL_DECLINATED = "dietu DIA150";

	public function calcGoalNutrients(Calculator $calculator): MetricResultCollection
	{
		$carbsResult = new QuantityMetricResult(new GoalNutrientsCarbsMetric);
		$fatsResult = new QuantityMetricResult(new GoalNutrientsFatsMetric);
		$proteinsResult = $this->calcGoalNutrientsProteins($calculator);

		$rdiResult = $calculator->calcReferenceDailyIntake();

		$nutrients = new Nutrients;
		$nutrients->setProteins($proteinsResult->getResult());

		$carbs = new Carbs(new Amount(static::CARBS_DEFAULT), "g");
		$nutrients->setCarbs($carbs);

		$fats = Fats::createFromEnergy(
			new Energy(
				new Amount(
					$rdiResult->getResult()->getInUnit(Energy::getBaseUnit())->getNumericalValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
				),
				Energy::getBaseUnit(),
			),
		);

		$nutrients->setFats($fats);

		$carbsResult->setResult($nutrients->getCarbs());
		$fatsResult->setResult($nutrients->getFats());
		$proteinsResult->setResult($nutrients->getProteins());

		return new MetricResultCollection([
			$carbsResult,
			$fatsResult,
			$proteinsResult,
		]);
	}
}
