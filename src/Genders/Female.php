<?php

namespace Fatty\Genders;

use Fatty\Amount;
use Fatty\BodyTypes\Apple;
use Fatty\BodyTypes\AppleWithHigherRisk;
use Fatty\BodyTypes\Balanced;
use Fatty\BodyTypes\PearOrHourglass;
use Fatty\Calculator;
use Fatty\ChildCollection;
use Fatty\Energy;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\QuantityMetric;
use Fatty\Metrics\StringMetric;
use Fatty\Percentage;
use Fatty\Pregnancy;
use Katu\Tools\Calendar\Timeout;

class Female extends \Fatty\Gender
{
	const BASAL_METABOLIC_RATE_STRATEGY_MIFFLIN_STJEOR = "msj";
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
	public function getBasalMetabolicRateStrategy(Calculator $calculator): string
	{
		// Lze použít standardní výpočet?
		try {
			// Zkusme použít standardní výpočet...
			parent::calcBasalMetabolicRate($calculator);

			return static::BASAL_METABOLIC_RATE_STRATEGY_STANDARD;
		} catch (\Throwable $e) {
			// Nevermind.
		}

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
				return static::BASAL_METABOLIC_RATE_STRATEGY_MIFFLIN_STJEOR;
			}
		}

		return static::BASAL_METABOLIC_RATE_STRATEGY_STANDARD;
	}

	public function calcBasalMetabolicRate(Calculator $calculator): QuantityMetric
	{
		switch ($this->getBasalMetabolicRateStrategy($calculator)) {
			// Ženy těhotné nebo do 6 měsíců po porodu:
			case static::BASAL_METABOLIC_RATE_STRATEGY_MIFFLIN_STJEOR:
				return $this->calcBasalMetabolicRateSimplified($calculator);
				break;
			default:
				return $this->calcBasalMetabolicRateStandard($calculator);
				break;
		}
	}

	public function calcBasalMetabolicRateStandard(Calculator $calculator): QuantityMetric
	{
		return parent::calcBasalMetabolicRate($calculator);
	}

	public function calcBasalMetabolicRateSimplified(Calculator $calculator): QuantityMetric
	{
		$weightBeforePregnancyAmount = $this->getPregnancy()->getWeightBeforePregnancy()->getInUnit("kg")->getAmount()->getValue();
		$heightAmount = $calculator->getProportions()->getHeight()->getInUnit("cm")->getAmount()->getValue();
		$ageAmount = $calculator->getBirthday()->getAge();

		$result = (new Energy(
			new Amount(
				(10 * $weightBeforePregnancyAmount)
				+ (6.25 * $heightAmount)
				- (5 * $ageAmount)
				- 161
			),
			"kcal",
		))->getInUnit($calculator->getUnits());

		$formula = "
			(10 * weight[$weightBeforePregnancyAmount]) + (6.25 * height[$heightAmount]) - (5 * age[$ageAmount]) - 161
			= " . (10 * $weightBeforePregnancyAmount) . " + " . (6.25 * $heightAmount) . " - " . (5 * $ageAmount) . " - 161
			= {$result->getInUnit("kcal")->getAmount()->getValue()} kcal
			= {$result->getInUnit("kJ")->getAmount()->getValue()} kJ
		";

		return new QuantityMetric(
			"basalMetabolicRate",
			$result,
			$formula
		);
	}

	/*****************************************************************************
	 * Doporučený denní příjem - bonusy.
	 */
	public function calcReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetric
	{
		$energy = new Energy(new Amount(0), "kcal");

		// Referece Daily Intake (RDI): K doporučenému příjmu se následně mohou přidat energetické bonusy za:

		// a) kojení - pokud žena kojí, viz. tabulka výše



		// b) těhotenství - v závislosti na pokročilosti těhotenství…
		// 1. trimestr - bonus 0 Kcal
		// 2. trimestr - bonus 300 Kcal
		// 3. trimestr - bonus 300 Kcal

		$referenceDate = $calculator->getReferenceDate();
		$trimester = $this->getPregnancy()->getCurrentTrimester($referenceDate);
		if (in_array($trimester->getIndex(), [2, 3])) {
			$energy = $energy->modify(new Energy(new Amount(300), "kcal"));
		}

		return new QuantityMetric("referenceDailyIntakeBonus", $energy);
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
