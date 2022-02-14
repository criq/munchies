<?php

namespace Fatty;

use Fatty\Metrics\AmountWithUnitMetric;
use Fatty\Nutrients\Carbs;
use Fatty\Nutrients\Fats;
use Fatty\Nutrients\Proteins;

abstract class Approach
{
	const CARBS_DEFAULT = null;
	const CARBS_MAX = null;
	const CARBS_MIN = null;
	const CODE = null;
	const ENERGY_DEFAULT = null;
	const FATS_DEFAULT = null;
	const LABEL_DECLINATED = null;
	const PROTEINS_DEFAULT = null;

	public function __toString(): string
	{
		return $this->getDeclinatedLabel();
	}

	public static function createFromCode(string $value): ?Approach
	{
		try {
			$class = "Fatty\\Approaches\\" . ucfirst($value);

			return new $class;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getCode(): string
	{
		return (string)static::CODE;
	}

	public function getDeclinatedLabel(): string
	{
		return (string)static::LABEL_DECLINATED;
	}

	public function getDefaultEnergy(): ?Energy
	{
		return static::ENERGY_DEFAULT ? new Energy(new Amount((float)static::ENERGY_DEFAULT), "kcal") : null;
	}

	public function getDefaultCarbs(): ?Carbs
	{
		return static::CARBS_DEFAULT ? new Carbs(new Amount((float)static::CARBS_DEFAULT), "g") : null;
	}

	public function getMinCarbs(): ?Carbs
	{
		return static::CARBS_MIN ? new Carbs(new Amount((float)static::CARBS_MIN), "g") : null;
	}

	public function getMaxCarbs(): ?Carbs
	{
		return static::CARBS_MAX ? new Carbs(new Amount((float)static::CARBS_MAX), "g") : null;
	}

	public function getDefaultFats(): ?Fats
	{
		return static::FATS_DEFAULT ? new Carbs(new Amount((float)static::FATS_DEFAULT), "g") : null;
	}

	public function getDefaultProteins(): ?Proteins
	{
		return static::PROTEINS_DEFAULT ? new Carbs(new Amount((float)static::PROTEINS_DEFAULT), "g") : null;
	}

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): \Fatty\Metrics\AmountWithUnitMetric
	{
		if (!$calculator->getGoal()->getVector()) {
			throw new \Fatty\Exceptions\MissingGoalVectorException;
		}

		$totalDailyEnergyExpenditure = $calculator->calcTotalDailyEnergyExpenditure()->getResult();
		$totalDailyEnergyExpenditureValue = $totalDailyEnergyExpenditure->getAmount()->getValue();
		$tdeeQuotientValue = $calculator->getGoal()->getVector()->calcTdeeQuotient($calculator)->getResult()->getValue();

		$result = (new Energy(
			new Amount($totalDailyEnergyExpenditureValue * $tdeeQuotientValue),
			$totalDailyEnergyExpenditure->getUnit(),
		))->getInUnit($calculator->getUnits());

		$formula = "totalDailyEnergyExpenditure[" . $totalDailyEnergyExpenditure . "] * weightGoalQuotient[" . $tdeeQuotientValue . "] = " . $result;

		return new AmountWithUnitMetric("weightGoalEnergyExpenditure", $result, $formula);
	}
}
