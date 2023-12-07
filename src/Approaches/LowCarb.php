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

class LowCarb extends \Fatty\Approach
{
	const CARBS_DEFAULT = 90;
	const CARBS_MAX = 90;
	const CARBS_MIN = 50;
	const CODE = "LOW_CARB";
	const LABEL_DECLINATED = "nízkosacharidovou dietu";

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

			// Sacharidy, maximum je 90 g.
			$carbs = Carbs::createFromEnergy(
				new Energy(
					new Amount(
						$rdiResult->getResult()->getInUnit(Energy::getBaseUnit())->getNumericalValue() * .2,
					),
					Energy::getBaseUnit(),
				),
			);

			if ($carbs->getInUnit("g")->getAmount()->getValue() > static::CARBS_MAX) {
				$carbs = new Carbs(new Amount(static::CARBS_MAX));
			}

			$nutrients->setCarbs($carbs);

			// Tuky.
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
		}

		return new MetricResultCollection([
			$carbsResult,
			$fatsResult,
			$proteinsResult,
		]);
	}
}
