<?php

namespace Fatty\Genders;

use Fatty\Amount;
use Fatty\ArrayValue;
use Fatty\BodyTypes\Apple;
use Fatty\BodyTypes\AppleWithHigherRisk;
use Fatty\BodyTypes\Balanced;
use Fatty\BodyTypes\PearOrHourglass;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\AmountMetricResult;
use Fatty\Metrics\ArrayMetricResult;
use Fatty\Metrics\BasalMetabolicRateMifflinStJeorAdjustmentMetric;
use Fatty\Metrics\BasalMetabolicRateMifflinStJeorWeightMetric;
use Fatty\Metrics\BasalMetabolicRateStrategyMetric;
use Fatty\Metrics\BodyFatPercentageMetric;
use Fatty\Metrics\BodyTypeMetric;
use Fatty\Metrics\BreastfeedingReferenceDailyIntakeBonusMetric;
use Fatty\Metrics\GoalNutrientProteinBonusMetric;
use Fatty\Metrics\PregnancyReferenceDailyIntakeBonusMetric;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\ReferenceDailyIntakeBonusMetric;
use Fatty\Metrics\SportProteinCoefficientMetric;
use Fatty\Metrics\SportProteinMatrixMetric;
use Fatty\Metrics\StringMetricResult;
use Fatty\Nutrients\Proteins;
use Fatty\Percentage;
use Fatty\StringValue;

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
	public function calcBodyFatPercentageByProportions(Calculator $calculator): AmountMetricResult
	{
		$result = new AmountMetricResult(new BodyFatPercentageMetric);

		$waistValue = $calculator->getProportions()->getWaist()->getInUnit("cm")->getAmount()->getValue();
		$neckValue = $calculator->getProportions()->getNeck()->getInUnit("cm")->getAmount()->getValue();
		$heightValue = $calculator->getProportions()->getHeight()->getInUnit("cm")->getAmount()->getValue();
		$hipsValue = $calculator->getProportions()->getHips()->getInUnit("cm")->getAmount()->getValue();

		$resultValue = ((495 / (1.29579 - (0.35004 * log10($waistValue + $hipsValue - $neckValue)) + (0.22100 * log10($heightValue)))) - 450) * 0.01;
		$percentage = new Percentage($resultValue);
		$formula = "
			((495 / (1.29579 - (0.35004 * log10(waist[{$waistValue}] + hips[{$hipsValue}] - neck[{$neckValue}])) + (0.22100 * log10(height[{$heightValue}])))) - 450) * 0.01
			= {$resultValue}
			";

		return $result->setResult($percentage)->setFormula($formula);
	}

	/****************************************************************************
	 * Basal metabolic rate.
	 */
	public function calcBasalMetabolicRateStrategy(Calculator $calculator): StringMetricResult
	{
		// Při těhotenství je zapotřebí použít Mifflin-StJeor kvůli rostoucímu břichu.
		if ($this->getIsPregnant($calculator)) {
			return (new StringMetricResult(new BasalMetabolicRateStrategyMetric))
				->setResult(new StringValue(static::BASAL_METABOLIC_RATE_STRATEGY_MIFFLIN_STJEOR))
				;
		}

		return parent::calcBasalMetabolicRateStrategy($calculator);
	}

	public function calcBasalMetabolicRateMifflinStJeorAdjustment(): QuantityMetricResult
	{
		return (new QuantityMetricResult(new BasalMetabolicRateMifflinStJeorAdjustmentMetric))
			->setResult(new Energy(new Amount(-161), "kcal"))
			;
	}

	public function calcBasalMetabolicRateMifflinStJeorWeight(Calculator $calculator): QuantityMetricResult
	{
		try {
			$weight = $this->getPregnancy()->getWeightBeforePregnancy();
		} catch (\Throwable $e) {
			// Nevermind.
		}

		$weight = ($weight ?? null) ?: $calculator->getWeight();

		return (new QuantityMetricResult(new BasalMetabolicRateMifflinStJeorWeightMetric))
			->setResult($weight)
			;
	}

	/*****************************************************************************
	 * Doporučený denní příjem - bonusy.
	 */
	public function calcReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new ReferenceDailyIntakeBonusMetric);

		$breastfeedingReferenceDailyIntakeBonusResult = $this->calcBreastfeedingReferenceDailyIntakeBonus($calculator);
		$result->addErrors($breastfeedingReferenceDailyIntakeBonusResult->getErrors());

		$pregnancyReferenceDailyIntakeBonusResult = $this->calcPregnancyReferenceDailyIntakeBonus($calculator);
		$result->addErrors($pregnancyReferenceDailyIntakeBonusResult->getErrors());

		if (!$result->hasErrors()) {
			$energy = (new Energy)
				->modify($breastfeedingReferenceDailyIntakeBonusResult->getResult())
				->modify($pregnancyReferenceDailyIntakeBonusResult->getResult())
				;

			$result->setResult($energy);
		}

		return $result;
	}

	public function calcPregnancyReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new PregnancyReferenceDailyIntakeBonusMetric);

		$energy = new Energy;
		$referenceTime = $calculator->getReferenceTime();

		$pregnancy = $this->getPregnancy();
		if ($pregnancy) {
			$trimester = $pregnancy->getCurrentTrimester($referenceTime);
			if ($trimester && in_array($trimester->getIndex(), [2, 3])) {
				$energy->modify(new Energy(new Amount(300), "kcal"));
			}
			if ($trimester && in_array($trimester->getIndex(), [3]) && $pregnancy->getNumberOfChildren() > 1) {
				$basalMetabolicRate = $calculator->calcBasalMetabolicRate()->getResult();
				$energy->modify(new Energy(new Amount($basalMetabolicRate->getNumericalValue() * .1), $basalMetabolicRate->getUnit()));
			}
		}

		$result->setResult($energy);

		return $result;
	}

	public function calcBreastfeedingReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new BreastfeedingReferenceDailyIntakeBonusMetric);

		$referenceDailyIntakeBonusResult = $this->getChildren()->calcReferenceDailyIntakeBonus($calculator);
		$result->addErrors($referenceDailyIntakeBonusResult->getErrors());

		if (!$result->hasErrors()) {
			$result->setResult($referenceDailyIntakeBonusResult->getResult());
		}

		return $result;
	}

	/*****************************************************************************
	 * Typ postavy.
	 */

	public function calcBodyType(Calculator $calculator): StringMetricResult
	{
		$result = new StringMetricResult(new BodyTypeMetric);

		$waistHipRatioResult = $calculator->calcWaistHipRatio();
		$result->addErrors($waistHipRatioResult->getErrors());

		if (!$result->hasErrors()) {
			$waistHipRatioValue = $waistHipRatioResult->getResult()->getNumericalValue();

			if ($waistHipRatioValue < .75) {
				$bodyType = new PearOrHourglass;
			} elseif ($waistHipRatioValue >= .75 && $waistHipRatioValue < .8) {
				$bodyType = new Balanced;
			} elseif ($waistHipRatioValue >= .8 && $waistHipRatioValue < .85) {
				$bodyType = new Apple;
			} else {
				$bodyType = new AppleWithHigherRisk;
			}

			$result->setResult(new StringValue($bodyType->getCode()));
		}

		return $result;
	}

	/****************************************************************************
	 * Sport durations.
	 */
	public function calcSportProteinSetKey(Calculator $calculator): StringMetricResult
	{
		$result = new StringMetricResult(new SportProteinCoefficientMetric);

		if ($this->getIsPregnant($calculator)) {
			$result->setResult(new StringValue("PREGNANT"));
		} elseif ($this->getIsNewMother($calculator)) {
			$result->setResult(new StringValue("NEW_MOTHER"));
		} else {
			return parent::calcSportProteinSetKey($calculator);
		}

		return $result;
	}

	public function calcSportProteinMatrix(): ArrayMetricResult
	{
		return (new ArrayMetricResult(new SportProteinMatrixMetric))
			->setResult(new ArrayValue([
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
			]))
			;
	}

	public function calcGoalNutrientProteinBonus(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new GoalNutrientProteinBonusMetric);

		$proteins = new Proteins(new Amount);

		if ($this->getIsPregnant($calculator) && $this->getPregnancy()->getNumberOfChildren() > 1) {
			$proteins->modify(new Proteins(new Amount(25), "g"));
		}

		if ($this->getIsBreastfeeding()) {
			if ($this->getIsPregnant($calculator) || $this->getIsNewMother($calculator)) {
				$proteins->modify(new Proteins(new Amount(20), "g"));
			}
		}

		$result->setResult($proteins);

		return $result;
	}
}
