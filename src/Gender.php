<?php

namespace Fatty;

use Fatty\Errors\MissingBirthdayError;
use Fatty\Errors\MissingBodyFatPercentageInputError;
use Fatty\Errors\MissingWeightError;
use Fatty\Metrics\AmountMetricResult;
use Fatty\Metrics\ArrayMetricResult;
use Fatty\Metrics\BasalMetabolicRateMetric;
use Fatty\Metrics\BasalMetabolicRateMifflinStJeorWeightMetric;
use Fatty\Metrics\BasalMetabolicRateStrategyMetric;
use Fatty\Metrics\BodyFatPercentageMetric;
use Fatty\Metrics\EssentialFatPercentageMetric;
use Fatty\Metrics\FitnessLevelMetric;
use Fatty\Metrics\GoalNutrientProteinBonusMetric;
use Fatty\Metrics\MaxOptimalWeightMetric;
use Fatty\Metrics\QuantityMetric;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\ReferenceDailyIntakeBonusMetric;
use Fatty\Metrics\SportProteinSetKeyMetric;
use Fatty\Metrics\StringMetricResult;
use Fatty\Nutrients\Proteins;
use Katu\Errors\Error;
use Katu\Tools\Calendar\Timeout;
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

	abstract public function calcBasalMetabolicRateMifflinStJeorAdjustment(): QuantityMetricResult;
	abstract public function calcBodyFatPercentageByProportions(Calculator $calculator): AmountMetricResult;
	abstract public function calcBodyType(Calculator $calculator): StringMetricResult;
	abstract public function calcSportProteinMatrix(): ArrayMetricResult;

	protected $children;
	protected $pregnancy;

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

	public function calcBodyFatPercentage(Calculator $calculator): AmountMetricResult
	{
		$result = new AmountMetricResult(new BodyFatPercentageMetric);

		$strategy = $this->getBodyFatPercentageStrategy($calculator);
		if (!$strategy) {
			$result->addError(new MissingBodyFatPercentageInputError);
		} else {
			switch ($strategy) {
				case static::BODY_FAT_PERCENTAGE_STRATEGY_MEASUREMENT:
					return $this->calcBodyFatPercentageByMeasurement($calculator);
					break;
				case static::BODY_FAT_PERCENTAGE_STRATEGY_PROPORTIONS:
					return $this->calcBodyFatPercentageByProportions($calculator);
					break;
			}
		}

		return $result;
	}

	public function calcBodyFatPercentageByMeasurement(Calculator $calculator): AmountMetricResult
	{
		return (new AmountMetricResult(new BodyFatPercentageMetric))
			->setResult($calculator->getBodyFatPercentage())
			;
	}

	public function calcEssentialFatPercentage(): AmountMetricResult
	{
		return (new AmountMetricResult(new EssentialFatPercentageMetric))
			->setResult(new Percentage((float)static::ESSENTIAL_FAT_PERCENTAGE))
			;
	}

	/****************************************************************************
	 * Basal metabolic rate.
	 */
	public function calcBasalMetabolicRate(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new BasalMetabolicRateMetric);

		$basalMetabolicRateStrategyResult = $this->calcBasalMetabolicRateStrategy($calculator);
		$result->addErrors($basalMetabolicRateStrategyResult->getErrors());

		if (!$result->hasErrors()) {
			switch ($basalMetabolicRateStrategyResult->getResult()->getStringValue()) {
				case static::BASAL_METABOLIC_RATE_STRATEGY_MIFFLIN_STJEOR:
					return $this->calcBasalMetabolicRateMifflinStJeor($calculator);
					break;
				case static::BASAL_METABOLIC_RATE_STRATEGY_KATCH_MCARDLE:
					return $this->calcBasalMetabolicRateKatchMcArdle($calculator);
					break;
			}
		}

		return $result;
	}

	public function calcBasalMetabolicRateStrategy(Calculator $calculator): StringMetricResult
	{
		return (new StringMetricResult(new BasalMetabolicRateStrategyMetric))
			->setResult(new StringValue(static::BASAL_METABOLIC_RATE_STRATEGY_KATCH_MCARDLE))
			;
	}

	public function calcBasalMetabolicRateKatchMcArdle(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new BasalMetabolicRateMetric);

		$fatFreeMassResult = $calculator->calcFatFreeMass();
		$result->addErrors($fatFreeMassResult->getErrors());

		if (!$result->hasErrors()) {
			$fatFreeMassValue = $fatFreeMassResult->getResult()->getNumericalValue();

			$energyValue = 370 + (21.6 * $fatFreeMassValue);

			$energy = (new Energy(
				new Amount($energyValue),
				"kcal",
			))->getInUnit($calculator->getUnits());

			$formula = "
				370 + (21.6 * fatFreeMass[{$fatFreeMassValue}])
				= 370 + " . (21.6 * $fatFreeMassValue) . "
				= {$energy->getInUnit("kcal")->getAmount()->getValue()} kcal
				= {$energy->getInUnit("kJ")->getAmount()->getValue()} kJ
			";

			$result->setResult($energy)->setFormula($formula);
		}

		return $result;
	}

	public function calcBasalMetabolicRateMifflinStJeor(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new BasalMetabolicRateMetric);

		$weightResult = $this->calcBasalMetabolicRateMifflinStJeorWeight($calculator);
		$result->addErrors($weightResult->getErrors());

		$heightResult = $calculator->calcHeight();
		$result->addErrors($heightResult->getErrors());

		$birthday = $calculator->getBirthday();
		if (!$birthday) {
			$result->addError(new MissingBirthdayError);
		}

		$basalMetabolicRateMifflinStJeorAdjustmentResult = $this->calcBasalMetabolicRateMifflinStJeorAdjustment();
		$result->addErrors($basalMetabolicRateMifflinStJeorAdjustmentResult->getErrors());

		if (!$result->hasErrors()) {
			$weightValue = $weightResult->getResult()->getInUnit("kg")->getNumericalValue();
			$heightValue = $heightResult->getResult()->getInUnit("cm")->getNumericalValue();
			$ageValue = $birthday->getAge($calculator->getReferenceTime());
			$basalMetabolicRateMifflinStJeorAdjustmentValue = $basalMetabolicRateMifflinStJeorAdjustmentResult->getResult()->getNumericalValue();

			$energy = (new Energy(
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
				= {$energy->getInUnit("kcal")->getAmount()->getValue()} kcal
				= {$energy->getInUnit("kJ")->getAmount()->getValue()} kJ
			";

			$result->setResult($energy)->setFormula($formula);
		}

		return $result;
	}

	public function calcBasalMetabolicRateMifflinStJeorWeight(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new BasalMetabolicRateMifflinStJeorWeightMetric);

		$weight = $calculator->getWeight();
		if (!$weight) {
			$result->addError(new MissingWeightError);
		} else {
			$result->setResult($weight);
		}

		return $result;
	}

	/*****************************************************************************
	 * Doporučený denní příjem - bonusy.
	 */
	public function calcReferenceDailyIntakeBonus(Calculator $calculator): QuantityMetricResult
	{
		return (new QuantityMetricResult(new ReferenceDailyIntakeBonusMetric))
			->setResult(new Energy)
			;
	}

	/****************************************************************************
	 * Fitness level.
	 */
	public function calcFitnessLevel(Calculator $calculator): StringMetricResult
	{
		$result = new StringMetricResult(new FitnessLevelMetric);

		$bodyFatPercentageResult = $calculator->calcBodyFatPercentage();
		$result->addErrors($bodyFatPercentageResult->getErrors());

		if (!$result->hasErrors()) {
			$bodyFatPercentageValue = $bodyFatPercentageResult->getResult()->getNumericalValue();
			$fitBodyFatPercentage = static::FIT_BODY_FAT_PERCENTAGE;

			$string = $bodyFatPercentageResult->getResult()->getNumericalValue() > $fitBodyFatPercentage ? "UNFIT" : "FIT";
			$formula = "bodyFatPercentage[{$bodyFatPercentageValue}] > {$fitBodyFatPercentage} ? UNFIT : FIT";

			$result->setResult(new StringValue($string))->setFormula($formula);
		}

		return $result;
	}

	public function calcMaxOptimalWeight(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new MaxOptimalWeightMetric);

		$fitnessLevelResult = $calculator->calcFitnessLevel();
		$result->addErrors($fitnessLevelResult->getErrors());

		if (!$result->hasErrors()) {
			if ($fitnessLevelResult->getResult()->getStringValue() == "UNFIT") {
				$fatFreeMassResult = $calculator->calcFatFreeMass();
				$result->addErrors($fatFreeMassResult->getErrors());

				if (!$result->hasErrors()) {
					$fatFreeMassValue = $fatFreeMassResult->getResult()->getInUnit("kg")->getNumericalValue();
					$fitBodyFatPercentage = static::FIT_BODY_FAT_PERCENTAGE;

					$value = $fatFreeMassValue * (1 + $fitBodyFatPercentage);

					$weight = new Weight(new Amount($value), "kg");

					$formula = "
						fatFreeMass[{$fatFreeMassValue}] * (1 + fitBodyFatPercentage[{$fitBodyFatPercentage}])
						= {$fatFreeMassValue} * " . (1 + $fitBodyFatPercentage) . "
						= {$value} kg
					";

					$result->setResult($weight)->setFormula($formula);
				}
			} else {
				$weight = $calculator->getWeight();
				if (!$weight) {
					$result->addError(new MissingWeightError);
				} else {
					$formula = "weight[$weight]";

					$result->setResult($weight)->setFormula($formula);
				}
			}
		}

		return $result;
	}

	public function calcSportProteinSetKey(Calculator $calculator): StringMetricResult
	{
		$result = new StringMetricResult(new SportProteinSetKeyMetric);

		$fitnessLevelResult = $calculator->calcFitnessLevel();
		$result->addErrors($fitnessLevelResult->getErrors());

		if (!$result->hasErrors()) {
			$result->setResult($fitnessLevelResult->getResult());
		}

		return $result;
	}

	public function calcGoalNutrientProteinBonus(Calculator $calculator): QuantityMetricResult
	{
		return (new QuantityMetricResult(new GoalNutrientProteinBonusMetric))
			->setResult(new Proteins(new Amount))
			;
	}

	/*****************************************************************************
	 * Těhotenství.
	 */
	public function setPregnancy(?Pregnancy $pregnancy): Gender
	{
		$this->pregnancy = $pregnancy;

		return $this;
	}

	public function getPregnancy(): ?Pregnancy
	{
		return $this->pregnancy;
	}

	public function getIsPregnant(Calculator $calculator): bool
	{
		$pregnancy = $this->getPregnancy();
		if ($pregnancy) {
			return $pregnancy->getIsPregnant($calculator->getReferenceTime());
		}

		return false;
	}

	public function getIsNewMother(Calculator $calculator): bool
	{
		return (bool)count($this->getChildren()->filterYoungerThan(new Timeout("6 months", $calculator->getReferenceTime())));
	}

	/*****************************************************************************
	 * Kojení.
	 */
	public function setChildren(?ChildCollection $children): Gender
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

	public function getIsBreastfeeding(): bool
	{
		return (bool)count($this->getChildren()->filterBreastfed());
	}
}
