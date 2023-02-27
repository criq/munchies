<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Errors\MissingGoalVectorError;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\WeightGoalEnergyExpenditureMetric;
use Fatty\Nutrients;

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

	public function getGoalNutrients(Calculator $calculator): Nutrients
	{
		$nutrients = new Nutrients;
		$nutrients->setCarbs((new static)->getDefaultCarbs());
		$nutrients->setFats((new static)->getDefaultFats());
		$nutrients->setProteins((new static)->getDefaultProteins());

		return $nutrients;
	}
}
