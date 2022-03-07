<?php

namespace Fatty\Genders;

use Fatty\Amount;
use Fatty\Birthday;
use Fatty\BodyTypes\Apple;
use Fatty\BodyTypes\AppleWithHigherRisk;
use Fatty\BodyTypes\Balanced;
use Fatty\BodyTypes\PearOrHourglass;
use Fatty\BreastfeedingMode;
use Fatty\BreastfeedingModes\Full;
use Fatty\BreastfeedingModes\Partial;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Exceptions\BreastfeedingChildbirthDateInFutureException;
use Fatty\Exceptions\FattyExceptionCollection;
use Fatty\Exceptions\InvalidBreastfeedingChildbirthDateException;
use Fatty\Exceptions\InvalidPregnancyChildbirthDateException;
use Fatty\Exceptions\MissingBirthdayException;
use Fatty\Exceptions\MissingBreastfeedingChildbirthDateException;
use Fatty\Exceptions\MissingBreastfeedingModeException;
use Fatty\Exceptions\MissingHeightException;
use Fatty\Exceptions\MissingPregnancyChildbirthDateException;
use Fatty\Exceptions\MissingWeightException;
use Fatty\Exceptions\PregnancyChildbirthDateInPastException;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\AmountWithUnitMetric;
use Fatty\Metrics\StringMetric;
use Fatty\Percentage;

class Female extends \Fatty\Gender
{
	const ESSENTIAL_FAT_PERCENTAGE = .13;

	private $isBreastfeeding;
	private $isPregnant;

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	protected function calcBodyFatPercentageByProportions(Calculator $calculator): AmountMetric
	{
		$waistValue = $calculator->getProportions()->getWaist()->getInUnit("cm")->getAmount()->getValue();
		$neckValue = $calculator->getProportions()->getNeck()->getInUnit("cm")->getAmount()->getValue();
		$heightValue = $calculator->getProportions()->getHeight()->getInUnit("cm")->getAmount()->getValue();
		$hipsValue = $calculator->getProportions()->getHips()->getInUnit("cm")->getAmount()->getValue();

		// $result = new Percentage(((495 / (1.0324 - (0.19077 * log10($waistValue - $neckValue)) + (0.15456 * log10($heightValue)))) - 450) * .01);
		// $formula = "((495 / (1.0324 - (0.19077 * log10(waist[{$waistValue}] - neck[{$neckValue}])) + (0.15456 * log10(height[{$heightValue}])))) - 450) * .01 = {$result->getValue()}";

		$resultValue = ((495 / (1.29579 - (0.35004 * log10($waistValue + $hipsValue - $neckValue)) + (0.22100 * log10($heightValue)))) - 450) * 0.01;
		$result = new Percentage($resultValue);
		$formula = "((495 / (1.29579 - (0.35004 * log10(waist[{$waistValue}] + hips[{$hipsValue}] - neck[{$neckValue}])) + (0.22100 * log10(height[{$heightValue}])))) - 450) * 0.01 = {$resultValue}";

		return new AmountMetric("bodyFatPercentage", $result, $formula);
	}

