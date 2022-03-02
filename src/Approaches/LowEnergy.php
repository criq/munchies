<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountWithUnitMetric;
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

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): AmountWithUnitMetric
	{
		if (!$calculator->getGoal()->getVector()) {
			throw new \Fatty\Exceptions\MissingGoalVectorException;
		}

		$result = (new Energy(
			new Amount(static::ENERGY_DEFAULT),
			static::ENERGY_UNIT,
		))->getInUnit($calculator->getUnits());

		return new AmountWithUnitMetric("weightGoalEnergyExpenditure", $result);
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
