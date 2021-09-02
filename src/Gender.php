<?php

namespace Fatty;

use Fatty\Exceptions\FattyException;
use Fatty\Exceptions\MissingBodyFatPercentageInputException;
use Fatty\Metrics\AmountMetric;

abstract class Gender
{
	const BODY_FAT_PERCENTAGE_STRATEGY_MEASUREMENT = 'measurement';
	const BODY_FAT_PERCENTAGE_STRATEGY_PROPORTIONS = 'proportions';
	const ESSENTIAL_FAT_PERCENTAGE = null;

	abstract protected function calcBodyFatPercentageByProportions(Calculator $calculator) : AmountMetric;
	abstract public function calcBasalMetabolicRate(Calculator $calculator) : Energy;
	abstract public function getBasalMetabolicRateFormula(Calculator $calculator) : string;
	abstract public function calcBodyType(Calculator $calculator) : BodyType;

	public static function createFromString(string $value) : ?Gender
	{
		try {
			$class = 'Fatty\\Genders\\' . ucfirst($value);

			return new $class;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getCode()
	{
		return lcfirst(array_slice(explode('\\', get_called_class()), -1, 1)[0]);
	}

	/****************************************************************************
	 * Těhotenství.
	 */
	public function isPregnant()
	{
		return false;
	}

	public function setIsPregnant($isPregnant)
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

		if ($bodyFatPercentage) {
			return static::BODY_FAT_PERCENTAGE_STRATEGY_MEASUREMENT;
		} elseif ($height && $neck && $waist) {
			return static::BODY_FAT_PERCENTAGE_STRATEGY_PROPORTIONS;
		} else {
			return false;
		}
	}

	public function calcBodyFatPercentage(Calculator $calculator) : AmountMetric
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

	protected function calcBodyFatPercentageByMeasurement(Calculator $calculator) : AmountMetric
	{
		return new AmountMetric('bodyFatPercentage', $calculator->getBodyFatPercentage());
	}

	public function calcEssentialFatPercentage() : Percentage
	{
		return new Percentage((float)static::ESSENTIAL_FAT_PERCENTAGE);
	}

	/*****************************************************************************
	 * Doporučený denní příjem - bonusy.
	 */
	public function calcReferenceDailyIntakeBonus()
	{
		return new Energy(new Amount(0));
	}
}
