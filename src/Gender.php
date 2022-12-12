<?php

namespace Fatty;

use Fatty\Exceptions\MissingBodyFatPercentageInputException;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\QuantityMetric;
use Fatty\Metrics\StringMetric;
use Fatty\Nutrients\Proteins;
use Katu\Errors\Error;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;

abstract class Gender
{
	const BASAL_METABOLIC_RATE_STRATEGY_KATCH_MCARDLE = "KATCH_MCARDLE";
	const BASAL_METABOLIC_RATE_STRATEGY_MIFFLIN_STJEOR = "MIFFLIN_STJEOR";
	const BODY_FAT_PERCENTAGE_STRATEGY_MEASUREMENT = "MEASUREMENT";
	const BODY_FAT_PERCENTAGE_STRATEGY_PROPORTIONS = "PROPORTIONS";
	const ESSENTIAL_FAT_PERCENTAGE = NULL;
	const FIT_BODY_FAT_PERCENTAGE = NULL;

	abstract public function calcBasalMetabolicRateMifflinStJeorAdjustment(): QuantityMetric;
	abstract public function calcBodyFatPercentageByProportions(Calculator $calculator): AmountMetric;
	abstract public function calcBodyType(Calculator $calculator): StringMetric;
	abstract public function getSportProteinMatrix(): array;

