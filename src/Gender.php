<?php

namespace Fatty;

use Fatty\Exceptions\MissingBodyFatPercentageInputException;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\QuantityMetric;
use Fatty\Metrics\StringMetric;
use Katu\Errors\Error;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;

abstract class Gender
{
	const BODY_FAT_PERCENTAGE_STRATEGY_MEASUREMENT = "measurement";
	const BODY_FAT_PERCENTAGE_STRATEGY_PROPORTIONS = "proportions";
	const ESSENTIAL_FAT_PERCENTAGE = null;
	const FIT_BODY_FAT_PERCENTAGE = null;
	const SPORT_PROTEIN_COEFFICIENT = null;

	abstract protected function calcBodyFatPercentageByProportions(Calculator $calculator): AmountMetric;
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

	/****************************************************************************
	 * Těhotenství.
	 */
	public function isPregnant(): bool
	{
		return false;
	}

	public function setIsPregnant(bool $isPregnant)
	{
		return false;
	}

	public function setPregnancyChildbirthDate($pregnancyChildbirthDate)
	{
		return false;
	}

	public function getPregnancyChildbirthDate()
	{
		return $this->pregnancyChildbirthDate;
	}

	public function isBreastfeeding()
	{
		return false;
	}

	public function setIsBreastfeeding($isBreastfeeding)
	{
		return false;
	}

	public function setBreastfeedingChildbirthDate($breastfeedingChildbirthDate)
	{
		return false;
	}

	public function getBreastfeedingChildbirthDate()
	{
		return $this->breastfeedingChildbirthDate;
	}

	public function setBreastfeedingMode($breastfeedingMode)
	{
		return false;
	}

	public function getBreastfeedingMode()
	{
		return $this->breastfeedingMode;
	}

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	public function getBodyFatPercentageStrategy(Calculator $calculator)
	{
		try {
			$bodyFatPercentage = $calculator->getBodyFatPercentage();
		} catch (\Throwable $e) {
			$bodyFatPercentage = false;
		}

		try {
			$height = $calculator->getProportions()->getHeight();
		} catch (\Throwable $e) {
			$height = false;
		}

		try {
			$neck = $calculator->getProportions()->getNeck();
		} catch (\Throwable $e) {
			$neck = false;
		}

		try {
			$waist = $calculator->getProportions()->getWaist();
		} catch (\Throwable $e) {
			$waist = false;
		}

		try {
			$hips = $calculator->getProportions()->getHips();
		} catch (\Throwable $e) {
			$hips = false;
		}

		if ($bodyFatPercentage) {
			return static::BODY_FAT_PERCENTAGE_STRATEGY_MEASUREMENT;
		} elseif ($height && $neck && $waist && $hips) {
			return static::BODY_FAT_PERCENTAGE_STRATEGY_PROPORTIONS;
		} else {
			return false;
		}
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

	protected function calcBodyFatPercentageByMeasurement(Calculator $calculator): AmountMetric
	{
		return new AmountMetric("bodyFatPercentage", $calculator->getBodyFatPercentage());
	}

	public function calcEssentialFatPercentage(): AmountMetric
	{
		return new AmountMetric("essentialFatPercentage", new Percentage((float)static::ESSENTIAL_FAT_PERCENTAGE));
	}

	public function getSportProteinCoefficient(): float
	{
		return (float)static::SPORT_PROTEIN_COEFFICIENT;
	}

	/*****************************************************************************
	 * Doporučený denní příjem - bonusy.
	 */
	public function calcReferenceDailyIntakeBonus(): QuantityMetric
	{
		return new QuantityMetric("referenceDailyIntakeBonus", new Energy(new Amount(0), "kJ"));
	}

	/****************************************************************************
	 * Fitness level.
	 */
	public function getFitnessLevel(Calculator $calculator): StringMetric
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
}
