<?php

namespace Fatty;

use Fatty\Metrics\QuantityMetric;
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
	const ENERGY_UNIT = "kcal";
	const FATS_DEFAULT = null;
	const LABEL_DECLINATED = null;
	const PROTEINS_DEFAULT = null;

	abstract public function getGoalNutrients(Calculator $calculator): Nutrients;

	public function __toString(): string
	{
		return $this->getDeclinatedLabel();
	}

	public static function createFromCode(string $value): ?Approach
	{
		return static::getAvailableClasses()[$value] ?? null;
	}

	public static function getAvailableClasses(): array
	{
		return [
			\Fatty\Approaches\Keto::CODE => new \Fatty\Approaches\Keto,
			\Fatty\Approaches\LowCarb::CODE => new \Fatty\Approaches\LowCarb,
			\Fatty\Approaches\LowEnergy::CODE => new \Fatty\Approaches\LowEnergy,
			\Fatty\Approaches\LowEnergyTransition::CODE => new \Fatty\Approaches\LowEnergyTransition,
			\Fatty\Approaches\Mediterranean::CODE => new \Fatty\Approaches\Mediterranean,
			\Fatty\Approaches\Standard::CODE => new \Fatty\Approaches\Standard,
		];
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
		return static::ENERGY_DEFAULT ? new Energy(new Amount((float)static::ENERGY_DEFAULT), static::ENERGY_UNIT) : null;
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
		return static::FATS_DEFAULT ? new Fats(new Amount((float)static::FATS_DEFAULT), "g") : null;
	}

	public function getDefaultProteins(): ?Proteins
	{
		return static::PROTEINS_DEFAULT ? new Proteins(new Amount((float)static::PROTEINS_DEFAULT), "g") : null;
	}

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetric
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

		$formula = "totalDailyEnergyExpenditure[{$totalDailyEnergyExpenditure}] * weightGoalQuotient[{$tdeeQuotientValue}] = {$result}";

		return new QuantityMetric("weightGoalEnergyExpenditure", $result, $formula);
	}

	public function calcGoalNutrientProteins(Calculator $calculator): QuantityMetric
	{
		$maxOptimalWeight = $calculator->calcMaxOptimalWeight();
		$calcSportProteinCoefficient = $calculator->calcSportProteinCoefficient();

		$resultValue = $maxOptimalWeight->getResult()->getAmount()->getValue() * $calcSportProteinCoefficient->getResult()->getValue();

		$proteins = new Nutrients\Proteins(new Amount($resultValue), "g");

		return new \Fatty\Metrics\QuantityMetric("goalNutrientsProteins", $proteins);
	}

	public function calcGoalNutrients(Calculator $calculator): MetricCollection
	{
		$dietApproach = $calculator->getDiet()->getApproach();
		if (!$dietApproach) {
			throw new \Fatty\Exceptions\MissingDietApproachException;
		}

		$nutrients = $this->getGoalNutrients($calculator);

		return new MetricCollection([
			new QuantityMetric("goalNutrientsCarbs", $nutrients->getCarbs()),
			new QuantityMetric("goalNutrientsFats", $nutrients->getFats()),
			new QuantityMetric("goalNutrientsProteins", $nutrients->getProteins()),
		]);
	}
}