	public static function createFromString(string $value): ?Gender
	{
		try {
			$value = ucfirst($value);
			$class = "Fatty\\Genders\\{$value}";

			return new $class;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public static function validateGender(Param $gender): Validation
	{
		$output = \Fatty\Gender::createFromString((string)$gender);
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid gender."))->addParam($gender));
		} else {
			return (new Validation)->setResponse($output)->addParam($gender->setOutput($output));
		}
	}

	public function getCode(): string
	{
		return lcfirst(array_slice(explode("\\", get_called_class()), -1, 1)[0]);
	}

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	public function getBodyFatPercentageStrategy(Calculator $calculator): ?string
	{
		try {
			$bodyFatPercentage = $calculator->getBodyFatPercentage();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		try {
			$height = $calculator->getProportions()->getHeight();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		try {
			$neck = $calculator->getProportions()->getNeck();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		try {
			$waist = $calculator->getProportions()->getWaist();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		try {
			$hips = $calculator->getProportions()->getHips();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		if ($bodyFatPercentage ?? null) {
			return static::BODY_FAT_PERCENTAGE_STRATEGY_MEASUREMENT;
		} elseif (($height ?? null) && ($neck ?? null) && ($waist ?? null) && ($hips ?? null)) {
			return static::BODY_FAT_PERCENTAGE_STRATEGY_PROPORTIONS;
		}

		return null;
	}

	public function calcBodyFatPercentage(Calculator $calculator): AmountMetric
	{
		$strategy = $this->getBodyFatPercentageStrategy($calculator);
		if (!$strategy) {
			throw new MissingBodyFatPercentageInputException;
		}

		switch ($strategy) {
			case static::BODY_FAT_PERCENTAGE_STRATEGY_MEASUREMENT:
				return $this->calcBodyFatPercentageByMeasurement($calculator);
				break;
			case static::BODY_FAT_PERCENTAGE_STRATEGY_PROPORTIONS:
				return $this->calcBodyFatPercentageByProportions($calculator);
				break;
		}
	}

	public function calcBodyFatPercentageByMeasurement(Calculator $calculator): AmountMetric
	{
		return new AmountMetric("bodyFatPercentage", $calculator->getBodyFatPercentage());
	}

	public function calcEssentialFatPercentage(): AmountMetric
	{
		return new AmountMetric("essentialFatPercentage", new Percentage((float)static::ESSENTIAL_FAT_PERCENTAGE));
	}

	/****************************************************************************
	 * Basal metabolic rate.
	 */
	public function calcBasalMetabolicRate(Calculator $calculator): QuantityMetric
	{
		switch ($this->calcBasalMetabolicRateStrategy($calculator)->getResult()) {
			case static::BASAL_METABOLIC_RATE_STRATEGY_MIFFLIN_STJEOR:
				return $this->calcBasalMetabolicRateMifflinStJeor($calculator);
				break;
			case static::BASAL_METABOLIC_RATE_STRATEGY_KATCH_MCARDLE:
				return $this->calcBasalMetabolicRateKatchMcArdle($calculator);
				break;
		}
	}

	public function calcBasalMetabolicRateStrategy(Calculator $calculator): StringMetric
	{
		return new StringMetric(
			"basalMetabolicRateStrategy",
			static::BASAL_METABOLIC_RATE_STRATEGY_KATCH_MCARDLE,
		);
	}

	public function calcBasalMetabolicRateKatchMcArdle(Calculator $calculator): QuantityMetric
	{
		$fatFreeMass = $calculator->calcFatFreeMass();
		$fatFreeMassValue = $fatFreeMass->getResult()->getAmount()->getValue();

		$resultValue = 370 + (21.6 * $fatFreeMassValue);
		$result = (new Energy(
			new Amount($resultValue),
			"kcal",
		))->getInUnit($calculator->getUnits());

		$formula = "
			370 + (21.6 * fatFreeMass[{$fatFreeMassValue}])
			= 370 + " . (21.6 * $fatFreeMassValue) . "
			= {$result->getInUnit("kcal")->getAmount()->getValue()} kcal
			= {$result->getInUnit("kJ")->getAmount()->getValue()} kJ
		";

		return new QuantityMetric("basalMetabolicRate", $result, $formula);
	}

	public function calcBasalMetabolicRateMifflinStJeor(Calculator $calculator): QuantityMetric
	{
		$weight = $this->calcBasalMetabolicRateMifflinStJeorWeight($calculator)->getResult();
		$weightValue = $weight->getInUnit("kg")->getAmount()->getValue();
		$heightValue = $calculator->getProportions()->getHeight()->getInUnit("cm")->getAmount()->getValue();
		$ageValue = $calculator->getBirthday()->getAge($calculator->getReferenceTime());

		$basalMetabolicRateMifflinStJeorAdjustment = $this->calcBasalMetabolicRateMifflinStJeorAdjustment()->getResult();
		$basalMetabolicRateMifflinStJeorAdjustmentValue = $basalMetabolicRateMifflinStJeorAdjustment->getAmount()->getValue();

		$result = (new Energy(
			new Amount(
				(10 * $weightValue)
				+ (6.25 * $heightValue)
				- (5 * $ageValue)
				+ $basalMetabolicRateMifflinStJeorAdjustmentValue
			),
			"kcal",
		))->getInUnit($calculator->getUnits());

		$formula = "
			(10 * weight[$weightValue]) + (6.25 * height[$heightValue]) - (5 * age[$ageValue]) + basalMetabolicRateMifflinStJeorAdjustment[$basalMetabolicRateMifflinStJeorAdjustmentValue]
			= " . (10 * $weightValue) . " + " . (6.25 * $heightValue) . " - " . (5 * $ageValue) . " + ($basalMetabolicRateMifflinStJeorAdjustmentValue)
			= {$result->getInUnit("kcal")->getAmount()->getValue()} kcal
			= {$result->getInUnit("kJ")->getAmount()->getValue()} kJ
		";

		return new QuantityMetric(
			"basalMetabolicRate",
			$result,
			$formula
		);
	}

	public function calcBasalMetabolicRateMifflinStJeorWeight(Calculator $calculator): QuantityMetric
	{
		return new QuantityMetric(
			"basalMetabolicRateMifflinStJeorWeight",
			$calculator->getWeight(),
		);
	}

	/*****************************************************************************
	 * Doporučený denní příjem - bonusy.
	 */
	public function calcReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetric
	{
		return new QuantityMetric("referenceDailyIntakeBonus", new Energy);
	}

	/****************************************************************************
	 * Fitness level.
	 */
	public function calcFitnessLevel(Calculator $calculator): StringMetric
	{
		$bodyFatPercentage = $calculator->calcBodyFatPercentage();
		$bodyFatPercentageValue = $bodyFatPercentage->getResult()->getValue();

		$fitBodyFatPercentage = static::FIT_BODY_FAT_PERCENTAGE;

		$string = $calculator->calcBodyFatPercentage()->getResult()->getValue() > $fitBodyFatPercentage ? "UNFIT" : "FIT";
		$formula = "bodyFatPercentage[{$bodyFatPercentageValue}] > {$fitBodyFatPercentage} ? UNFIT : FIT";

		return new StringMetric("fitnessLevel", $string, $string, $formula);
	}

	public function calcMaxOptimalWeight(Calculator $calculator): QuantityMetric
	{
		if ($calculator->calcFitnessLevel()->getResult() == "UNFIT") {
			$fatFreeMass = $calculator->calcFatFreeMass();
			$fatFreeMassValue = $fatFreeMass->getResult()->getInUnit("kg")->getAmount()->getValue();

			$fitBodyFatPercentage = static::FIT_BODY_FAT_PERCENTAGE;

			$value = $fatFreeMassValue * (1 + $fitBodyFatPercentage);

			$weight = new Weight(new Amount($value), "kg");
			$formula = "
				fatFreeMass[{$fatFreeMassValue}] * (1 + fitBodyFatPercentage[{$fitBodyFatPercentage}])
				= {$fatFreeMassValue} * " . (1 + $fitBodyFatPercentage) . "
				= {$value} kg
			";
		} else {
			$weight = $calculator->getWeight();
			$formula = "weight[$weight]";
		}

		return new QuantityMetric("maxOptimalWeight", $weight, $formula);
	}

	public function calcSportProteinSetKey(Calculator $calculator): StringMetric
	{
		return new StringMetric("sportProteinSetKey", (string)$calculator->calcFitnessLevel()->getResult());
	}

	public function calcGoalNutrientProteinBonus(Calculator $calculator): QuantityMetric
	{
		return new QuantityMetric(
			"goalNutrientProteinBonus",
			new Proteins(new Amount),
		);
	}
}
