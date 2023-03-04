<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Errors\MissingGoalVectorError;
use Fatty\Metrics\GoalNutrientsCarbsMetric;
use Fatty\Metrics\GoalNutrientsFatsMetric;
use Fatty\Metrics\GoalNutrientsProteinsMetric;
use Fatty\Metrics\MetricResultCollection;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\WeightGoalEnergyExpenditureMetric;

class LowEnergy extends \Fatty\Approach
{
	const CARBS_DEFAULT = 40;
	const CARBS_MAX = 50;
	const CARBS_MIN = 0;
	const CODE = "LOW_ENERGY";
	const ENERGY_DEFAULT = 800;
	const FATS_DEFAULT = 30;
	const LABEL_DECLINATED = "nízkoenergetickou dietu";
	const PROTEINS_DEFAULT = 82;

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new WeightGoalEnergyExpenditureMetric);

		if (!$calculator->getGoal()->getVector()) {
			$result->addError(new MissingGoalVectorError);
		}

		if (!$result->hasErrors()) {
			$energy = (new Energy(
				new Amount(static::ENERGY_DEFAULT),
				static::ENERGY_UNIT,
			))->getInUnit($calculator->getUnits());

			$result->setResult($energy);
		}

		return $result;
	}

	public function calcGoalNutrients(Calculator $calculator): MetricResultCollection
	{
		return new MetricResultCollection([
			(new QuantityMetricResult(new GoalNutrientsCarbsMetric))->setResult((new static)->getDefaultCarbs()),
			(new QuantityMetricResult(new GoalNutrientsFatsMetric))->setResult((new static)->getDefaultFats())
			(new QuantityMetricResult(new GoalNutrientsProteinsMetric))->setResult((new static)->getDefaultProteins()),
		]);
	}
}
