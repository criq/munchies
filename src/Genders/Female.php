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
use Fatty\ChildCollection;
use Fatty\Energy;
use Fatty\Exceptions\MissingBreastfeedingChildbirthDateException;
use Fatty\Exceptions\MissingBreastfeedingModeException;
use Fatty\Exceptions\MissingPregnancyChildbirthDateException;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\QuantityMetric;
use Fatty\Metrics\StringMetric;
use Fatty\Percentage;
use Fatty\Pregnancy;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Calendar\Timeout;

class Female extends \Fatty\Gender
{
	const BASAL_METABOLIC_RATE_STRATEGY_SIMPLIFIED = "simplified";
	const BASAL_METABOLIC_RATE_STRATEGY_STANDARD = "standard";
	const ESSENTIAL_FAT_PERCENTAGE = 0.13;
	const FIT_BODY_FAT_PERCENTAGE = 0.25;
	const SPORT_PROTEIN_COEFFICIENT = 1.4;

	protected $children;
	protected $pregnancy;

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	protected function calcBodyFatPercentageByProportions(Calculator $calculator): AmountMetric
	{
		$waistValue = $calculator->getProportions()->getWaist()->getInUnit("cm")->getAmount()->getValue();
		$neckValue = $calculator->getProportions()->getNeck()->getInUnit("cm")->getAmount()->getValue();
		$heightValue = $calculator->getProportions()->getHeight()->getInUnit("cm")->getAmount()->getValue();
		$hipsValue = $calculator->getProportions()->getHips()->getInUnit("cm")->getAmount()->getValue();

		$resultValue = ((495 / (1.29579 - (0.35004 * log10($waistValue + $hipsValue - $neckValue)) + (0.22100 * log10($heightValue)))) - 450) * 0.01;
		$result = new Percentage($resultValue);
		$formula = "
			((495 / (1.29579 - (0.35004 * log10(waist[{$waistValue}] + hips[{$hipsValue}] - neck[{$neckValue}])) + (0.22100 * log10(height[{$heightValue}])))) - 450) * 0.01
			= {$resultValue}
			";

		return new AmountMetric("bodyFatPercentage", $result, $formula);
	}

	/****************************************************************************
	 * Basal metabolic rate.
	 */
	public function getBasalMetabolicRateStrategy(Calculator $calculator): ?string
	{
		// Lze použít standardní výpočet?
		// return static::BASAL_METABOLIC_RATE_STRATEGY_STANDARD;

		// Lze použít zjednodušený výpočet?
		if ($this->getIsPregnant() && count($this->getChildren()->filterYoungerThan(new Timeout("6 months")))) {
			try {
				$weightBeforePregnancy = $this->getPregnancy()->getWeightBeforePregnancy();
			} catch (\Throwable $e) {
				// Nevermind.
			}

			try {
				$height = $calculator->getProportions()->getHeight();
			} catch (\Throwable $e) {
				// Nevermind.
			}

			try {
				$age = $calculator->getBirthday()->getAge();
			} catch (\Throwable $e) {
				// Nevermind.
			}

			if (($weightBeforePregnancy ?? null) && ($height ?? null) && ($age ?? null)) {
				return static::BASAL_METABOLIC_RATE_STRATEGY_SIMPLIFIED;
			}
		}

		return null;
	}

	public function calcBasalMetabolicRate(Calculator $calculator): QuantityMetric
	{
		var_dump($this->getBasalMetabolicRateStrategy($calculator));die;
		// Ženy těhotné nebo do 6 měsíců po porodu:

		var_dump($this);die;

		return parent::calcBasalMetabolicRate($calculator);
	}

	/*****************************************************************************
	 * Těhotenství.
	 */
	public function setPregnancy(?Pregnancy $pregnancy): Female
	{
		$this->pregnancy = $pregnancy;

		return $this;
	}

	public function getPregnancy(): ?Pregnancy
	{
		return $this->pregnancy;
	}

	public function getIsPregnant(): bool
	{
		try {
			return $this->getPregnancy()->getIsPregnant();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return false;
	}

	/*****************************************************************************
	 * Kojení.
	 */
	public function setChildren(?ChildCollection $children): Female
	{
		$this->children = $children;

		return $this;
	}

	public function getChildren(): ChildCollection
	{
		if (!$this->children) {
			$this->children = new ChildCollection;
		}

		return $this->children;
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
		if (!$this->getIsPregnant()) {
			return new QuantityMetric("referenceDailyIntakeBonusPregnancy", new Energy(new Amount(0), "kJ"));
		}

		if (!($this->getChildbirthDate() instanceof Birthday)) {
			throw new MissingPregnancyChildbirthDateException;
		}

		$diff = $this->getChildbirthDate()->diff(new Time);
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

		$diff = $this->getBreastfeedingChildbirthDate()->diff(new Time);
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
