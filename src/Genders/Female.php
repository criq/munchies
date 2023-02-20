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
use Fatty\Nutrients\Proteins;
use Fatty\Percentage;
use Katu\Tools\Calendar\Timeout;

class Female extends \Fatty\Gender
{
	const ESSENTIAL_FAT_PERCENTAGE = 0.13;
	const FIT_BODY_FAT_PERCENTAGE = 0.25;
	const PREGNANCY_SPORT_PROTEIN_COEFFICIENT = 1.8;

	protected $children;
	protected $pregnancy;

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	public function calcBodyFatPercentageByProportions(Calculator $calculator): AmountMetric
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
	public function calcBasalMetabolicRateStrategy(Calculator $calculator): StringMetric
	{
		// Při těhotenství je zapotřebí použít Mifflin-StJeor kvůli rostoucímu břichu.
		if ($this->getIsPregnant($calculator)) {
			return new StringMetric(
				"basalMetabolicRateStrategy",
				static::BASAL_METABOLIC_RATE_STRATEGY_MIFFLIN_STJEOR,
			);
		}

		return parent::calcBasalMetabolicRateStrategy($calculator);
	}

	public function calcBasalMetabolicRateMifflinStJeorAdjustment(): QuantityMetric
	{
		return new QuantityMetric(
			"basalMetabolicRateMifflinStJeorAdjustment",
			new Energy(new Amount(-161), "kcal"),
		);
	}

	public function calcBasalMetabolicRateMifflinStJeorWeight(Calculator $calculator): QuantityMetric
	{
		try {
			$weight = $this->getPregnancy()->getWeightBeforePregnancy();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		$weight = ($weight ?? null) ?: $calculator->getWeight();

		return new QuantityMetric(
			"basalMetabolicRateMifflinStJeorWeight",
			$weight,
		);
	}

	/*****************************************************************************
	 * Doporučený denní příjem - bonusy.
	 */
	public function calcReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetric
	{
		$energy = (new Energy)
			->modify($this->calcBreastfeedingReferenceDailyIntakeBonus($calculator)->getResult())
			->modify($this->calcPregnancyReferenceDailyIntakeBonus($calculator)->getResult())
			;

		return new QuantityMetric("referenceDailyIntakeBonus", $energy);
	}

	public function calcPregnancyReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetric
	{
		$energy = new Energy;
		$referenceTime = $calculator->getReferenceTime();

		$pregnancy = $this->getPregnancy();
		if ($pregnancy) {
			$trimester = $pregnancy->getCurrentTrimester($referenceTime);
			if ($trimester && in_array($trimester->getIndex(), [2, 3])) {
				$energy->modify(new Energy(new Amount(300), "kcal"));
			}
		}

		return new QuantityMetric("pregnancyReferenceDailyIntakeBonus", $energy);
	}

	public function calcBreastfeedingReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetric
	{
		$energy = $this->getChildren()->calcReferenceDailyIntakeBonus($calculator)->getResult();

		return new QuantityMetric("breastfeedingReferenceDailyIntakeBonus", $energy);
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
	 * Sport durations.
	 */
	public function calcSportProteinSetKey(Calculator $calculator): StringMetric
	{
		if ($this->getIsPregnant($calculator)) {
			return new StringMetric(
				"sportProteinSetKey",
				"PREGNANT",
			);
		} elseif ($this->getIsNewMother($calculator)) {
			return new StringMetric(
				"sportProteinSetKey",
				"NEW_MOTHER",
			);
		}

		return parent::calcSportProteinSetKey($calculator);
	}

	public function getSportProteinMatrix(): array
	{
		return [
			"FIT" => [
				"NO_ACTIVITY" => 1.4,
				"LOW_FREQUENCY" => 1.4,
				"AEROBIC" => 1.6,
				"ANAEROBIC" => 1.8,
				"ANAEROBIC_SHORT" => 1.6,
				"ANAEROBIC_LONG" => 1.8,
			],
			"UNFIT" => [
				"NO_ACTIVITY" => 1.4,
				"LOW_FREQUENCY" => 1.5,
				"AEROBIC" => 1.8,
				"ANAEROBIC" => 1.8,
				"ANAEROBIC_SHORT" => 1.8,
				"ANAEROBIC_LONG" => 1.8,
			],
			"PREGNANT" => [
				"NO_ACTIVITY" => 1.8,
				"LOW_FREQUENCY" => 1.9,
				"AEROBIC" => 1.9,
				"ANAEROBIC" => 2.2,
				"ANAEROBIC_SHORT" => 2.2,
				"ANAEROBIC_LONG" => 2.2,
			],
			"NEW_MOTHER" => [
				"NO_ACTIVITY" => 1.8,
				"LOW_FREQUENCY" => 1.9,
				"AEROBIC" => 1.9,
				"ANAEROBIC" => 2.2,
				"ANAEROBIC_SHORT" => 2.2,
				"ANAEROBIC_LONG" => 2.2,
			],
		];
	}

	public function calcGoalNutrientProteinBonus(Calculator $calculator): QuantityMetric
	{
		$proteins = new Proteins(new Amount);

		if ($this->getIsBreastfeeding()) {
			if ($this->getIsPregnant($calculator) || $this->getIsNewMother($calculator)) {
				$proteins->modify(new Proteins(new Amount(20), "g"));
			}
		}

		return new QuantityMetric(
			"goalNutrientProteinBonus",
			$proteins,
		);
	}
}
