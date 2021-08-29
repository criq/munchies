<?php

namespace Fatty;

use Fatty\Exceptions\FattyException;

abstract class Gender
{
	const BODY_FAT_PERCENTAGE_STRATEGY_MEASUREMENT = 'measurement';
	const BODY_FAT_PERCENTAGE_STRATEGY_PROPORTIONS = 'proportions';

	abstract protected function getBodyFatPercentageByProportions(Calculator $calculator) : Percentage;
	abstract public function getBasalMetabolicRate(Calculator $calculator);
	abstract public function getBasalMetabolicRateFormula(Calculator $calculator);
	abstract public function getBodyFatPercentageByProportionsFormula(Calculator $calculator) : string;
	abstract public function getBodyType(Calculator $calculator);

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
		} catch (\Exception $e) {
			$bodyFatPercentage = false;
		}

		try {
			$height = $calculator->getProportions()->getHeight();
		} catch (\Exception $e) {
			$height = false;
		}

		try {
			$neck = $calculator->getProportions()->getNeck();
		} catch (\Exception $e) {
			$neck = false;
		}

		try {
			$waist = $calculator->getProportions()->getWaist();
		} catch (\Exception $e) {
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

	public function getBodyFatPercentage(Calculator $calculator) : Percentage
	{
		$strategy = $this->getBodyFatPercentageStrategy($calculator);
		if (!$strategy) {
			throw (new FattyException("Missing data to determine your body fat percentage."))
				->setAbbr('missingBodyFatPercentageInput');
		}

		switch ($strategy) {
			case static::BODY_FAT_PERCENTAGE_STRATEGY_MEASUREMENT:
				return $this->getBodyFatPercentageByMeasurement($calculator);
				break;
			case static::BODY_FAT_PERCENTAGE_STRATEGY_PROPORTIONS:
				return $this->getBodyFatPercentageByProportions($calculator);
				break;
		}
	}

	protected function getBodyFatPercentageByMeasurement(Calculator $calculator)
	{
		return $calculator->getBodyFatPercentage();
	}

	public function getBodyFatPercentageFormula(Calculator $calculator)
	{
		$result = $this->getBodyFatPercentage($calculator)->getAmount();

		switch ($this->getBodyFatPercentageStrategy($calculator)) {
			case static::BODY_FAT_PERCENTAGE_STRATEGY_MEASUREMENT:
				return $result;
				break;
			case static::BODY_FAT_PERCENTAGE_STRATEGY_PROPORTIONS:
				return $this->getBodyFatPercentageByProportionsFormula($calculator) . ' = ' . $result;
				break;
		}
	}

	/*****************************************************************************
	 * Doporučený denní příjem - bonusy.
	 */
	public function getReferenceDailyIntakeBonus()
	{
		return new Energy(0);
	}
}