	/*****************************************************************************
	 * Bazální metabolismus - BMR.
	 */
	public function calcBasalMetabolicRate(Calculator $calculator): AmountWithUnitMetric
	{
		$exceptionCollection = new FattyExceptionCollection;

		if (!$calculator->getWeight()) {
			$exceptionCollection->add(new MissingWeightException);
		}

		if (!$calculator->getProportions()->getHeight()) {
			$exceptionCollection->add(new MissingHeightException);
		}

		if (!$calculator->getBirthday()) {
			$exceptionCollection->add(new MissingBirthdayException);
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		$weightValue = $calculator->getWeight()->getInUnit("kg")->getAmount()->getValue();
		$heightValue = $calculator->getProportions()->getHeight()->getInUnit("cm")->getAmount()->getValue();
		$age = $calculator->getBirthday()->getAge();

		$resultValue = (10 * $weightValue) + (6.25 * $heightValue) - (5 * $age) - 161;
		$result = (new Energy(new Amount($resultValue), "kcal"))
			->getInUnit($calculator->getUnits())
			;

		$formula = "
			(10 * weight[{$weightValue}]) + (6.25 * height[{$heightValue}]) - (5 * age[{$age}]) - 161
			= " . (10 * $weightValue) . " + " . (6.25 * $heightValue) . " - " . (5 * $age) . " - 161
			= {$result->getInUnit("kcal")->getAmount()->getValue()} kcal
			= {$result->getInUnit("kJ")->getAmount()->getValue()} kJ
		";

		return new AmountWithUnitMetric("basalMetabolicRate", $result, $formula);
	}

	/*****************************************************************************
	 * Těhotenství.
	 */
	public function isPregnant()
	{
		return $this->isPregnant;
	}

	public function setIsPregnant($isPregnant)
	{
		$this->isPregnant = (bool)$isPregnant;
	}

	public function setPregnancyChildbirthDate($pregnancyChildbirthDate)
	{
		if (!$pregnancyChildbirthDate) {
			throw new MissingPregnancyChildbirthDateException;
		}

		if (is_string($pregnancyChildbirthDate)) {
			$pregnancyChildbirthDate = \DateTime::createFromFormat("j.n.Y", $pregnancyChildbirthDate);
		}

		if ($pregnancyChildbirthDate instanceof \DateTime) {
			$pregnancyChildbirthDate = new Birthday($pregnancyChildbirthDate);
		}

		if (!($pregnancyChildbirthDate instanceof Birthday)) {
			throw new InvalidPregnancyChildbirthDateException;
		}

		if ($pregnancyChildbirthDate->getBirthday()->isInPast()) {
			throw new PregnancyChildbirthDateInPastException;
		}

		$this->pregnancyChildbirthDate = $pregnancyChildbirthDate;

		return $this;
	}

	/*****************************************************************************
	 * Kojení.
	 */
	public function setIsBreastfeeding($isBreastfeeding)
	{
		$this->isBreastfeeding = (bool)$isBreastfeeding;
	}

	public function isBreastfeeding()
	{
		return $this->isBreastfeeding;
	}

	public function setBreastfeedingChildbirthDate($breastfeedingChildbirthDate)
	{
		if (!$breastfeedingChildbirthDate) {
			throw new MissingBreastfeedingChildbirthDateException;
		}

		if (is_string($breastfeedingChildbirthDate)) {
			$breastfeedingChildbirthDate = \DateTime::createFromFormat("j.n.Y", $breastfeedingChildbirthDate);
		}

		if ($breastfeedingChildbirthDate instanceof \DateTime) {
			$breastfeedingChildbirthDate = new Birthday($breastfeedingChildbirthDate);
		}

		if (!($breastfeedingChildbirthDate instanceof Birthday)) {
			throw new InvalidBreastfeedingChildbirthDateException;
		}

		if ($breastfeedingChildbirthDate->getBirthday()->isInFuture()) {
			throw new BreastfeedingChildbirthDateInFutureException;
		}

		$this->breastfeedingChildbirthDate = $breastfeedingChildbirthDate;

		return $this;
	}

	public function setBreastfeedingMode($breastfeedingMode)
	{
		if (is_string($breastfeedingMode)) {
			$className = "Fatty\\BreastfeedingModes\\" . ucfirst($breastfeedingMode);
			if (class_exists($className)) {
				$breastfeedingMode = new $className;
			}
		}

		if (!($breastfeedingMode instanceof BreastfeedingMode)) {
			throw new InvalidBreastfeedingChildbirthDateException;
		}

		$this->breastfeedingMode = $breastfeedingMode;

		return $this;
	}

	/*****************************************************************************
	 * Doporučený denní příjem - bonusy.
	 */
	public function calcReferenceDailyIntakeBonus(): AmountWithUnitMetric
	{
		$exceptionCollection = new FattyExceptionCollection;

		try {
			$referenceDailyIntakeBonusPregnancy = $this->calcReferenceDailyIntakeBonusPregnancy();
		} catch (\Throwable $e) {
			$exceptionCollection->add($e);
		}

		try {
			$referenceDailyIntakeBonusBreastfeeding = $this->calcReferenceDailyIntakeBonusBreastfeeding();
		} catch (\Throwable $e) {
			$exceptionCollection->add($e);
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		$result = new Energy(
			new Amount(
				$referenceDailyIntakeBonusPregnancy->getResult()->getInUnit("kcal")->getAmount()->getValue() + $referenceDailyIntakeBonusBreastfeeding->getResult()->getInUnit("kcal")->getAmount()->getValue()
			),
			"kcal",
		);

		return new AmountWithUnitMetric("referenceDailyIntakeBonus", $result);
	}

	public function calcReferenceDailyIntakeBonusPregnancy(): AmountWithUnitMetric
	{
		if (!$this->isPregnant()) {
			return new AmountWithUnitMetric("referenceDailyIntakeBonusPregnancy", new Energy(new Amount(0), "kJ"));
		}

		if (!($this->getPregnancyChildbirthDate() instanceof Birthday)) {
			throw new MissingPregnancyChildbirthDateException;
		}

		$diff = $this->getPregnancyChildbirthDate()->diff(new \DateTime);
		if ($diff->days <= 90) {
			$change = 85;
		} elseif ($diff->days <= 180) {
			$change = 285;
		} else {
			$change = 475;
		}

		return new AmountWithUnitMetric("referenceDailyIntakeBonusPregnancy", new Energy(new Amount($change), "kcal"));
	}

	public function calcReferenceDailyIntakeBonusBreastfeeding(): AmountWithUnitMetric
	{
		$exceptionCollection = new FattyExceptionCollection;

		if (!$this->isBreastfeeding()) {
			return new AmountWithUnitMetric("referenceDailyIntakeBonusBreastfeeding", new Energy(new Amount(0), "kJ"));
		}

		if (!($this->getBreastfeedingChildbirthDate() instanceof Birthday)) {
			$exceptionCollection->add(new MissingBreastfeedingChildbirthDateException);
		}

		if (!($this->getBreastfeedingMode() instanceof BreastfeedingMode)) {
			$exceptionCollection->add(new MissingBreastfeedingModeException);
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		$diff = $this->getBreastfeedingChildbirthDate()->diff(new \DateTime);
		if ($diff->days <= 365 / 12 * 3) {
			$change = 650;
		} elseif ($this->getBreastfeedingMode() instanceof Full && $diff->days <= 365 / 12 * 6) {
			$change = 570;
		} elseif ($this->getBreastfeedingMode() instanceof Full && $diff->days <= 365) {
			$change = 455;
		} elseif ($this->getBreastfeedingMode() instanceof Full && $diff->days <= 365 * 2) {
			$change = 420;
		} elseif ($this->getBreastfeedingMode() instanceof Partial && $diff->days <= 365 / 12 * 6) {
			$change = 280;
		} elseif ($this->getBreastfeedingMode() instanceof Partial && $diff->days <= 365) {
			$change = 230;
		} elseif ($this->getBreastfeedingMode() instanceof Partial && $diff->days <= 365 * 2) {
			$change = 210;
		} else {
			$change = 100;
		}

		return new AmountWithUnitMetric("referenceDailyIntakeBonusBreastfeeding", new Energy(new Amount($change), "kcal"));
	}

	/*****************************************************************************
	 * Typ postavy.
	 */

	public function calcBodyType(Calculator $calculator): StringMetric
	{
		$waistHipRatioValue = $calculator->calcWaistHipRatio()->getResult()->getValue();

		if ($waistHipRatioValue < .75) {
			$result = new PearOrHourglass;
		} elseif ($waistHipRatioValue >= .75 && $waistHipRatioValue < .8) {
			$result = new Balanced;
		} elseif ($waistHipRatioValue >= .8 && $waistHipRatioValue < .85) {
			$result = new Apple;
		} else {
			$result = new AppleWithHigherRisk;
		}

		return new StringMetric("bodyType", $result->getCode(), $result->getLabel());
	}
}
