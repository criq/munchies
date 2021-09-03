<?php

namespace Fatty;

use Fatty\Approaches\Keto;
use Fatty\Approaches\LowCarb;
use Fatty\Approaches\Ned;
use Fatty\Approaches\Standard;
use Fatty\Exceptions\FattyException;
use Fatty\Exceptions\FattyExceptionCollection;
use Fatty\Exceptions\InvalidActivityException;
use Fatty\Exceptions\InvalidBirthdayException;
use Fatty\Exceptions\InvalidBodyFatPercentageException;
use Fatty\Exceptions\InvalidDietApproachException;
use Fatty\Exceptions\InvalidDietCarbsException;
use Fatty\Exceptions\InvalidGenderException;
use Fatty\Exceptions\InvalidGoalVectorException;
use Fatty\Exceptions\InvalidGoalWeightException;
use Fatty\Exceptions\InvalidHeightException;
use Fatty\Exceptions\InvalidHipsException;
use Fatty\Exceptions\InvalidNeckException;
use Fatty\Exceptions\InvalidSportDurationsAerobicException;
use Fatty\Exceptions\InvalidSportDurationsAnaerobicException;
use Fatty\Exceptions\InvalidSportDurationsLowFrequencyException;
use Fatty\Exceptions\InvalidUnitsException;
use Fatty\Exceptions\InvalidWaistException;
use Fatty\Exceptions\InvalidWeightException;
use Fatty\Exceptions\MissingDietApproachException;
use Fatty\Exceptions\MissingGenderException;
use Fatty\Exceptions\MissingGoalVectorException;
use Fatty\Exceptions\MissingHeightException;
use Fatty\Exceptions\MissingHipsException;
use Fatty\Exceptions\MissingWaistException;
use Fatty\Exceptions\MissingWeightException;
use Fatty\Exceptions\UnableToCalcEssentialFatPercentageException;
use Fatty\Exceptions\UnableToCalcOptimalFatPercentageException;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\AmountWithUnitMetric;
use Fatty\Metrics\StringMetric;
use Fatty\Nutrients\Carbs;
use Fatty\Nutrients\Fats;
use Fatty\SportDurations\Aerobic;
use Fatty\SportDurations\Anaerobic;
use Fatty\SportDurations\LowFrequency;

class Calculator
{
	protected $activity;
	protected $birthday;
	protected $bodyFatPercentage;
	protected $diet;
	protected $gender;
	protected $goal;
	protected $params;
	protected $proportions;
	protected $sportDurations;
	protected $units = 'kJ';
	protected $weight;

	public function __construct(?array $params = [])
	{
		$this->setParams($params);
	}

	public static function createFromParams(array $params): Calculator
	{
		$object = new static;
		$object->setParams($params);

		return $object;
	}

