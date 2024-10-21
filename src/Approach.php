<?php

namespace Fatty;

use Fatty\Errors\MissingGoalVectorError;
use Fatty\Metrics\GoalNutrientsProteinsMetric;
use Fatty\Metrics\MetricResultCollection;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\WeightGoalEnergyExpenditureMetric;
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

	abstract public function calcGoalNutrients(Calculator $calculator): MetricResultCollection;

	public function __toString(): string
	{
		return $this->getDeclinatedLabel();
	}

	public static function createFromCode(string $code): ?Approach
	{
		return ApproachCollection::createDefault()->getAssoc()[$code] ?? null;
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

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new WeightGoalEnergyExpenditureMetric);

		if (!$calculator->getGoal()->getVector()) {
			$result->addError(new MissingGoalVectorError);
		}

		$totalDailyEnergyExpenditureResult = $calculator->calcTotalDailyEnergyExpenditure();
		$result->addErrors($totalDailyEnergyExpenditureResult->getErrors());

		$weightGoalQuotientResult = $calculator->getGoal()->getVector()->calcWeightGoalQuotient($calculator);
		$result->addErrors($weightGoalQuotientResult->getErrors());

		if (!$result->hasErrors()) {
			$totalDailyEnergyExpenditureValue = $totalDailyEnergyExpenditureResult->getResult()->getNumericalValue();
			$weightGoalQuotientValue = $weightGoalQuotientResult->getResult()->getNumericalValue();

			$energy = (new Energy(
				new Amount($totalDailyEnergyExpenditureValue * $weightGoalQuotientValue),
				$totalDailyEnergyExpenditureResult->getResult()->getUnit(),
			))->getInUnit($calculator->getUnits());

			$formula = "
				totalDailyEnergyExpenditure[{$totalDailyEnergyExpenditureValue}] * weightGoalQuotient[{$weightGoalQuotientValue}]
				= {$energy->getInUnit("kcal")->getAmount()->getValue()} kcal
				= {$energy->getInUnit("kJ")->getAmount()->getValue()} kJ
			";

			$result->setResult($energy)->setFormula($formula);
		}

		return $result;
	}

	public function calcGoalNutrientsProteins(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new GoalNutrientsProteinsMetric);

		$maxOptimalWeightResult = $calculator->calcMaxOptimalWeight();
		$result->addErrors($maxOptimalWeightResult->getErrors());

		$calcSportProteinCoefficientResult = $calculator->calcSportProteinCoefficient();
		$result->addErrors($calcSportProteinCoefficientResult->getErrors());

		if (!$result->hasErrors()) {
			$maxOptimalWeightValue = $maxOptimalWeightResult->getResult()->getNumericalValue();
			$calcSportProteinCoefficient = $calcSportProteinCoefficientResult->getResult()->getNumericalValue();

			$amount = $maxOptimalWeightValue * $calcSportProteinCoefficient;
			$proteins = new Nutrients\Proteins(new Amount($amount), "g");

			$result->setResult($proteins);
		}

		return $result;
	}
}
