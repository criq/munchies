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
use Fatty\Exceptions\InvalidBreastfeedingChildbirthDateException;
use Fatty\Exceptions\InvalidPregnancyChildbirthDateException;
use Fatty\Exceptions\MissingBreastfeedingChildbirthDateException;
use Fatty\Exceptions\MissingBreastfeedingModeException;
use Fatty\Exceptions\MissingPregnancyChildbirthDateException;
use Fatty\Exceptions\PregnancyChildbirthDateInPastException;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\QuantityMetric;
use Fatty\Metrics\StringMetric;
use Fatty\Percentage;
use Fatty\Weight;

class Female extends \Fatty\Gender
{
	const ESSENTIAL_FAT_PERCENTAGE = 0.13;
	const FIT_BODY_FAT_PERCENTAGE = 0.25;
	const SPORT_PROTEIN_COEFFICIENT = 1.4;

	protected $isBreastfeeding;
	protected $isPregnant;
	protected $weightBeforePregnancy;

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
	 * Těhotenství.
	 */
	public function isPregnant(): bool
	{
		return (bool)$this->isPregnant;
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

	public function setWeightBeforePregnancy(?Weight $weight): Female
	{
		$this->weightBeforePregnancy = $weight;

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
	public function calcReferenceDailyIntakeBonus(): QuantityMetric
	{
		$exceptions = new \Fatty\Exceptions\FattyExceptionCollection;

		try {
			$referenceDailyIntakeBonusPregnancy = $this->calcReferenceDailyIntakeBonusPregnancy();
		} catch (\Throwable $e) {
			$exceptions->addException($e);
		}

		try {
			$referenceDailyIntakeBonusBreastfeeding = $this->calcReferenceDailyIntakeBonusBreastfeeding();
		} catch (\Throwable $e) {
			$exceptions->addException($e);
		}

		if ($exceptions->hasExceptions()) {
			throw $exceptions;
		}

		$result = new Energy(
			new Amount(
				$referenceDailyIntakeBonusPregnancy->getResult()->getInUnit("kcal")->getAmount()->getValue() + $referenceDailyIntakeBonusBreastfeeding->getResult()->getInUnit("kcal")->getAmount()->getValue()
			),
			"kcal",
		);

		return new QuantityMetric("referenceDailyIntakeBonus", $result);
	}

	public function calcReferenceDailyIntakeBonusPregnancy(): QuantityMetric
	{
		if (!$this->isPregnant()) {
			return new QuantityMetric("referenceDailyIntakeBonusPregnancy", new Energy(new Amount(0), "kJ"));
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

		return new QuantityMetric("referenceDailyIntakeBonusPregnancy", new Energy(new Amount($change), "kcal"));
	}

	public function calcReferenceDailyIntakeBonusBreastfeeding(): QuantityMetric
	{
		$exceptions = new \Fatty\Exceptions\FattyExceptionCollection;

		if (!$this->isBreastfeeding()) {
			return new QuantityMetric("referenceDailyIntakeBonusBreastfeeding", new Energy(new Amount(0), "kJ"));
		}

		if (!($this->getBreastfeedingChildbirthDate() instanceof Birthday)) {
			$exceptions->addException(new MissingBreastfeedingChildbirthDateException);
		}

		if (!($this->getBreastfeedingMode() instanceof BreastfeedingMode)) {
			$exceptions->addException(new MissingBreastfeedingModeException);
		}

		if ($exceptions->hasExceptions()) {
			throw $exceptions;
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

		return new QuantityMetric("referenceDailyIntakeBonusBreastfeeding", new Energy(new Amount($change), "kcal"));
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

	/****************************************************************************
	 * Sport protein matrix.
	 */
	public function getSportProteinMatrix(): array
	{
		return [
			"FIT" => [
				"LOW_FREQUENCY" => 1.4,
				"AEROBIC" => 1.6,
				"ANAEROBIC" => 1.8,
				"ANAEROBIC_SHORT" => 1.6,
				"ANAEROBIC_LONG" => 1.8,
			],
			"UNFIT" => [
				"LOW_FREQUENCY" => 1.5,
				"AEROBIC" => 1.8,
				"ANAEROBIC" => 1.8,
				"ANAEROBIC_SHORT" => 1.8,
				"ANAEROBIC_LONG" => 1.8,
			],
		];
	}
}