	public function setParams(array $params): Calculator
	{
		$this->params = $params;

		$exceptionCollection = new FattyExceptionCollection;

		if (trim($params['gender'] ?? null)) {
			try {
				$value = \Fatty\Gender::createFromString($params['gender']);
				if (!$value) {
					throw new InvalidGenderException;
				}

				$this->setGender($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['birthday'] ?? null)) {
			try {
				$value = \Fatty\Birthday::createFromString($params['birthday']);
				if (!$value) {
					throw new InvalidBirthdayException;
				}

				$this->setBirthday($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['weight'] ?? null)) {
			try {
				$value = Weight::createFromString($params['weight'], 'kg');
				if (!$value) {
					throw new InvalidWeightException;
				}

				$this->setWeight($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['proportions_height'] ?? null)) {
			try {
				$value = Length::createFromString($params['proportions_height'], 'cm');
				if (!$value) {
					throw new InvalidHeightException;
				}

				$this->getProportions()->setHeight($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['proportions_waist'] ?? null)) {
			try {
				$value = Length::createFromString($params['proportions_waist'], 'cm');
				if (!$value) {
					throw new InvalidWaistException;
				}

				$this->getProportions()->setWaist($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['proportions_hips'] ?? null)) {
			try {
				$value = Length::createFromString($params['proportions_hips'], 'cm');
				if (!$value) {
					throw new InvalidHipsException;
				}

				$this->getProportions()->setHips($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['proportions_neck'] ?? null)) {
			try {
				$value = Length::createFromString($params['proportions_neck'], 'cm');
				if (!$value) {
					throw new InvalidNeckException;
				}

				$this->getProportions()->setNeck($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['bodyFatPercentage'] ?? null)) {
			try {
				$value = Percentage::createFromPercent($params['bodyFatPercentage']);
				if (!$value) {
					throw new InvalidBodyFatPercentageException;
				}

				$this->setBodyFatPercentage($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['activity'] ?? null)) {
			try {
				$value = Activity::createFromString($params['activity']);
				if (!$value) {
					throw new InvalidActivityException;
				}

				$this->setActivity($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['sportDurations_lowFrequency'] ?? null)) {
			try {
				$value = LowFrequency::createFromString($params['sportDurations_lowFrequency'], 'minutesPerWeek');
				if (!$value) {
					throw new InvalidSportDurationsLowFrequencyException;
				}

				$this->getSportDurations()->setLowFrequency($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['sportDurations_aerobic'] ?? null)) {
			try {
				$value = Aerobic::createFromString($params['sportDurations_aerobic'], 'minutesPerWeek');
				if (!$value) {
					throw new InvalidSportDurationsAerobicException;
				}

				$this->getSportDurations()->setAerobic($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['sportDurations_anaerobic'] ?? null)) {
			try {
				$value = Anaerobic::createFromString($params['sportDurations_anaerobic'], 'minutesPerWeek');
				if (!$value) {
					throw new InvalidSportDurationsAnaerobicException;
				}

				$this->getSportDurations()->setAnaerobic($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		$this->getGoal()->setDuration(new Duration(new Amount(12), 'weeks'));

		if (trim($params['goal_vector'] ?? null)) {
			try {
				$value = Vector::createFromString($params['goal_vector']);
				if (!$value) {
					throw new InvalidGoalVectorException;
				}

				$this->getGoal()->setVector($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['goal_weight'] ?? null)) {
			try {
				$value = Weight::createFromString($params['goal_weight'], 'kg');
				if (!$value) {
					throw new InvalidGoalWeightException;
				}

				$this->getGoal()->setWeight($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (trim($params['diet_approach'] ?? null)) {
			try {
				$value = Approach::createFromString($params['diet_approach']);
				if (!$value) {
					throw new InvalidDietApproachException;
				}

				$this->getDiet()->setApproach($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		$params['diet_carbs'] = 80;
		if (trim($params['diet_carbs'] ?? null)) {
			try {
				$value = Carbs::createFromString($params['diet_carbs'], 'g');
				if (!$value) {
					throw new InvalidDietCarbsException;
				}

				$this->getDiet()->setCarbs($value);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		// if ($this->getGender() instanceof \App\Classes\Profile\Genders\Female) {
		// 	if (isset($params['pregnancyIsPregnant']) && $params['pregnancyIsPregnant']) {
		// 		$this->getGender()->setIsPregnant(true);

		// 		if (isset($params['pregnancyChildbirthDate'])) {
		// 			try {
		// 				$this->getGender()->setPregnancyChildbirthDate($params['pregnancyChildbirthDate']);
		// 			} catch (FattyException $e) {
		// 				$exceptionCollection->add($e);
		// 			}
		// 		}
		// 	}

		// 	if (isset($params['breastfeedingIsBreastfeeding']) && $params['breastfeedingIsBreastfeeding']) {
		// 		$this->getGender()->setIsBreastfeeding(true);

		// 		if (isset($params['breastfeeding']['childbirthDate'])) {
		// 			try {
		// 				$this->getGender()->setBreastfeedingChildbirthDate($params['breastfeeding']['childbirthDate']);
		// 			} catch (FattyException $e) {
		// 				$exceptionCollection->add($e);
		// 			}
		// 		}

		// 		if (isset($params['breastfeedingMode'])) {
		// 			try {
		// 				$this->getGender()->setBreastfeedingMode($params['breastfeedingMode']);
		// 			} catch (FattyException $e) {
		// 				$exceptions->add($e);
		// 			}
		// 		}
		// 	}
		// }

		if (trim($params['units'] ?? null)) {
			try {
				$this->setUnits($params['units']);
			} catch (FattyException $e) {
				$exceptionCollection->add($e);
			}
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		return $this;
	}

	public function getParams(): array
	{
		return $this->params;
	}

	public static function getDeviation($value, $ideal, $extremes): Amount
	{
		try {
			$deviation = $value - $ideal;
			$range = $deviation < 0 ? [$extremes[0], $ideal] : [$ideal, $extremes[1]];
			$res = $deviation / ($range[1] - $range[0]);

			if ($res < -1) {
				$res = -1;
			}
			if ($res > 1) {
				$res = 1;
			}

			return new Amount($res);
		} catch (FattyException $e) {
			return new Amount(0);
		}
	}

	public function getIsOverweight(): bool
	{
		return (bool)$this->calcFatOverOptimalWeight()->filterByName('fatOverOptimalWeightMax')[0]->getResult()->getAmount()->getValue();
	}

	/*****************************************************************************
	 * Units.
	 */
	public function setUnits(string $value): Calculator
	{
		if (!in_array($value, ['kJ', 'kcal'])) {
			throw new InvalidUnitsException;
		}

		$this->units = $value;

		return $this;
	}

	public function getUnits(): string
	{
		return $this->units;
	}

	/*****************************************************************************
	 * Gender.
	 */
	public function setGender(?Gender $value): Calculator
	{
		$this->gender = $value;

		return $this;
	}

	public function getGender(): ?Gender
	{
		return $this->gender;
	}

	/*****************************************************************************
	 * Birthday.
	 */
	public function setBirthday(?Birthday $value): Calculator
	{
		$this->birthday = $value;

		return $this;
	}

	public function getBirthday(): ?Birthday
	{
		return $this->birthday;
	}

	/*****************************************************************************
	 * Weight.
	 */
	public function setWeight(?Weight $value): Calculator
	{
		$this->weight = $value;

		return $this;
	}

	public function getWeight(): ?Weight
	{
		return $this->weight;
	}

	public function calcWeight(): ?Metric
	{
		return new AmountWithUnitMetric('weight', $this->getWeight());
	}

	/*****************************************************************************
	 * Proportions.
	 */
	public function getProportions(): Proportions
	{
		$this->proportions = $this->proportions instanceof Proportions ? $this->proportions : new Proportions;

		return $this->proportions;
	}

	/*****************************************************************************
	 * Body fat percentage.
	 */
	public function setBodyFatPercentage(?Percentage $value): Calculator
	{
		$this->bodyFatPercentage = $value;

		return $this;
	}

	public function getBodyFatPercentage(): ?Percentage
	{
		return $this->bodyFatPercentage;
	}

	public function calcBodyFatPercentage(): AmountMetric
	{
		$gender = $this->getGender();
		if (!$gender) {
			throw new MissingGenderException;
		}

		return $this->getGender()->calcBodyFatPercentage($this);
	}

	/*****************************************************************************
	 * Activity.
	 */
	public function setActivity(?Activity $activity): Calculator
	{
		$this->activity = $activity;

		return $this;
	}

	public function getActivity(): ?Activity
	{
		return $this->activity;
	}

	public function calcActivity(): Activity
	{
		return $this->activity ?: new Activity(0);
	}

	public function getSportDurations(): SportDurations
	{
		$this->sportDurations = $this->sportDurations instanceof SportDurations ? $this->sportDurations : new SportDurations;

		return $this->sportDurations;
	}

	public function calcSportActivity(): Activity
	{
		return $this->getSportDurations()->calcSportActivity();
	}

	/*****************************************************************************
	 * Physical activity level.
	 */
	public function calcPhysicalActivityLevel(): AmountMetric
	{
		$exceptionCollection = new FattyExceptionCollection;

		try {
			$activity = $this->calcActivity();
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$sportActivity = $this->calcSportActivity();
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		$result = new Activity($activity->getValue() + $sportActivity->getValue());
		$formula = 'activityPal[' . $this->calcActivity()->getValue() . '] + sportPal[' . $this->calcSportActivity()->getValue() . '] = ' . $result->getValue();

		return new AmountMetric('physicalActivityLevel', $result, $formula);
	}

	/*****************************************************************************
	 * Goal.
	 */
	public function getGoal(): Goal
	{
		$this->goal = $this->goal instanceof Goal ? $this->goal : new Goal;

		return $this->goal;
	}

	/*****************************************************************************
	 * Diet.
	 */
	public function getDiet(): Diet
	{
		$this->diet = $this->diet instanceof Diet ? $this->diet : new Diet;

		return $this->diet;
	}

	/*****************************************************************************
	 * Body mass index - BMI.
	 */
	public function calcBodyMassIndex(): AmountMetric
	{
		$exceptionCollection = new FattyExceptionCollection;

		if (!($this->getWeight() instanceof Weight)) {
			$exceptionCollection->add(new MissingWeightException);
		}

		if (!($this->getProportions()->getHeight() instanceof Length)) {
			$exceptionCollection->add(new MissingHeightException);
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		$weight = $this->getWeight()->getInUnit('kg')->getAmount()->getValue();
		$height = $this->getProportions()->getHeight()->getInUnit('m')->getAmount()->getValue();

		$result = new Amount($weight / pow($height, 2));
		$formula = 'weight[' . $weight . '] / pow(height[' . $height . '], 2) = ' . $result->getValue();

		return new AmountMetric('bodyMassIndex', $result, $formula);
	}

	public function calcBodyMassIndexDeviation(): AmountMetric
	{
		return new AmountMetric('bodyMassIndexDeviation', static::getDeviation($this->calcBodyMassIndex()->getResult()->getValue(), 22, [17.7, 40]));
	}

	/*****************************************************************************
	 * Waist-hip ratio - WHR.
	 */
	public function calcWaistHipRatio(): AmountMetric
	{
		$exceptionCollection = new FattyExceptionCollection;

		if (!($this->getProportions()->getWaist() instanceof Length)) {
			$exceptionCollection->add(new MissingWaistException);
		}

		if (!($this->getProportions()->getHips() instanceof Length)) {
			$exceptionCollection->add(new MissingHipsException);
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		$waist = $this->getProportions()->getWaist()->getInUnit('cm')->getAmount()->getValue();
		$hips = $this->getProportions()->getHips()->getInUnit('cm')->getAmount()->getValue();

		$result = new Amount($waist / $hips);
		$formula = 'waist[' . $waist . '] / hips[' . $hips . '] = ' . $result->getValue();

		return new AmountMetric('waistHipRatio', $result, $formula);
	}

	public function calcWaistHipRatioDeviation(): AmountMetric
	{
		$waistHipRatio = $this->calcWaistHipRatio()->getResult()->getValue();

		if ($this->getGender() instanceof Genders\Male) {
			$result = static::getDeviation($waistHipRatio, .8, [.8, .95]);
		} elseif ($this->getGender() instanceof Genders\Female) {
			$result = static::getDeviation($waistHipRatio, .9, [.9, 1]);
		}

		return new AmountMetric('waistHipRatioDeviation', $result);
	}

	/*****************************************************************************
	 * Míra rizika.
	 */
	public function calcRiskDeviation(): AmountMetric
	{
		$gender = $this->getGender();
		$bodyMassIndex = $this->calcBodyMassIndex()->getResult()->getValue();
		$waistHipRatio = $this->calcWaistHipRatio()->getResult()->getValue();
		$isOverweight = $this->getIsOverweight();

		if (($gender instanceof Genders\Male && $waistHipRatio < .8 && !$isOverweight)
			|| ($gender instanceof Genders\Female && $waistHipRatio < .9 && !$isOverweight)
		) {
			$column = "A";
		} elseif (($gender instanceof Genders\Male && $waistHipRatio < .8 && $isOverweight)
			|| ($gender instanceof Genders\Female && $waistHipRatio < .9 && $isOverweight)
		) {
			$column = "B";
		} elseif (($gender instanceof Genders\Male && $waistHipRatio >= .8 && $waistHipRatio <= .95 && !$isOverweight)
			|| ($gender instanceof Genders\Female && $waistHipRatio >= .9 && $waistHipRatio <= 1 && !$isOverweight)
		) {
			$column = "C";
		} elseif (($gender instanceof Genders\Male && $waistHipRatio >= .8 && $waistHipRatio <= .95 && $isOverweight)
			|| ($gender instanceof Genders\Female && $waistHipRatio >= .9 && $waistHipRatio <= 1 && $isOverweight)
		) {
			$column = "D";
		} else {
			$column = "E";
		}

		if ($bodyMassIndex < 17.7) {
			$row = 1;
		} elseif ($bodyMassIndex >= 17.7 && $bodyMassIndex < 18) {
			$row = 2;
		} elseif ($bodyMassIndex >= 18 && $bodyMassIndex < 25) {
			$row = 3;
		} elseif ($bodyMassIndex >= 25 && $bodyMassIndex < 30) {
			$row = 4;
		} elseif ($bodyMassIndex >= 30 && $bodyMassIndex < 35) {
			$row = 5;
		} elseif ($bodyMassIndex >= 35 && $bodyMassIndex < 40) {
			$row = 6;
		} else {
			$row = 7;
		}

		$matrix = [
			'A' => [1 => -1, -.5,   0,   0,   0,   0,   0],
			'B' => [1 =>  1,  .5,  .5,  .5,   1,   1,   1],
			'C' => [1 =>  1,   1,  .5,  .5,  .5,  .5,  .5],
			'D' => [1 =>  1,   1,  .5,  .5,   1,   1,   1],
			'E' => [1 =>  1,   1,  .5,   1,   1,   1,   1],
		];

		return new AmountMetric('riskDeviation', new Amount($matrix[$column][$row]));
	}

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	public function calcBodyFatWeight(): AmountWithUnitMetric
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		$gender = $this->getGender();
		if (!$gender) {
			throw new MissingGenderException;
		}

		$result = new Weight(
			new Amount(
				$weight->getInUnit('kg')->getAmount()->getValue() * $gender->calcBodyFatPercentage($this)->getResult()->getValue()
			),
			'kg',
		);

		return new AmountWithUnitMetric('bodyFatWeight', $result);
	}

	public function calcActiveBodyMassPercentage(): AmountMetric
	{
		return new AmountMetric('activeBodyMassPercentage', new Percentage(1 - $this->calcBodyFatPercentage($this)->getResult()->getValue()));
	}

	public function calcActiveBodyMassWeight(): AmountWithUnitMetric
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		$result = new Weight(
			new Amount(
				$weight->getInUnit('kg')->getAmount()->getValue() * $this->calcActiveBodyMassPercentage()->getResult()->getValue()
			),
			'kg',
		);

		return new AmountWithUnitMetric('activeBodyMassWeight', $result);
	}

	public function calcOptimalFatPercentage(): MetricCollection
	{
		if (!$this->getBirthday()) {
			throw new InvalidBirthdayException;
		}

		$gender = $this->getGender();
		$age = $this->getBirthday()->getAge();

		if ($gender instanceof Genders\Male) {
			if ($age < 18) {
				$result = new Interval(new Percentage(0), new Percentage(0));
			} elseif ($age >= 18 && $age < 30) {
				$result = new Interval(new Percentage(.10), new Percentage(.15));
			} elseif ($age >= 30 && $age < 50) {
				$result = new Interval(new Percentage(.11), new Percentage(.17));
			} else {
				$result = new Interval(new Percentage(.12), new Percentage(.19));
			}
		} elseif ($gender instanceof Genders\Female) {
			if ($age < 18) {
				$result = new Interval(new Percentage(0), new Percentage(0));
			} elseif ($age >= 18 && $age < 30) {
				$result = new Interval(new Percentage(.14), new Percentage(.21));
			} elseif ($age >= 30 && $age < 50) {
				$result = new Interval(new Percentage(.15), new Percentage(.23));
			} else {
				$result = new Interval(new Percentage(.16), new Percentage(.25));
			}
		}

		return new MetricCollection([
			new AmountMetric('optimalFatPercentageMin', $result->getMin()),
			new AmountMetric('optimalFatPercentageMax', $result->getMax()),
		]);
	}

	public function calcOptimalFatWeight(): MetricCollection
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		$optimalFatPercentage = $this->calcOptimalFatPercentage();
		if (!$optimalFatPercentage) {
			throw new UnableToCalcOptimalFatPercentageException;
		}

		return new MetricCollection([
			new AmountWithUnitMetric(
				'optimalFatWeightMin',
				new Weight(
					new Amount(
						$weight->getInUnit('kg')->getAmount()->getValue() * $this->calcOptimalFatPercentage()->filterByName('optimalFatPercentageMin')[0]->getResult()->getValue()
					),
					'kg',
				)
			),
			new AmountWithUnitMetric(
				'optimalFatWeightMax',
				new Weight(
					new Amount(
						$weight->getInUnit('kg')->getAmount()->getValue() * $this->calcOptimalFatPercentage()->filterByName('optimalFatPercentageMax')[0]->getResult()->getValue()
					),
					'kg',
				)
			),
		]);
	}

	public function getOptimalWeight(): Interval
	{
		$activeBodyMassWeightValue = $this->calcActiveBodyMassWeight()->getResult()->getInUnit('kg')->getAmount()->getValue();
		$optimalFatWeight = $this->calcOptimalFatWeight();
		$optimalFatWeightMinValue = $optimalFatWeight->filterByName('optimalFatWeightMin')[0]->getResult()->getInUnit('kg')->getAmount()->getValue();
		$optimalFatWeightMaxValue = $optimalFatWeight->filterByName('optimalFatWeightMax')[0]->getResult()->getInUnit('kg')->getAmount()->getValue();

		return new Interval(
			new Weight(
				new Amount($activeBodyMassWeightValue + $optimalFatWeightMinValue),
				'kg',
			),
			new Weight(
				new Amount($activeBodyMassWeightValue + $optimalFatWeightMaxValue),
				'kg',
			),
		);
	}

	public function calcEssentialFatPercentage(): AmountMetric
	{
		$gender = $this->getGender();
		if (!$gender) {
			throw new MissingGenderException;
		}

		return $this->getGender()->calcEssentialFatPercentage();
	}

	public function calcEssentialFatWeight(): AmountWithUnitMetric
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		$essentialFatPercentage = $this->calcEssentialFatPercentage();
		if (!$essentialFatPercentage) {
			throw new UnableToCalcEssentialFatPercentageException;
		}

		return new AmountWithUnitMetric(
			'essentialFatWeight',
			new Weight(
				new Amount(
					$weight->getInUnit('kg')->getAmount()->getValue() * $essentialFatPercentage->getResult()->getValue()
				),
				'kg',
			)
		);
	}

	public function calcFatWithinOptimalPercentage(): MetricCollection
	{
		$optimalFatWeight = $this->calcOptimalFatWeight();
		$bodyFatWeight = $this->calcBodyFatWeight();

		$min = $optimalFatWeight->filterByName('optimalFatWeightMin')[0]->getResult()->getInUnit('kg')->getAmount()->getValue() / $bodyFatWeight->getResult()->getInUnit('kg')->getAmount()->getValue();
		$max = $optimalFatWeight->filterByName('optimalFatWeightMax')[0]->getResult()->getInUnit('kg')->getAmount()->getValue() / $bodyFatWeight->getResult()->getInUnit('kg')->getAmount()->getValue();

		return new MetricCollection([
			new AmountMetric('fatWithinOptimalPercentageMin', new Percentage($min <= 1 ? $min : 1)),
			new AmountMetric('fatWithinOptimalPercentageMax', new Percentage($max <= 1 ? $max : 1)),
		]);
	}

	public function calcFatWithinOptimalWeight(): MetricCollection
	{
		$bodyFatWeight = $this->calcBodyFatWeight();
		$optimalFatWeight = $this->calcOptimalFatWeight();

		$min = $bodyFatWeight->getResult()->getInUnit('kg')->getAmount()->getValue() - $optimalFatWeight->filterByName('optimalFatWeightMin')[0]->getResult()->getInUnit('kg')->getAmount()->getValue();
		$max = $bodyFatWeight->getResult()->getInUnit('kg')->getAmount()->getValue() - $optimalFatWeight->filterByName('optimalFatWeightMax')[0]->getResult()->getInUnit('kg')->getAmount()->getValue();

		return new MetricCollection([
			new AmountWithUnitMetric('fatWithinOptimalWeightMin', new Weight(
				new Amount(
					$bodyFatWeight->getResult()->getInUnit('kg')->getAmount()->getValue() - ($min >= 0 ? $min : 0)
				),
				'kg',
			)),
			new AmountWithUnitMetric('fatWithinOptimalWeightMax', new Weight(
				new Amount(
					$bodyFatWeight->getResult()->getInUnit('kg')->getAmount()->getValue() - ($max >= 0 ? $max : 0)
				),
				'kg',
			)),
		]);
	}

	public function calcFatOverOptimalWeight(): MetricCollection
	{
		$bodyFatWeight = $this->calcBodyFatWeight();
		$optimalFatWeight = $this->calcOptimalFatWeight();

		$min = $bodyFatWeight->getResult()->getInUnit('kg')->getAmount()->getValue() - $optimalFatWeight->filterByName('optimalFatWeightMin')[0]->getResult()->getInUnit('kg')->getAmount()->getValue();
		$max = $bodyFatWeight->getResult()->getInUnit('kg')->getAmount()->getValue() - $optimalFatWeight->filterByName('optimalFatWeightMax')[0]->getResult()->getInUnit('kg')->getAmount()->getValue();

		return new MetricCollection([
			new AmountWithUnitMetric('fatOverOptimalWeightMin', new Weight(
				new Amount(
					$min >= 0 ? $min : 0
				),
				'kg',
			)),
			new AmountWithUnitMetric('fatOverOptimalWeightMax', new Weight(
				new Amount(
					$max >= 0 ? $max : 0
				),
				'kg',
			)),
		]);
	}

	public function calcFatOverOptimalPercentage(): MetricCollection
	{
		$fatOverOptimalWeight = $this->calcFatOverOptimalWeight();
		$bodyFatWeight = $this->calcBodyFatWeight();

		$min = $fatOverOptimalWeight->filterByName('fatOverOptimalWeightMin')[0]->getResult()->getInUnit('kg')->getAmount()->getValue() / $bodyFatWeight->getResult()->getInUnit('kg')->getAmount()->getValue();
		$max = $fatOverOptimalWeight->filterByName('fatOverOptimalWeightMax')[0]->getResult()->getInUnit('kg')->getAmount()->getValue() / $bodyFatWeight->getResult()->getInUnit('kg')->getAmount()->getValue();

		return new MetricCollection([
			new AmountMetric('fatOverOptimalPercentageMin', new Percentage($min)),
			new AmountMetric('fatOverOptimalPercentageMax', new Percentage($max)),
		]);
	}

	public function calcBodyFatDeviation(): AmountMetric
	{
		$gender = $this->getGender();
		$bodyMassIndex = $this->calcBodyMassIndex();
		$bodyMassIndexDeviation = $this->calcBodyMassIndexDeviation();
		$isOverweight = $this->getIsOverweight();

		if ($gender instanceof Genders\Male && $bodyMassIndex->getResult()->getValue() >= .95 && !$isOverweight) {
			return new AmountMetric('bodyFatDeviation', new Amount(0));
		}

		return $bodyMassIndexDeviation;
	}

	/*****************************************************************************
	 * Beztuková tělesná hmotnost - FFM.
	 */
	public function calcFatFreeMass(): AmountWithUnitMetric
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		$weightValue = $weight->getInUnit('kg')->getAmount()->getValue();
		$bodyFatPercentageValue = $this->calcBodyFatPercentage()->getResult()->getValue();

		$result = new Weight(
			new Amount($weightValue - ($bodyFatPercentageValue * $weightValue)),
			'kg',
		);

		$formula = 'weight[' . $weightValue . '] - (bodyFatPercentage[' . $bodyFatPercentageValue . '] * weight[' . $weightValue . ']) = ' . $result->getAmount()->getValue();

		return new AmountWithUnitMetric('fatFreeMass', $result, $formula);
	}

	/*****************************************************************************
	 * Bazální metabolismus - BMR.
	 */
	public function calcBasalMetabolicRate(): AmountWithUnitMetric
	{
		if (!$this->getGender()) {
			throw new MissingGenderException;
		}

		return $this->getGender()->calcBasalMetabolicRate($this);
	}

	/*****************************************************************************
	 * Total Energy Expenditure - Termický efekt pohybu - TEE.
	 */
	public function calcTotalEnergyExpenditure(): AmountWithUnitMetric
	{
		$basalMetabolicRate = $this->calcBasalMetabolicRate()->getResult()->getAmount()->getValue();
		$physicalActivityLevel = $this->calcPhysicalActivityLevel()->getResult()->getValue();

		$result = new Energy(
			new Amount($basalMetabolicRate * $physicalActivityLevel),
			'kCal',
		);

		$formula = 'basalMetabolicRate[' . $basalMetabolicRate . '] * physicalActivityLevel[' . $physicalActivityLevel . '] = ' . $result->getAmount()->getValue();

		return new AmountWithUnitMetric('totalEnergyExpenditure', $result, $formula);
	}

	/*****************************************************************************
	 * Total Daily Energy Expenditure - Celkový doporučený denní příjem - TDEE.
	 */
	public function calcTotalDailyEnergyExpenditure(): AmountWithUnitMetric
	{
		if (!$this->getGoal()->getVector()) {
			throw new MissingGoalVectorException;
		}

		$totalEnergyExpenditureValue = $this->calcTotalEnergyExpenditure()->getResult()->getAmount()->getValue();
		$tdeeQuotientValue = $this->getGoal()->getVector()->calcTdeeQuotient($this)->getResult()->getValue();

		$result = new Energy(
			new Amount($totalEnergyExpenditureValue * $tdeeQuotientValue),
			'kCal',
		);

		$formula = 'totalEnergyExpenditure[' . $totalEnergyExpenditureValue . '] * weightGoalQuotient[' . $tdeeQuotientValue . '] = ' . $result->getAmount()->getValue();

		return new AmountWithUnitMetric('totalDailyEnergyExpenditure', $result, $formula);
	}

	/*****************************************************************************
	 * Reference Daily Intake - Doporučený denní příjem - DDP.
	 */
	public function calcReferenceDailyIntake(): AmountWithUnitMetric
	{
		$exceptionCollection = new FattyExceptionCollection;

		try {
			$totalDailyEnergyExpenditure = $this->calcTotalDailyEnergyExpenditure();
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$gender = $this->getGender();
			if (!$gender) {
				throw new MissingGenderException;
			}

			$referenceDailyIntakeBonus = $gender->calcReferenceDailyIntakeBonus();
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		if ($this->getDiet() instanceof Ned) {
			$result = new Energy(
				new Amount(
					(float)Ned::ENERGY_DEFAULT
				),
				'kCal',
			);

			$formula = $result->getAmount()->getValue();
		} else {
			$totalDailyEnergyExpenditureValue = $totalDailyEnergyExpenditure->getResult()->getAmount()->getValue();
			$referenceDailyIntakeBonusValue = $referenceDailyIntakeBonus->getAmount()->getValue();

			$result = new Energy(
				new Amount($totalDailyEnergyExpenditureValue + $referenceDailyIntakeBonusValue),
				'kCal',
			);

			$formula = 'totalDailyEnergyExpenditure[' . $totalDailyEnergyExpenditureValue . '] + referenceDailyIntakeBonus[' . $referenceDailyIntakeBonusValue . '] = ' . $result->getAmount()->getValue();
		}

		return new AmountWithUnitMetric('referenceDailyIntake', $result, $formula);
	}

	/*****************************************************************************
	 * Body type - typ postavy.
	 */
	public function calcBodyType(Calculator $calculator): StringMetric
	{
		$gender = $this->getGender();
		if (!$gender) {
			throw new MissingGenderException;
		}

		return $gender->calcBodyType($this);
	}

	/*****************************************************************************
	 * Živiny.
	 */
	public function calcGoalNutrients(): MetricCollection
	{
		$nutrients = new Nutrients;

		/***************************************************************************
		 * Proteins.
		 */
		// 1
		if ($this->getSportDurations()->getTotalDuration() > 60 || $this->calcPhysicalActivityLevel()->getResult()->getValue() >= 1.9) {
			// 13
			if ($this->getGender() instanceof Genders\Male) {
				// 14
				if ($this->calcFatOverOptimalWeight()->filterByName('fatOverOptimalWeightMax')[0]->getResult()->getInUnit('kg')->getAmount()) {
					$optimalWeight = $this->getOptimalWeight()->getMax();

				// 15
				} else {
					$optimalWeight = $this->getWeight();
				}

				$matrix = [
					'fit'   => [1.5, 2.2, 1.8],
					'unfit' => [1.5, 2,   1.7],
				];
				$matrixSet = ($this->calcBodyFatPercentage()->getResult()->getValue() > .19 || $this->calcBodyMassIndex()->getResult()->getValue() > 25) ? 'unfit' : 'fit';

				$optimalNutrients = [];
				foreach ($this->getSportDurations()->getMaxDurations() as $sportDuration) {
					if ($sportDuration instanceof LowFrequency) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][0];
					} elseif ($sportDuration instanceof Anaerobic) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][1];
					} elseif ($sportDuration instanceof Aerobic) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][2];
					}
				}

				if ($this->calcPhysicalActivityLevel()->getResult()->getValue() >= 1.9) {
					$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][1];
				}

				$nutrients->setProteins(new Nutrients\Proteins(new Amount(max($optimalNutrients)), 'g'));

			// 12
			} elseif ($this->getGender() instanceof Genders\Female) {
				// 20
				if ($this->getGender()->isPregnant()) {
					// @TODO

				// 16
				} else {
					// 17
					if ($this->calcFatOverOptimalWeight()->filterByName('fatOverOptimalWeightMax')[0]->getResult()->getInUnit('kg')->getAmount()) {
						$optimalWeight = $this->getOptimalWeight()->getMax();

					// 18
					} else {
						$optimalWeight = $this->getWeight();
					}

					$matrix = [
						'fit'   => [1.4, 1.8, 1.6],
						'unfit' => [1.5, 1.8, 1.8],
					];
					$matrixSet = ($this->calcBodyFatPercentage()->getResult()->getValue() > .25 || $this->calcBodyMassIndex()->getResult()->getValue() > 25) ? 'unfit' : 'fit';

					$optimalNutrients = [];
					foreach ($this->getSportDurations()->getMaxDurations() as $sportDuration) {
						if ($sportDuration instanceof LowFrequency) {
							$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][0];
						} elseif ($sportDuration instanceof Anaerobic) {
							$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][1];
						} elseif ($sportDuration instanceof Aerobic) {
							$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][2];
						}
					}

					if ($this->calcPhysicalActivityLevel()->getResult()->getValue() >= 1.9) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][1];
					}

					$nutrients->setProteins(new Nutrients\Proteins(new Amount(max($optimalNutrients)), 'g'));

					// 19
					if ($this->getGender()->isPregnant() || $this->getGender()->isBreastfeeding()) {
						$nutrients->setProteins(new Nutrients\Proteins($nutrients->getProteins()->getInUnit('g')->getAmount() + 20, 'g'));
					}
				}
			}

		// 2
		} else {
			// 3
			if ($this->getGender() instanceof Genders\Female && ($this->getGender()->isPregnant() || $this->getGender()->isBreastfeeding())) {
				// 11
				$nutrients->setProteins(new Nutrients\Proteins(min(($this->getWeight()->getInUnit('kg')->getAmount()->getValue() * 1.4) + 20, 90), 'g'));

			// 4
			} else {
				// 5
				if ($this->getGender() instanceof Genders\Male) {
					// 7
					if ($this->calcFatOverOptimalWeight()->filterByName('fatOverOptimalWeightMax')[0]->getResult()->getInUnit('kg')->getAmount()) {
						$nutrients->setProteins(new Nutrients\Proteins(new Amount($this->getOptimalWeight()->getMax()->getInUnit('kg')->getAmount()->getValue() * 1.5), 'g'));

					// 8
					} else {
						$nutrients->setProteins(new Nutrients\Proteins(new Amount($this->getWeight()->getInUnit('kg')->getAmount()->getValue() * 1.5), 'g'));
					}
				// 6
				} elseif ($this->getGender() instanceof Genders\Female) {
					// 9
					if ($this->calcFatOverOptimalWeight()->filterByName('fatOverOptimalWeightMax')[0]->getResult()->getInUnit('kg')->getAmount()) {
						$nutrients->setProteins(new Nutrients\Proteins(new Amount($this->getOptimalWeight()->getMax()->getInUnit('kg')->getAmount()->getValue() * 1.4), 'g'));

					// 10
					} else {
						$nutrients->setProteins(new Nutrients\Proteins(new Amount($this->getWeight()->getInUnit('kg')->getAmount()->getValue() * 1.4), 'g'));
					}
				}
			}
		}

		/***************************************************************************
		 * Carbs and fats.
		 */
		$goalTdee = $this->getGoal()->calcGoalTotalDailyEnergyExpenditure($this);
		$diet = $this->getDiet();
		$dietApproach = $this->getDiet()->getApproach();
		if (!$dietApproach) {
			throw new MissingDietApproachException;
		}

		// 1
		if ($dietApproach instanceof Standard) {
			// 4
			if ($this->getSportDurations()->getAnaerobic() instanceof SportDuration && $this->getSportDurations()->getAnaerobic()->getAmount()->getValue() >= 100) {
				$nutrients->setCarbs(
					Carbs::createFromEnergy(
						new Energy(
							new Amount($goalTdee->getResult()->getInUnit('kJ')->getAmount()->getValue() * .58),
							'kJ',
						),
					),
				);
				$nutrients->setFats(
					Fats::createFromEnergy(
						new Energy(
							new Amount(
								$goalTdee->getResult()->getInBaseUnit()->getAmount()->getValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue(),
							),
							Energy::getBaseUnit(),
						),
					),
				);
			// 5
			} elseif ($this->getGender() instanceof Genders\Female && ($this->getGender()->isPregnant() || $this->getGender()->isBreastfeeding())) {
				$nutrients->setFats(
					Fats::createFromEnergy(
						new Energy(
							new Amount(
								$goalTdee->getResult()->getInBaseUnit()->getAmount()->getValue() * .35
							),
							Energy::getBaseUnit(),
						),
					),
				);
				$nutrients->setCarbs(
					Carbs::createFromEnergy(
						new Energy(
							new Amount(
								$goalTdee->getResult()->getInBaseUnit()->getAmount()->getValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
							),
							Energy::getBaseUnit(),
						),
					),
				);
			} else {
				$nutrients->setCarbs(
					Carbs::createFromEnergy(
						new Energy(
							new Amount(
								$goalTdee->getResult()->getInBaseUnit()->getAmount()->getValue() * .55
							),
							Energy::getBaseUnit(),
						),
					),
				);
				$nutrients->setFats(
					Fats::createFromEnergy(
						new Energy(
							new Amount(
								$goalTdee->getResult()->getInBaseUnit()->getAmount()->getValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
							),
							Energy::getBaseUnit(),
						),
					),
				);
			}

		// Mediterranean diet.
		} elseif ($dietApproach instanceof Standard) {
			$nutrients->setFats(
				Fats::createFromEnergy(
					new Energy(
						new Amount(
							$goalTdee->getResult()->getInBaseUnit()->getAmount()->getValue() * .4
						),
						Energy::getBaseUnit(),
					),
				),
			);
			$nutrients->setCarbs(
				Carbs::createFromEnergy(
					new Energy(
						new Amount(
							$goalTdee->getResult()->getInBaseUnit()->getAmount()->getValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
						),
						Energy::getBaseUnit(),
					),
				),
			);

		// 2
		} elseif ($dietApproach instanceof LowCarb) {
			// 7
			if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
				$dietCarbs = $diet->getCarbs();
				$nutrients->setCarbs(
					new Carbs(
						$dietCarbs->getAmount(),
						$dietCarbs->getUnit(),
					),
				);
				$nutrients->setFats(
					Fats::createFromEnergy(
						new Energy(
							new Amount(
								$goalTdee->getResult()->getInBaseUnit()->getAmount()->getValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
							),
							Energy::getBaseUnit(),
						),
					),
				);
				// @TODO - message
			// 8
			} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
				$dietCarbs = $diet->getCarbs();
				$nutrients->setCarbs(new Carbs(
					$dietCarbs->getAmount(),
					$dietCarbs->getUnit(),
				));
				$nutrients->setFats(
					Fats::createFromEnergy(
						new Energy(
							new Amount(
								$goalTdee->getResult()->getInBaseUnit()->getAmount()->getValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
							),
							Energy::getBaseUnit(),
						),
					),
				);
				// @TODO - message
			// 9
			} else {
				$dietCarbs = $diet->getCarbs();
				$nutrients->setCarbs(
					new Carbs(
						$dietCarbs->getAmount(),
						$dietCarbs->getUnit(),
					),
				);
				$nutrients->setFats(
					Fats::createFromEnergy(
						new Energy(
							new Amount(
								$goalTdee->getResult()->getInBaseUnit()->getAmount()->getValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
							),
							Energy::getBaseUnit(),
						),
					),
				);
			}

		// 3
		} elseif ($dietApproach instanceof Keto) {
			// 7
			if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
				// @TODO - message
			// 8
			} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
				// @TODO - message
			// 9
			} else {
				$dietCarbs = $diet->getCarbs();
				$nutrients->setCarbs(
					new Carbs(
						$dietCarbs->getAmount(),
						$dietCarbs->getUnit(),
					),
				);
				$nutrients->setFats(
					Fats::createFromEnergy(
						new Energy(
							new Amount(
								$goalTdee->getResult()->getInBaseUnit()->getAmount()->getValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
							),
							Energy::getBaseUnit(),
						),
					),
				);
			}
		// NED diet.
		} elseif ($dietApproach instanceof Ned) {
			$nutrients->setCarbs((new Ned)->getCarbsDefault());
			$nutrients->setFats((new Ned)->getFatsDefault());
			$nutrients->setProteins((new Ned)->getProteinsDefault());
		}

		return new MetricCollection([
			new AmountWithUnitMetric('goalNutrientsCarbs', $nutrients->getCarbs()),
			new AmountWithUnitMetric('goalNutrientsFats', $nutrients->getFats()),
			new AmountWithUnitMetric('goalNutrientsProteins', $nutrients->getProteins()),
		]);
	}

	/*****************************************************************************
	 * Messages.
	 */
	// public function getBodyFatMessages()
	// {
	// 	$messages = [];

	// 	// High sport physical activity level (>= 2).
	// 	if ($this->calcPhysicalActivityLevel()->getValue() >= 2 && ($this->getSportDurations()->getAerobic()->getAmount() || $this->getSportDurations()->getAnaerobic()->getAmount())) {
	// 		$messages[] = [
	// 			'message' => \Katu\Config::get('caloricCalculator', 'messages', 'highSportPhysicalActivityLevel'),
	// 			'fields' => ['sportDurations[aerobic]', 'sportDurations[anaerobic]'],
	// 		];
	// 	}

	// 	return $messages;
	// }

	// public function getBodyMassIndexMessages()
	// {
	// 	$messages = [];

	// 	$gender = $this->getGender();
	// 	$bodyMassIndexAmount = $this->calcBodyMassIndex()->getValue();
	// 	$bodyFatPercentageAmount = $this->calcBodyFatPercentage()->getAmount();

	// 	$bodyMassIndexAmount = 28;
	// 	$gender = new Genders\Male;
	// 	$bodyFatPercentageAmount = .18;

	// 	if ($bodyMassIndexAmount <= 25) {
	// 		if ($gender instanceof Genders\Male) {
	// 			if ($bodyFatPercentageAmount < .19) {
	// 				if ($bodyMassIndexAmount <= 18.5) {
	// 					if ($bodyMassIndexAmount < 17) {
	// 						$messages[]['message'] = "Těžká podvýživa, poruchy příjmu potravy!";
	// 					} else {
	// 						$messages[]['message'] = "Pozor, BMI není v normě, podváha!";
	// 					}

	// 					if ($bodyFatPercentageAmount <= .05) {
	// 						$messages[]['message'] = "Pozor, množství esenciálního tuku u mužů je 3-5 %. Jsi na hraně!";
	// 					}
	// 				} else {
	// 					$messages[]['message'] = "Super, BMI i podíl tělesného tuku je jak má být.";
	// 				}
	// 			} else {
	// 				$messages[]['message'] = "BMI je v pořádku, ale máte více tělesného tuku, než by mělo být.";
	// 			}
	// 		} elseif ($gender instanceof Genders\Female) {
	// 			if ($bodyFatPercentageAmount < .25) {
	// 				if ($bodyMassIndexAmount <= 18.5) {
	// 					if ($bodyMassIndexAmount < 17) {
	// 						$messages[]['message'] = "Těžká podvýživa, poruchy příjmu potravy!";
	// 					} else {
	// 						$messages[]['message'] = "Pozor, BMI není v normě, podváha!";
	// 					}

	// 					if ($bodyFatPercentageAmount <= .13) {
	// 						$messages[]['message'] = "Pozor, množství esenciálního tuku u žen je 11-13 %. Jsi na hraně!";
	// 					}
	// 				} else {
	// 					$messages[]['message'] = "Super, BMI i podíl tělesného tuku je jak má být.";
	// 				}
	// 			} else {
	// 				$messages[]['message'] = "BMI je v pořádku, ale máte více tělesného tuku, než by mělo být.";
	// 			}
	// 		}
	// 	} else {
	// 		if ($gender instanceof Genders\Male) {
	// 			if ($bodyFatPercentageAmount < .19) {
	// 				$messages[]['message'] = "BMI sice v normě není, ale vše v pořádku, ty asi hodně cvičíš, takže na to nekoukej.";
	// 			} else {
	// 				if ($bodyMassIndexAmount < 25) {
	// 					$messages[]['message'] = "BMI je v pořádku, ale máte více tělesného tuku, než by mělo být.";
	// 				} elseif ($bodyMassIndexAmount < 30) {
	// 					$messages[]['message'] = "Pozor, máš nadváhu.";
	// 				} elseif ($bodyMassIndexAmount < 35) {
	// 					$messages[]['message'] = "Obezita 1. stupně, pozor, hrozí riziko vzniku chorob.";
	// 				} elseif ($bodyMassIndexAmount < 40) {
	// 					$messages[]['message'] = "Obezita 2. stupně, vysoké riziko vzniku chorob.";
	// 				} else {
	// 					$messages[]['message'] = "Obezita 3. stupně, morbidní obezita.";
	// 				}
	// 			}
	// 		} elseif ($gender instanceof Genders\Female) {
	// 			if ($bodyFatPercentageAmount < .25) {
	// 				$messages[]['message'] = "BMI sice v normě není, ale vše v pořádku, ty asi hodně cvičíš, takže na to nekoukej.";
	// 			} else {
	// 				if ($bodyMassIndexAmount < 25) {
	// 					$messages[]['message'] = "BMI je v pořádku, ale máte více tělesného tuku, než by mělo být.";
	// 				} elseif ($bodyMassIndexAmount < 30) {
	// 					$messages[]['message'] = "Pozor, máš nadváhu.";
	// 				} elseif ($bodyMassIndexAmount < 35) {
	// 					$messages[]['message'] = "Obezita 1. stupně, pozor, hrozí riziko vzniku chorob.";
	// 				} elseif ($bodyMassIndexAmount < 40) {
	// 					$messages[]['message'] = "Obezita 2. stupně, vysoké riziko vzniku chorob.";
	// 				} else {
	// 					$messages[]['message'] = "Obezita 3. stupně, morbidní obezita.";
	// 				}
	// 			}
	// 		}
	// 	}

	// 	return $messages;
	// }

	// public function getGoalMessages()
	// {
	// 	$exceptionCollection = new FattyExceptionCollection;

	// 	$messages = [];

	// 	// Is pregnant.
	// 	if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
	// 		$messages[] = [
	// 			'message' => \Katu\Config::get('caloricCalculator', 'messages', 'isPregnant'),
	// 			'fields' => ['pregnancy[isPregnant]'],
	// 		];
	// 	}

	// 	// Is breastfeeding.
	// 	if ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
	// 		$messages[] = [
	// 			'message' => \Katu\Config::get('caloricCalculator', 'messages', 'isBreastfeeding'),
	// 			'fields' => ['pregnancy[isBreastfeeding]'],
	// 		];
	// 	}

	// 	// Is loosing weight.
	// 	if ($this->getGoal()->getVector() instanceof Vectors\Loose) {
	// 		// Is loosing weight while pregnant.
	// 		if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
	// 			$messages[] = [
	// 				'message' => \Katu\Config::get('caloricCalculator', 'messages', 'isPregnantAndLoosingWeight'),
	// 				'fields' => ['pregnancy[isPregnant]', 'goalTrend', 'goalWeight'],
	// 			];

	// 		// Is loosing weight while breastfeeding.
	// 		} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
	// 			$messages[] = [
	// 				'message' => \Katu\Config::get('caloricCalculator', 'messages', 'isBreastfeedingAndLoosingWeight'),
	// 				'fields' => ['pregnancy[isBreastfeeding]', 'goalTrend', 'goalWeight'],
	// 			];
	// 		} else {
	// 			if (!($this->getWeight() instanceof Weight)) {
	// 				$ec->add(
	// 					(new FattyException("Missing weight."))
	// 						->setAbbr('missingWeight')
	// 				);
	// 			}

	// 			if (!($this->getGoal()->getWeight() instanceof Weight)) {
	// 				$ec->add(
	// 					(new FattyException("Missing weight target."))
	// 						->setAbbr('missingGoalWeight')
	// 				);
	// 			}

	// 			// Is loosing realistic?
	// 			if (!$ec->has()) {
	// 				// Unrealistic loosing.
	// 				if ($this->getGoal()->getDifference($this) > 0) {
	// 					$realisticGoalWeight = $this->getGoal()->getFinal($this);

	// 					$messages[] = [
	// 						'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'loosingWeightUnrealistic'), [
	// 							'%realisticGoalWeight%' => $realisticGoalWeight,
	// 						]),
	// 						'fields' => ['goalWeight'],
	// 					];

	// 				// Realistic loosing.
	// 				} else {
	// 					$weightChange = new Weight($this->getWeight()->getInUnit('kg')->getAmount() - $this->getGoal()->getWeight()->getInUnit('kg')->getAmount());

	// 					$messages[] = [
	// 						'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'loosingWeightRealistic'), [
	// 							'%weightChange%' => $weightChange,
	// 						]),
	// 						'fields' => ['goalWeight'],
	// 					];

	// 					$slowLooseTdee = (new Vectors\SlowLoose)->calcTotalDailyEnergyExpenditure($this);
	// 					$looseTdee = (new Vectors\Loose)->calcTotalDailyEnergyExpenditure($this);

	// 					$messages[] = [
	// 						'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'loosingWeightTdeeRecommendations'), [
	// 							'%slowLooseTdee%' => $slowLooseTdee,
	// 							'%looseTdee%' => $looseTdee,
	// 						]),
	// 						'fields' => ['goalWeight'],
	// 					];
	// 				}
	// 			}
	// 		}
	// 	} elseif ($this->getGoal()->getTrend() instanceof Vectors\Gain) {
	// 		if (!($this->getWeight() instanceof Weight)) {
	// 			$ec->add(
	// 				(new FattyException("Missing weight."))
	// 					->setAbbr('missingWeight')
	// 			);
	// 		}

	// 		if (!($this->getGoal()->getWeight() instanceof Weight)) {
	// 			$ec->add(
	// 				(new FattyException("Missing weight target."))
	// 					->setAbbr('missingGoalWeight')
	// 			);
	// 		}

	// 		// Is gaining realistic?
	// 		if (!$ec->has()) {
	// 			// Unrealistic gaining.
	// 			if ($this->getGoal()->getDifference($this) > 0) {
	// 				$realisticGoalWeight = $this->getGoal()->getFinal($this);

	// 				$messages[] = [
	// 					'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'gainingWeightUnrealistic'), [
	// 						'%realisticGoalWeight%' => $realisticGoalWeight,
	// 					]),
	// 					'fields' => ['goalWeight'],
	// 				];

	// 			// Realistic gaining.
	// 			} else {
	// 				$weightChange = new Weight($this->getGoal()->getWeight()->getInUnit('kg')->getAmount() - $this->getWeight()->getInUnit('kg')->getAmount());

	// 				$messages[] = [
	// 					'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'gainingWeightRealistic'), [
	// 						'%weightChange%' => $weightChange,
	// 					]),
	// 					'fields' => ['goalWeight'],
	// 				];

	// 				$slowGainTdee = (new Vectors\SlowGain)->calcTotalDailyEnergyExpenditure($this);
	// 				$gainTdee = (new Vectors\Gain)->calcTotalDailyEnergyExpenditure($this);

	// 				$messages[] = [
	// 					'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'gainingWeightTdeeRecommendations'), [
	// 						'%slowGainTdee%' => $slowGainTdee,
	// 						'%gainTdee%' => $gainTdee,
	// 					]),
	// 					'fields' => ['goalWeight'],
	// 				];
	// 			}

	// 			$messages[] = [
	// 				'message' => \Katu\Config::get('caloricCalculator', 'messages', 'gainingRecommendations'),
	// 				'fields' => ['goalWeight'],
	// 			];
	// 		}
	// 	}

	// 	if ($ec->has()) {
	// 		throw $ec;
	// 	}

	// 	return $messages;
	// }

	// public function getGoalNutrientMessages()
	// {
	// 	$messages = [];

	// 	if ($this->getDiet() instanceof Approaches\LowCarb) {
	// 		// 7
	// 		if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
	// 			$messages[] = [
	// 				'message' => \Katu\Config::get('caloricCalculator', 'messages', 'lowCarbButPregnant'),
	// 				'fields' => ['diet'],
	// 			];

	// 		// 8
	// 		} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
	// 			$messages[] = [
	// 				'message' => \Katu\Config::get('caloricCalculator', 'messages', 'lowCarbButBreastfeeding'),
	// 				'fields' => ['diet'],
	// 			];
	// 		}

	// 	// 3
	// 	} elseif ($this->getDiet() instanceof Approaches\Keto) {
	// 		// 7
	// 		if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
	// 			$messages[] = [
	// 				'message' => \Katu\Config::get('caloricCalculator', 'messages', 'ketoButPregnant'),
	// 				'fields' => ['diet'],
	// 			];

	// 		// 8
	// 		} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
	// 			$messages[] = [
	// 				'message' => \Katu\Config::get('caloricCalculator', 'messages', 'ketoButBreastfeeding'),
	// 				'fields' => ['diet'],
	// 			];
	// 		}
	// 	}

	// 	return $messages;
	// }

	// public function getMessages()
	// {
	// 	$exceptionCollection = new FattyExceptionCollection;

	// 	$messages = [];

	// 	try {
	// 		$messages = array_merge($messages, $this->getBodyFatMessages());
	// 	} catch (FattyException $e) {
	// 		$exceptionCollection->add($e);
	// 	}

	// 	try {
	// 		$messages = array_merge($messages, $this->getBodyMassIndexMessages());
	// 	} catch (FattyException $e) {
	// 		$exceptionCollection->add($e);
	// 	}

	// 	try {
	// 		$messages = array_merge($messages, $this->getGoalMessages());
	// 	} catch (FattyException $e) {
	// 		$exceptionCollection->add($e);
	// 	}

	// 	try {
	// 		$messages = array_merge($messages, $this->getGoalNutrientMessages());
	// 	} catch (FattyException $e) {
	// 		$exceptionCollection->add($e);
	// 	}

	// 	if ($ec->has()) {
	// 		throw $ec;
	// 	}

	// 	return $messages;
	// }

	public function getResponse(): array
	{
		$exceptionCollection = new FattyExceptionCollection;

		$res = [];

		/**************************************************************************
		 * Input.
		 */
		$res['input']['gender'] = $this->getGender() ? $this->getGender()->getCode() : null;
		$res['input']['birthday'] = $this->getBirthday() ? $this->getBirthday()->getDatetime()->format('Y-m-d') : null;
		$res['input']['weight'] = $this->getWeight() ? $this->getWeight()->getAmount()->getValue() : null;
		$res['input']['proportions_height'] = $this->getProportions()->getHeight() ? $this->getProportions()->getHeight()->getAmount()->getValue() : null;
		$res['input']['proportions_waist'] = $this->getProportions()->getWaist() ? $this->getProportions()->getWaist()->getAmount()->getValue() : null;
		$res['input']['proportions_hips'] = $this->getProportions()->getHips() ? $this->getProportions()->getHips()->getAmount()->getValue() : null;
		$res['input']['proportions_neck'] = $this->getProportions()->getNeck() ? $this->getProportions()->getNeck()->getAmount()->getValue() : null;
		$res['input']['bodyFatPercentage'] = $this->getBodyFatPercentage() ? $this->getBodyFatPercentage()->getValue() : null;
		$res['input']['activity'] = $this->getActivity() ? $this->getActivity()->getValue() : null;
		$res['input']['sportDurations_lowFrequency'] = $this->getSportDurations()->getLowFrequency() ? $this->getSportDurations()->getLowFrequency()->getAmount()->getValue() : null;
		$res['input']['sportDurations_aerobic'] = $this->getSportDurations()->getAerobic() ? $this->getSportDurations()->getAerobic()->getAmount()->getValue() : null;
		$res['input']['sportDurations_anaerobic'] = $this->getSportDurations()->getAnaerobic() ? $this->getSportDurations()->getAnaerobic()->getAmount()->getValue() : null;
		$res['input']['goal_vector'] = $this->getGoal()->getVector() ? $this->getGoal()->getVector()->getCode() : null;
		$res['input']['goal_weight'] = $this->getGoal()->getWeight() ? $this->getGoal()->getWeight()->getAmount()->getValue() : null;
		$res['input']['diet_approach'] = $this->getDiet()->getApproach() ? $this->getDiet()->getApproach()->getCode() : null;
		$res['input']['diet_carbs'] = $this->getDiet()->getCarbs() ? $this->getDiet()->getCarbs()->getAmount()->getValue() : null;

		// $res['input']['pregnancyIsPregnant'] =
		// 			$this->getGender() instanceof \App\Classes\Profile\Genders\Female
		// 	&& $this->getGender()->isPregnant()
		// 		? true : false;

		// $res['input']['pregnancyChildbirthDate'] =
		// 			$this->getGender() instanceof \App\Classes\Profile\Genders\Female
		// 	&& $this->getGender()->isPregnant()
		// 	&& $this->getGender()->getPregnancyChildbirthDate() instanceof \App\Classes\Profile\Birthday
		// 		? $this->getGender()->getPregnancyChildbirthDate()->getBirthday()->format('Y-m-d') : null;

		// $res['input']['breastfeedingIsBreastfeeding'] =
		// 			$this->getGender() instanceof \App\Classes\Profile\Genders\Female
		// 	&& $this->getGender()->isBreastfeeding()
		// 		? true : false;

		// $res['input']['breastfeeding']['childbirthDate'] =
		// 			$this->getGender() instanceof \App\Classes\Profile\Genders\Female
		// 	&& $this->getGender()->isBreastfeeding()
		// 	&& $this->getGender()->getBreastfeedingChildbirthDate() instanceof \App\Classes\Profile\Birthday
		// 		? $this->getGender()->getBreastfeedingChildbirthDate()->getBirthday()->format('Y-m-d') : null;

		// $res['input']['breastfeedingMode'] =
		// 			$this->getGender() instanceof \App\Classes\Profile\Genders\Female
		// 	&& $this->getGender()->isBreastfeeding()
		// 	&& $this->getGender()->getBreastfeedingMode() instanceof \App\Classes\Profile\BreastfeedingMode
		// 		? $this->getGender()->getBreastfeedingMode()->getCode() : null;

		/**************************************************************************
		 * Output.
		 */
		$metricCollection = new MetricCollection;

		try {
			$metricCollection->append($this->calcWeight());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->getProportions()->calcHeight());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->getProportions()->calcHeight());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcBodyMassIndex());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcBodyMassIndexDeviation());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcWaistHipRatio());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcWaistHipRatioDeviation());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcBodyFatPercentage());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcBodyFatWeight());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcActiveBodyMassPercentage());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->merge($this->calcOptimalFatPercentage());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->merge($this->calcOptimalFatWeight());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcEssentialFatPercentage());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcEssentialFatWeight());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->merge($this->calcFatWithinOptimalPercentage());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->merge($this->calcFatWithinOptimalWeight());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->merge($this->calcFatOverOptimalPercentage());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->merge($this->calcFatOverOptimalWeight());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcBodyFatDeviation());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcRiskDeviation());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcActiveBodyMassWeight());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcFatFreeMass());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcBasalMetabolicRate());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcPhysicalActivityLevel());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcTotalEnergyExpenditure());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcTotalDailyEnergyExpenditure());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->calcReferenceDailyIntake());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->append($this->getGoal()->calcGoalVector());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		// try {
		// 	$metricCollection->append($this->getGoal()->calcGoalWeight());
		// } catch (FattyException $e) {
		// 	$exceptionCollection->add($e);
		// }

		try {
			$metricCollection->append($this->getGoal()->calcGoalTotalDailyEnergyExpenditure($this));
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		// try {
		// 	$metric = $this->getDiet();
		// 	if ($metric) {
		// 		$res['output']['metrics']['dietApproach']['result'] = $metric->getArray();
		// 		$res['output']['metrics']['dietApproach']['string'] = (string)$metric;
		// 	}
		// } catch (FattyException $e) {
		// 	$ec->add($e);
		// }

		// try {
		// 	$diet = $calculator->getDiet();
		// 	if ($diet) {
		// 		$metric = $diet->getCarbs();
		// 		if ($metric) {
		// 			$res['output']['metrics']['dietCarbs']['result'] = $metric->getArray();
		// 			$res['output']['metrics']['dietCarbs']['string'] = (string)$metric . " denně";
		// 		}
		// 	}
		// } catch (FattyException $e) {
		// 	$ec->add($e);
		// }

		// try {
		// 	if ($metric) {
		// 		$res['output']['metrics']['dietDuration']['result'] = $calculator->getGoal()->getDuration()->getAmount() . " weeks";
		// 		$res['output']['metrics']['dietDuration']['string'] = $calculator->getGoal()->getDuration()->getAmount() . " týdnů";
		// 	}
		// } catch (FattyException $e) {
		// 	$ec->add($e);
		// }

		try {
			$metricCollection->append($this->calcBodyType($this));
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metricCollection->merge($this->calcGoalNutrients());
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		$res['output']['metrics'] = $metricCollection->getResponse();

		return $res;
	}
}
