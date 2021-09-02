<?php

namespace Fatty;

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
use Fatty\Nutrients\Carbs;
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

	public static function createFromParams(array $params) : Calculator
	{
		$object = new static;
		$object->setParams($params);

		return $object;
	}

	public function setParams(array $params) : Calculator
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
				$value = Weight::createFromString($params['weight']);
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
				$value = Length::createFromString($params['proportions_height']);
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
				$value = Length::createFromString($params['proportions_waist']);
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
				$value = Length::createFromString($params['proportions_hips']);
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
				$value = Length::createFromString($params['proportions_neck']);
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
				$value = LowFrequency::createFromString($params['sportDurations_lowFrequency']);
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
				$value = Aerobic::createFromString($params['sportDurations_aerobic']);
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
				$value = Anaerobic::createFromString($params['sportDurations_anaerobic']);
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
				$value = Weight::createFromString($params['goal_weight']);
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
				$value = Carbs::createFromString($params['diet_carbs']);
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

	public function getParams() : array
	{
		return $this->params;
	}

	public static function getDeviation($value, $ideal, $extremes) : Amount
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

	/*****************************************************************************
	 * Units.
	 */
	public function setUnits(string $value) : Calculator
	{
		if (!in_array($value, ['kJ', 'kcal'])) {
			throw new InvalidUnitsException;
		}

		$this->units = $value;

		return $this;
	}

	public function getUnits() : string
	{
		return $this->units;
	}

	/*****************************************************************************
	 * Gender.
	 */
	public function setGender(?Gender $value) : Calculator
	{
		$this->gender = $value;

		return $this;
	}

	public function getGender() : ?Gender
	{
		return $this->gender;
	}

	/*****************************************************************************
	 * Birthday.
	 */
	public function setBirthday(?Birthday $value) : Calculator
	{
		$this->birthday = $value;

		return $this;
	}

	public function getBirthday() : ?Birthday
	{
		return $this->birthday;
	}

	/*****************************************************************************
	 * Weight.
	 */
	public function setWeight(?Weight $value) : Calculator
	{
		$this->weight = $value;

		return $this;
	}

	public function getWeight() : ?Weight
	{
		return $this->weight;
	}

	/*****************************************************************************
	 * Proportions.
	 */
	public function getProportions() : Proportions
	{
		$this->proportions = $this->proportions instanceof Proportions ? $this->proportions : new Proportions;

		return $this->proportions;
	}

	/*****************************************************************************
	 * Body fat percentage.
	 */
	public function setBodyFatPercentage(?Percentage $value) : Calculator
	{
		$this->bodyFatPercentage = $value;

		return $this;
	}

	public function getBodyFatPercentage() : ?Percentage
	{
		return $this->bodyFatPercentage;
	}

	public function calcBodyFatPercentage() : ?Percentage
	{
		$gender = $this->getGender();
		if (!$gender) {
			throw new MissingGenderException;
		}

		return $this->getGender()->calcBodyFatPercentage($this);
	}

	public function getBodyFatPercentageFormula() : string
	{
		return $this->getGender()->getBodyFatPercentageFormula($this);
	}

	/*****************************************************************************
	 * Activity.
	 */
	public function setActivity(?Activity $activity) : Calculator
	{
		$this->activity = $activity;

		return $this;
	}

	public function getActivity() : ?Activity
	{
		return $this->activity;
	}

	public function calcActivity() : Activity
	{
		return $this->activity ?: new Activity(0);
	}

	public function getSportDurations() : SportDurations
	{
		$this->sportDurations = $this->sportDurations instanceof SportDurations ? $this->sportDurations : new SportDurations;

		return $this->sportDurations;
	}

	public function calcSportActivity() : Activity
	{
		return $this->getSportDurations()->calcSportActivity();
	}

	/*****************************************************************************
	 * Physical activity level.
	 */
	public function calcPhysicalActivityLevel() : Activity
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

		return new Activity($activity->getValue() + $sportActivity->getValue());
	}

	public function getPhysicalActivityLevelFormula() : string
	{
		$result = $this->calcPhysicalActivityLevel()->getValue();

		return 'activityPal[' . $this->calcActivity()->getValue() . '] + sportPal[' . $this->calcSportActivity()->getValue() . '] = ' . $result;
	}

	/*****************************************************************************
	 * Goal.
	 */
	public function getGoal() : Goal
	{
		$this->goal = $this->goal instanceof Goal ? $this->goal : new Goal;

		return $this->goal;
	}

	/*****************************************************************************
	 * Diet.
	 */
	public function getDiet() : Diet
	{
		$this->diet = $this->diet instanceof Diet ? $this->diet : new Diet;

		return $this->diet;
	}

	/*****************************************************************************
	 * Body mass index - BMI.
	 */
	public function calcBodyMassIndex() : Amount
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

		return new Amount($this->getWeight()->getInKg()->getAmount()->getValue() / pow($this->getProportions()->getHeight()->getInM()->getAmount()->getValue(), 2));
	}

	public function getBodyMassIndexFormula() : string
	{
		$result = $this->calcBodyMassIndex();

		return 'weight[' . $this->getWeight()->getInKg()->getAmount() . '] / pow(height[' . $this->getProportions()->getHeight()->getInM()->getAmount() . '], 2) = ' . $result;
	}

	public function getBodyMassIndexDeviation() : Amount
	{
		return static::getDeviation($this->calcBodyMassIndex()->getValue(), 22, [17.7, 40]);
	}

	/*****************************************************************************
	 * Waist-hip ratio - WHR.
	 */
	public function calcWaistHipRatio() : Amount
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

		return new Amount($this->getProportions()->getWaist()->getInCm()->getAmount()->getValue() / $this->getProportions()->getHips()->getInCm()->getAmount()->getValue());
	}

	public function getWaistHipRatioFormula() : string
	{
		$result = $this->calcWaistHipRatio();

		return 'waist[' . $this->getProportions()->getWaist()->getInCm()->getAmount() . '] / hips[' . $this->getProportions()->getHips()->getInCm()->getAmount() . '] = ' . $result;
	}

	public function calcWaistHipRatioDeviation() : Amount
	{
		$waistHipRatio = $this->calcWaistHipRatio()->getValue();

		if ($this->getGender() instanceof Genders\Male) {
			return static::getDeviation($waistHipRatio, .8, [.8, .95]);
		} elseif ($this->getGender() instanceof Genders\Female) {
			return static::getDeviation($waistHipRatio, .9, [.9, 1]);
		}
	}

	/*****************************************************************************
	 * Míra rizika.
	 */
	public function calcRiskDeviation() : Amount
	{
		$gender = $this->getGender();
		$bodyMassIndex = $this->calcBodyMassIndex()->getValue();
		$waistHipRatio = $this->calcWaistHipRatio()->getValue();
		$isOverweight = (bool)$this->calcFatOverOptimalWeight()->getMax()->getAmount();

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

		return new Amount($matrix[$column][$row]);
	}

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	public function calcBodyFatWeight() : Weight
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		$gender = $this->getGender();
		if (!$gender) {
			throw new MissingGenderException;
		}

		return new Weight(new Amount($weight->getInKg()->getAmount()->getValue() * $gender->calcBodyFatPercentage($this)->getValue()));
	}

	public function calcActiveBodyMassPercentage() : Percentage
	{
		return new Percentage(1 - $this->calcBodyFatPercentage($this)->getValue());
	}

	public function calcActiveBodyMassWeight() : Weight
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		return new Weight(new Amount($weight->getInKg()->getAmount()->getValue() * $this->calcActiveBodyMassPercentage()->getValue()));
	}

	public function calcOptimalFatPercentage() : Interval
	{
		if (!$this->getBirthday()) {
			throw new InvalidBirthdayException;
		}

		$gender = $this->getGender();
		$age = $this->getBirthday()->getAge();

		if ($gender instanceof Genders\Male) {
			if ($age < 18) {
				return new Interval(new Percentage(0), new Percentage(0));
			} elseif ($age >= 18 && $age < 30) {
				return new Interval(new Percentage(.10), new Percentage(.15));
			} elseif ($age >= 30 && $age < 50) {
				return new Interval(new Percentage(.11), new Percentage(.17));
			} else {
				return new Interval(new Percentage(.12), new Percentage(.19));
			}
		} elseif ($gender instanceof Genders\Female) {
			if ($age < 18) {
				return new Interval(new Percentage(0), new Percentage(0));
			} elseif ($age >= 18 && $age < 30) {
				return new Interval(new Percentage(.14), new Percentage(.21));
			} elseif ($age >= 30 && $age < 50) {
				return new Interval(new Percentage(.15), new Percentage(.23));
			} else {
				return new Interval(new Percentage(.16), new Percentage(.25));
			}
		}
	}

	public function calcOptimalFatWeight() : Interval
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		$optimalFatPercentage = $this->calcOptimalFatPercentage();
		if (!$optimalFatPercentage) {
			throw new UnableToCalcOptimalFatPercentageException;
		}

		return new Interval(
			new Weight(new Amount($weight->getInKg()->getAmount()->getValue() * $this->calcOptimalFatPercentage()->getMin()->getValue())),
			new Weight(new Amount($weight->getInKg()->getAmount()->getValue() * $this->calcOptimalFatPercentage()->getMax()->getValue())),
		);
	}

	public function getOptimalWeight() : Interval
	{
		$activeBodyMassWeight = $this->calcActiveBodyMassWeight();
		$optimalFatWeight = $this->calcOptimalFatWeight();

		return new Interval(
			new Weight(new Amount($activeBodyMassWeight->getInKg()->getAmount()->getValue() + $optimalFatWeight->getMin()->getInKg()->getAmount()->getValue())),
			new Weight(new Amount($activeBodyMassWeight->getInKg()->getAmount()->getValue() + $optimalFatWeight->getMax()->getInKg()->getAmount()->getValue())),
		);
	}

	public function calcEssentialFatPercentage() : Percentage
	{
		$gender = $this->getGender();
		if (!$gender) {
			throw new MissingGenderException;
		}

		return $this->getGender()->calcEssentialFatPercentage();
	}

	public function calcEssentialFatWeight()
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		$essentialFatPercentage = $this->calcEssentialFatPercentage();
		if (!$essentialFatPercentage) {
			throw new UnableToCalcEssentialFatPercentageException;
		}

		return new Weight(new Amount($weight->getInKg()->getAmount()->getValue() * $essentialFatPercentage->getValue()));
	}

	public function calcFatWithinOptimalPercentage()
	{
		$bodyFatWeight = $this->calcBodyFatWeight();
		$optimalFatWeight = $this->calcOptimalFatWeight();

		$min = $optimalFatWeight->getMin()->getInKg()->getAmount()->getValue() / $bodyFatWeight->getInKg()->getAmount()->getValue();
		$max = $optimalFatWeight->getMax()->getInKg()->getAmount()->getValue() / $bodyFatWeight->getInKg()->getAmount()->getValue();

		return new Interval(
			new Percentage($min <= 1 ? $min : 1),
			new Percentage($max <= 1 ? $max : 1),
		);
	}

	public function calcFatWithinOptimalWeight()
	{
		$bodyFatWeight = $this->calcBodyFatWeight();
		$optimalFatWeight = $this->calcOptimalFatWeight();

		$min = $bodyFatWeight->getInKg()->getAmount()->getValue() - $optimalFatWeight->getMin()->getInKg()->getAmount()->getValue();
		$max = $bodyFatWeight->getInKg()->getAmount()->getValue() - $optimalFatWeight->getMax()->getInKg()->getAmount()->getValue();

		return new Interval(
			new Weight(new Amount($bodyFatWeight->getInKg()->getAmount()->getValue() - ($min >= 0 ? $min : 0))),
			new Weight(new Amount($bodyFatWeight->getInKg()->getAmount()->getValue() - ($max >= 0 ? $max : 0))),
		);
	}

	public function calcFatOverOptimalPercentage()
	{
		$bodyFatWeight = $this->calcBodyFatWeight();
		$fatOverOptimalWeight = $this->calcFatOverOptimalWeight();

		$min = $fatOverOptimalWeight->getMin()->getInKg()->getAmount()->getValue() / $bodyFatWeight->getInKg()->getAmount()->getValue();
		$max = $fatOverOptimalWeight->getMax()->getInKg()->getAmount()->getValue() / $bodyFatWeight->getInKg()->getAmount()->getValue();

		return new Interval(
			new Percentage($min),
			new Percentage($max),
		);
	}

	public function calcFatOverOptimalWeight()
	{
		$bodyFatWeight = $this->calcBodyFatWeight();
		// print_r($bodyFatWeight);die;
		$optimalFatWeight = $this->calcOptimalFatWeight();
		// print_r($optimalFatWeight);die;

		$min = $bodyFatWeight->getInKg()->getAmount()->getValue() - $optimalFatWeight->getMin()->getInKg()->getAmount()->getValue();
		$max = $bodyFatWeight->getInKg()->getAmount()->getValue() - $optimalFatWeight->getMax()->getInKg()->getAmount()->getValue();

		return new Interval(
			new Weight(new Amount($min >= 0 ? $min : 0)),
			new Weight(new Amount($max >= 0 ? $max : 0)),
		);
	}

	public function calcBodyFatDeviation()
	{
		$gender = $this->getGender();
		$bodyMassIndex = $this->calcBodyMassIndex();
		$bodyMassIndexDeviation = $this->getBodyMassIndexDeviation();
		$isOverweight = (bool)$this->calcFatOverOptimalWeight()->getMax()->getAmount();

		if ($gender instanceof Genders\Male && $bodyMassIndex->getValue() >= .95 && !$isOverweight) {
			return 0;
		}

		return $bodyMassIndexDeviation;
	}

	/*****************************************************************************
	 * Beztuková tělesná hmotnost - FFM.
	 */
	public function calcFatFreeMass()
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new MissingWeightException;
		}

		return new Weight(new Amount($weight->getInKg()->getAmount()->getValue() - ($this->calcBodyFatPercentage()->getAsPercentage() * $weight->getInKg()->getAmount()->getValue())));
	}

	public function getFatFreeMassFormula()
	{
		$result = $this->calcFatFreeMass()->getInKg()->getAmount();

		return 'weight[' . $this->getWeight()->getInKg()->getAmount() . '] - (bodyFatPercentage[' . $this->calcBodyFatPercentage()->getAsPercentage() . '] * weight[' . $this->getWeight()->getInKg()->getAmount() . ']) = ' . $result;
	}

	/*****************************************************************************
	 * Bazální metabolismus - BMR.
	 */
	public function calcBasalMetabolicRate() : Energy
	{
		if (!$this->getGender()) {
			throw new MissingGenderException;
		}

		return $this->getGender()->calcBasalMetabolicRate($this);
	}

	public function getBasalMetabolicRateFormula() : string
	{
		$result = $this->calcBasalMetabolicRate()->getAmount();

		return $this->getGender()->getBasalMetabolicRateFormula($this) . ' = ' . $result;
	}

	/*****************************************************************************
	 * Total Energy Expenditure - Termický efekt pohybu - TEE.
	 */
	public function calcTotalEnergyExpenditure()
	{
		return new Energy(new Amount($this->calcBasalMetabolicRate()->getAmount()->getValue() * $this->calcPhysicalActivityLevel()->getValue()), 'kCal');
	}

	public function getTotalEnergyExpenditureFormula()
	{
		$result = $this->calcTotalEnergyExpenditure()->getAmount();

		return 'basalMetabolicRate[' . $this->calcBasalMetabolicRate()->getAmount()->getValue() . '] * physicalActivityLevel[' . $this->calcPhysicalActivityLevel()->getValue() . '] = ' . $result;
	}

	/*****************************************************************************
	 * Total Daily Energy Expenditure - Celkový doporučený denní příjem - TDEE.
	 */
	public function calcTotalDailyEnergyExpenditure()
	{
		if (!$this->getGoal()->getVector()) {
			throw new MissingGoalVectorException;
		}

		return new Energy(new Amount($this->calcTotalEnergyExpenditure()->getAmount()->getValue() * $this->getGoal()->getVector()->getTdeeQuotient($this)->getValue()), 'kCal');
	}

	public function getTotalDailyEnergyExpenditureFormula()
	{
		$result = $this->calcTotalDailyEnergyExpenditure()->getAmount();

		return 'totalEnergyExpenditure[' . $this->calcTotalEnergyExpenditure()->getAmount() . '] * weightGoalQuotient[' . $this->getGoal()->getVector()->getTdeeQuotient($this)->getValue() . '] = ' . $result;
	}

	/*****************************************************************************
	 * Reference Daily Intake - Doporučený denní příjem - DDP.
	 */
	public function calcReferenceDailyIntake()
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

		if ($this->getDiet() instanceof Approaches\Ned) {
			return new Energy(new Amount((float)Approaches\Ned::ENERGY_DEFAULT), 'kCal');
		} else {
			return new Energy(new Amount($totalDailyEnergyExpenditure->getAmount()->getValue() + $referenceDailyIntakeBonus->getAmount()->getValue()), 'kCal');
		}
	}

	public function getReferenceDailyIntakeFormula()
	{
		$result = $this->calcReferenceDailyIntake()->getAmount();

		if ($this->getDiet() instanceof Approaches\Ned) {
			return $result;
		} else {
			return 'totalDailyEnergyExpenditure[' . $this->calcTotalDailyEnergyExpenditure()->getAmount() . '] + referenceDailyIntakeBonus[' . $this->gender->calcReferenceDailyIntakeBonus()->getAmount() . '] = ' . $result;
		}
	}

	/*****************************************************************************
	 * Body type - typ postavy.
	 */
	public function calcBodyType() : BodyType
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
	public function getGoalNutrients()
	{
		$nutrients = new Nutrients;

		/***************************************************************************
		 * Proteins.
		 */
		// 1
		if ($this->getSportDurations()->getTotalDuration() > 60 || $this->calcPhysicalActivityLevel()->getValue() >= 1.9) {
			// 13
			if ($this->getGender() instanceof Genders\Male) {
				// 14
				if ($this->calcFatOverOptimalWeight()->getMax()->getInKg()->getAmount()) {
					$optimalWeight = $this->getOptimalWeight()->getMax();

				// 15
				} else {
					$optimalWeight = $this->getWeight();
				}

				$matrix = [
					'fit'   => [1.5, 2.2, 1.8],
					'unfit' => [1.5, 2,   1.7],
				];
				$matrixSet = ($this->calcBodyFatPercentage()->getValue() > .19 || $this->calcBodyMassIndex()->getValue() > 25) ? 'unfit' : 'fit';

				$optimalNutrients = [];
				foreach ($this->getSportDurations()->getMaxDurations() as $sportDuration) {
					if ($sportDuration instanceof SportDurations\LowFrequency) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][0];
					} elseif ($sportDuration instanceof SportDurations\Anaerobic) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][1];
					} elseif ($sportDuration instanceof SportDurations\Aerobic) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][2];
					}
				}

				if ($this->calcPhysicalActivityLevel()->getValue() >= 1.9) {
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
					if ($this->calcFatOverOptimalWeight()->getMax()->getInKg()->getAmount()) {
						$optimalWeight = $this->getOptimalWeight()->getMax();

					// 18
					} else {
						$optimalWeight = $this->getWeight();
					}

					$matrix = [
						'fit'   => [1.4, 1.8, 1.6],
						'unfit' => [1.5, 1.8, 1.8],
					];
					$matrixSet = ($this->calcBodyFatPercentage()->getValue() > .25 || $this->calcBodyMassIndex()->getValue() > 25) ? 'unfit' : 'fit';

					$optimalNutrients = [];
					foreach ($this->getSportDurations()->getMaxDurations() as $sportDuration) {
						if ($sportDuration instanceof SportDurations\LowFrequency) {
							$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][0];
						} elseif ($sportDuration instanceof SportDurations\Anaerobic) {
							$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][1];
						} elseif ($sportDuration instanceof SportDurations\Aerobic) {
							$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][2];
						}
					}

					if ($this->calcPhysicalActivityLevel()->getValue() >= 1.9) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][1];
					}

					$nutrients->setProteins(new Nutrients\Proteins(new Amount(max($optimalNutrients)), 'g'));

					// 19
					if ($this->getGender()->isPregnant() || $this->getGender()->isBreastfeeding()) {
						$nutrients->setProteins(new Nutrients\Proteins($nutrients->getProteins()->getInG()->getAmount() + 20, 'g'));
					}
				}
			}

		// 2
		} else {
			// 3
			if ($this->getGender() instanceof Genders\Female && ($this->getGender()->isPregnant() || $this->getGender()->isBreastfeeding())) {
				// 11
				$nutrients->setProteins(new Nutrients\Proteins(min(($this->getWeight()->getInKg()->getAmount()->getValue() * 1.4) + 20, 90), 'g'));

			// 4
			} else {
				// 5
				if ($this->getGender() instanceof Genders\Male) {
					// 7
					if ($this->calcFatOverOptimalWeight()->getMax()->getInKg()->getAmount()) {
						$nutrients->setProteins(new Nutrients\Proteins(new Amount($this->getOptimalWeight()->getMax()->getInKg()->getAmount()->getValue() * 1.5), 'g'));

					// 8
					} else {
						$nutrients->setProteins(new Nutrients\Proteins(new Amount($this->getWeight()->getInKg()->getAmount()->getValue() * 1.5), 'g'));
					}
				// 6
				} elseif ($this->getGender() instanceof Genders\Female) {
					// 9
					if ($this->calcFatOverOptimalWeight()->getMax()->getInKg()->getAmount()) {
						$nutrients->setProteins(new Nutrients\Proteins(new Amount($this->getOptimalWeight()->getMax()->getInKg()->getAmount()->getValue() * 1.4), 'g'));

					// 10
					} else {
						$nutrients->setProteins(new Nutrients\Proteins(new Amount($this->getWeight()->getInKg()->getAmount()->getValue() * 1.4), 'g'));
					}
				}
			}
		}

		/***************************************************************************
		 * Carbs and fats.
		 */
		$goalTdee = $this->getGoal()->calcGoalTdee($this);
		$diet = $this->getDiet();
		$dietApproach = $this->getDiet()->getApproach();
		if (!$dietApproach) {
			throw new MissingDietApproachException;
		}

		// 1
		if ($dietApproach instanceof Approaches\Standard) {
			// 4
			if ($this->getSportDurations()->getAnaerobic() instanceof SportDuration && $this->getSportDurations()->getAnaerobic()->getAmount()->getValue() >= 100) {
				$nutrients->setCarbs(Nutrients\Carbs::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() * .58), 'kJ')));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() - $nutrients->getEnergy()->getInKJ()->getAmount()->getValue()))));
			// 5
			} elseif ($this->getGender() instanceof Genders\Female && ($this->getGender()->isPregnant() || $this->getGender()->isBreastfeeding())) {
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() * .35), 'kJ')));
				$nutrients->setCarbs(Nutrients\Carbs::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() - $nutrients->getEnergy()->getInKJ()->getAmount()->getValue()))));
			} else {
				$nutrients->setCarbs(Nutrients\Carbs::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() * .55), 'kJ')));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() - $nutrients->getEnergy()->getInKJ()->getAmount()->getValue()))));
			}

		// Mediterranean diet.
		} elseif ($dietApproach instanceof Approaches\Standard) {
			$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() * .4), 'kJ')));
			$nutrients->setCarbs(Nutrients\Carbs::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() - $nutrients->getEnergy()->getInKJ()->getAmount()->getValue()))));

		// 2
		} elseif ($dietApproach instanceof Approaches\LowCarb) {
			// 7
			if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
				$dietCarbs = $diet->getCarbs();
				$nutrients->setCarbs(new Nutrients\Carbs($dietCarbs->getAmount(), $dietCarbs->getUnit()));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() - $nutrients->getEnergy()->getInKJ()->getAmount()->getValue()))));
				// @TODO - message
			// 8
			} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
				$dietCarbs = $diet->getCarbs();
				$nutrients->setCarbs(new Nutrients\Carbs($dietCarbs->getAmount(), $dietCarbs->getUnit()));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() - $nutrients->getEnergy()->getInKJ()->getAmount()->getValue()))));
				// @TODO - message
			// 9
			} else {
				$dietCarbs = $diet->getCarbs();
				$nutrients->setCarbs(new Nutrients\Carbs($dietCarbs->getAmount(), $dietCarbs->getUnit()));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() - $nutrients->getEnergy()->getInKJ()->getAmount()->getValue()))));
			}

		// 3
		} elseif ($dietApproach instanceof Approaches\Keto) {
			// 7
			if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
				// @TODO - message
			// 8
			} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
				// @TODO - message
			// 9
			} else {
				$dietCarbs = $diet->getCarbs();
				$nutrients->setCarbs(new Nutrients\Carbs($dietCarbs->getAmount(), $dietCarbs->getUnit()));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy(new Amount($goalTdee->getInKJ()->getAmount()->getValue() - $nutrients->getEnergy()->getInKJ()->getAmount()->getValue()))));
			}
		// NED diet.
		} elseif ($dietApproach instanceof Approaches\Ned) {
			$nutrients->setCarbs((new Approaches\Ned)->getCarbsDefault());
			$nutrients->setFats((new Approaches\Ned)->getFatsDefault());
			$nutrients->setProteins((new Approaches\Ned)->getProteinsDefault());
		}

		return $nutrients;
	}

	/*****************************************************************************
	 * Messages.
	 */
	public function getBodyFatMessages()
	{
		$messages = [];

		// High sport physical activity level (>= 2).
		if ($this->calcPhysicalActivityLevel()->getValue() >= 2 && ($this->getSportDurations()->getAerobic()->getAmount() || $this->getSportDurations()->getAnaerobic()->getAmount())) {
			$messages[] = [
				'message' => \Katu\Config::get('caloricCalculator', 'messages', 'highSportPhysicalActivityLevel'),
				'fields' => ['sportDurations[aerobic]', 'sportDurations[anaerobic]'],
			];
		}

		return $messages;
	}

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
	// 					$weightChange = new Weight($this->getWeight()->getInKg()->getAmount() - $this->getGoal()->getWeight()->getInKg()->getAmount());

	// 					$messages[] = [
	// 						'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'loosingWeightRealistic'), [
	// 							'%weightChange%' => $weightChange,
	// 						]),
	// 						'fields' => ['goalWeight'],
	// 					];

	// 					$slowLooseTdee = (new Vectors\SlowLoose)->calcGoalTdee($this);
	// 					$looseTdee = (new Vectors\Loose)->calcGoalTdee($this);

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
	// 				$weightChange = new Weight($this->getGoal()->getWeight()->getInKg()->getAmount() - $this->getWeight()->getInKg()->getAmount());

	// 				$messages[] = [
	// 					'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'gainingWeightRealistic'), [
	// 						'%weightChange%' => $weightChange,
	// 					]),
	// 					'fields' => ['goalWeight'],
	// 				];

	// 				$slowGainTdee = (new Vectors\SlowGain)->calcGoalTdee($this);
	// 				$gainTdee = (new Vectors\Gain)->calcGoalTdee($this);

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

	public function getResponse() : array
	{
		$exceptionCollection = new FattyExceptionCollection;

		$res = [];

		/**************************************************************************
		 * Input.
		 */
		$res['input']['gender'] = $this->getGender() ? $this->getGender()->getCode() : null;
		$res['input']['birthday'] = $this->getBirthday() ? $this->getBirthday()->getDatetime()->format('Y-m-d') : null;
		$res['input']['weight'] = $this->getWeight() ? $this->getWeight()->getArray() : null;
		$res['input']['proportions_height'] = $this->getProportions()->getHeight() ? $this->getProportions()->getHeight()->getArray() : null;
		$res['input']['proportions_waist'] = $this->getProportions()->getWaist() ? $this->getProportions()->getWaist()->getArray() : null;
		$res['input']['proportions_hips'] = $this->getProportions()->getHips() ? $this->getProportions()->getHips()->getArray() : null;
		$res['input']['proportions_neck'] = $this->getProportions()->getNeck() ? $this->getProportions()->getNeck()->getArray() : null;
		$res['input']['bodyFatPercentage'] = $this->getBodyFatPercentage() ? $this->getBodyFatPercentage()->getArray() : null;
		$res['input']['activity'] = $this->getActivity() ? $this->getActivity()->getValue() : null;
		$res['input']['sportDurations_lowFrequency'] = $this->getSportDurations()->getLowFrequency() ? $this->getSportDurations()->getLowFrequency()->getArray() : null;
		$res['input']['sportDurations_aerobic'] = $this->getSportDurations()->getAerobic() ? $this->getSportDurations()->getAerobic()->getArray() : null;
		$res['input']['sportDurations_anaerobic'] = $this->getSportDurations()->getAnaerobic() ? $this->getSportDurations()->getAnaerobic()->getArray() : null;
		$res['input']['goal_vector'] = $this->getGoal()->getVector() ? $this->getGoal()->getVector()->getCode() : null;
		$res['input']['goal_weight'] = $this->getGoal()->getWeight() ? $this->getGoal()->getWeight()->getArray() : null;
		$res['input']['diet_approach'] = $this->getDiet()->getApproach() ? $this->getDiet()->getApproach() : null;
		$res['input']['diet_carbs'] = $this->getDiet()->getCarbs() ? $this->getDiet()->getCarbs()->getArray() : null;

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
		try {
			$metric = $this->getWeight();
			if ($metric) {
				$res['output']['metrics']['weight']['result'] = $metric->getArray();
				$res['output']['metrics']['weight']['string'] = (string)$metric;
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->getProportions()->getHeight();
			if ($metric) {
				$res['output']['metrics']['height']['result'] = $metric->getArray();
				$res['output']['metrics']['height']['string'] = (string)$metric;
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcBodyMassIndex();
			if ($metric) {
				$res['output']['metrics']['bodyMassIndex']['result'] = [
					'amount' => $metric->getValue(),
					'unit' => null,
				];
				$res['output']['metrics']['bodyMassIndex']['string'] = (string)$metric;
				$res['output']['metrics']['bodyMassIndex']['formula'] = $this->getBodyMassIndexFormula();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->getBodyMassIndexDeviation();
			$res['output']['metrics']['bodyMassIndexDeviation']['result'] = [
				'amount' => $metric,
				'unit' => null,
			];
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcWaistHipRatio();
			if ($metric) {
				$res['output']['metrics']['waistHipRatio']['result'] = [
					'amount' => $metric->getValue(),
					'unit' => null,
				];
				$res['output']['metrics']['waistHipRatio']['string'] = (string)$metric;
				$res['output']['metrics']['waistHipRatio']['formula'] = $this->getWaistHipRatioFormula();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcWaistHipRatioDeviation();
			$res['output']['metrics']['waistHipRatioDeviation']['result'] = [
				'amount' => $metric,
				'unit' => null,
			];
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcBodyFatPercentage($this);
			if ($metric) {
				$res['output']['metrics']['bodyFatPercentage']['result'] = $metric->getArray();
				$res['output']['metrics']['bodyFatPercentage']['string'] = (string)$metric;
				$res['output']['metrics']['bodyFatPercentage']['formula'] = $this->getBodyFatPercentageFormula();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcBodyFatWeight();
			if ($metric) {
				$res['output']['metrics']['bodyFatWeight']['result'] = $metric->getArray();
				$res['output']['metrics']['bodyFatWeight']['string'] = (string)$metric;
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcActiveBodyMassPercentage();
			if ($metric) {
				$res['output']['metrics']['activeBodyMassPercentage']['result'] = $metric->getArray();
				$res['output']['metrics']['activeBodyMassPercentage']['string'] = (string)$metric;
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcOptimalFatPercentage();
			if ($metric) {
				$res['output']['metrics']['optimalFatPercentageMin']['result'] = $metric->getMin()->getArray();
				$res['output']['metrics']['optimalFatPercentageMin']['string'] = (string)$metric->getMin();
				$res['output']['metrics']['optimalFatPercentageMax']['result'] = $metric->getMax()->getArray();
				$res['output']['metrics']['optimalFatPercentageMax']['string'] = (string)$metric->getMax();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcOptimalFatWeight();
			if ($metric) {
				$res['output']['metrics']['optimalFatWeightMin']['result'] = $metric->getMin()->getArray();
				$res['output']['metrics']['optimalFatWeightMin']['string'] = (string)$metric->getMin();
				$res['output']['metrics']['optimalFatWeightMax']['result'] = $metric->getMax()->getArray();
				$res['output']['metrics']['optimalFatWeightMax']['string'] = (string)$metric->getMax();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcEssentialFatPercentage();
			if ($metric) {
				$res['output']['metrics']['essentialFatPercentage']['result'] = $metric->getArray();
				$res['output']['metrics']['essentialFatPercentage']['string'] = (string)$metric;
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcEssentialFatWeight();
			if ($metric) {
				$res['output']['metrics']['essentialFatWeight']['result'] = $metric->getArray();
				$res['output']['metrics']['essentialFatWeight']['string'] = (string)$metric;
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcFatWithinOptimalPercentage();
			if ($metric) {
				$res['output']['metrics']['fatWithinOptimalPercentageMin']['result'] = $metric->getMin()->getArray();
				$res['output']['metrics']['fatWithinOptimalPercentageMin']['string'] = (string)$metric->getMin();
				$res['output']['metrics']['fatWithinOptimalPercentageMax']['result'] = $metric->getMax()->getArray();
				$res['output']['metrics']['fatWithinOptimalPercentageMax']['string'] = (string)$metric->getMax();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcFatWithinOptimalWeight();
			if ($metric) {
				$res['output']['metrics']['fatWithinOptimalWeightMin']['result'] = $metric->getMin()->getArray();
				$res['output']['metrics']['fatWithinOptimalWeightMin']['string'] = (string)$metric->getMin();
				$res['output']['metrics']['fatWithinOptimalWeightMax']['result'] = $metric->getMax()->getArray();
				$res['output']['metrics']['fatWithinOptimalWeightMax']['string'] = (string)$metric->getMax();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcFatOverOptimalPercentage();
			if ($metric) {
				$res['output']['metrics']['fatOverOptimalPercentageMin']['result'] = $metric->getMin()->getArray();
				$res['output']['metrics']['fatOverOptimalPercentageMin']['string'] = (string)$metric->getMin();
				$res['output']['metrics']['fatOverOptimalPercentageMax']['result'] = $metric->getMax()->getArray();
				$res['output']['metrics']['fatOverOptimalPercentageMax']['string'] = (string)$metric->getMax();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcFatOverOptimalWeight();
			if ($metric) {
				$res['output']['metrics']['fatOverOptimalWeightMin']['result'] = $metric->getMin()->getArray();
				$res['output']['metrics']['fatOverOptimalWeightMin']['string'] = (string)$metric->getMin();
				$res['output']['metrics']['fatOverOptimalWeightMax']['result'] = $metric->getMax()->getArray();
				$res['output']['metrics']['fatOverOptimalWeightMax']['string'] = (string)$metric->getMax();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcBodyFatDeviation();
			$res['output']['metrics']['bodyFatDeviation']['result'] = [
				'amount' => $metric,
				'unit' => null,
			];
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcRiskDeviation();
			$res['output']['metrics']['riskDeviation']['result'] = [
				'amount' => $metric,
				'unit' => null,
			];
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcActiveBodyMassWeight();
			if ($metric) {
				$res['output']['metrics']['activeBodyMassWeight']['result'] = $metric->getArray();
				$res['output']['metrics']['activeBodyMassWeight']['string'] = (string)$metric;
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcFatFreeMass();
			if ($metric) {
				$res['output']['metrics']['fatFreeMass']['result'] = $metric->getArray();
				$res['output']['metrics']['fatFreeMass']['string'] = (string)$metric;
				$res['output']['metrics']['fatFreeMass']['formula'] = $this->getFatFreeMassFormula();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcBasalMetabolicRate()->getInUnit($this->getUnits());
			if ($metric) {
				$res['output']['metrics']['basalMetabolicRate']['result'] = $metric->getArray();
				$res['output']['metrics']['basalMetabolicRate']['string'] = (string)$metric;
				$res['output']['metrics']['basalMetabolicRate']['formula'] = $this->getBasalMetabolicRateFormula();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcPhysicalActivityLevel();
			if ($metric) {
				$res['output']['metrics']['physicalActivityLevel']['result'] = [
					'amount' => $metric->getValue(),
					'unit' => null,
				];
				$res['output']['metrics']['physicalActivityLevel']['string'] = (string)$metric;
				$res['output']['metrics']['physicalActivityLevel']['formula'] = $this->getPhysicalActivityLevelFormula();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcTotalEnergyExpenditure()->getInUnit($this->getUnits());
			if ($metric) {
				$res['output']['metrics']['totalEnergyExpenditure']['result'] = $metric->getArray();
				$res['output']['metrics']['totalEnergyExpenditure']['string'] = (string)$metric;
				$res['output']['metrics']['totalEnergyExpenditure']['formula'] = $this->getTotalEnergyExpenditureFormula();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcTotalDailyEnergyExpenditure()->getInUnit($this->getUnits());
			if ($metric) {
				$res['output']['metrics']['totalDailyEnergyExpenditure']['result'] = $metric->getArray();
				$res['output']['metrics']['totalDailyEnergyExpenditure']['string'] = (string)$metric;
				$res['output']['metrics']['totalDailyEnergyExpenditure']['formula'] = $this->getTotalDailyEnergyExpenditureFormula();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->calcReferenceDailyIntake()->getInUnit($this->getUnits());
			if ($metric) {
				$res['output']['metrics']['referenceDailyIntake']['result'] = $metric->getArray();
				$res['output']['metrics']['referenceDailyIntake']['string'] = (string)$metric;
				$res['output']['metrics']['referenceDailyIntake']['formula'] = $this->getReferenceDailyIntakeFormula();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		// try {
		// 	$metric = $this->getGoalTrend();
		// 	if ($metric) {
		// 		$res['output']['metrics']['goalTrend']['result'] = [
		// 			'amount' => $metric->getCode(),
		// 			'unit' => null,
		// 		];
		// 		$res['output']['metrics']['goalTrend']['string'] = (string)$metric;
		// 	}
		// } catch (FattyException $e) {
		// 	$ec->add($e);
		// }

		// try {
		// 	$metric = $this->getGoalWeight();
		// 	if ($metric) {
		// 		$res['output']['metrics']['goalWeight']['result'] = $metric->getArray();
		// 		$res['output']['metrics']['goalWeight']['string'] = (string)$metric;
		// 	}
		// } catch (FattyException $e) {
		// 	$ec->add($e);
		// }

		try {
			$metric = $this->getGoal()->calcGoalTdee($this)->getInUnit($this->getUnits());
			if ($metric) {
				$res['output']['metrics']['goalTdee']['result'] = $metric->getArray();
				$res['output']['metrics']['goalTdee']['string'] = (string)$metric;
			}
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
			$metric = $this->calcBodyType();
			if ($metric) {
				$res['output']['metrics']['bodyType']['result'] = $metric->getCode();
				$res['output']['metrics']['bodyType']['string'] = (string)$metric;
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		try {
			$metric = $this->getGoalNutrients();
			if ($metric) {
				$res['output']['metrics']['nutrientsCarbs']['result'] = $metric->getCarbs()->getArray();
				$res['output']['metrics']['nutrientsCarbs']['string'] = (string)$metric->getCarbs();
				$res['output']['metrics']['nutrientsProteins']['result'] = $metric->getProteins()->getArray();
				$res['output']['metrics']['nutrientsProteins']['string'] = (string)$metric->getProteins();
				$res['output']['metrics']['nutrientsFats']['result'] = $metric->getFats()->getArray();
				$res['output']['metrics']['nutrientsFats']['string'] = (string)$metric->getFats();
			}
		} catch (FattyException $e) {
			$exceptionCollection->add($e);
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		if (count($exceptionCollection)) {
			throw $exceptionCollection;
		}

		return $res;
	}
}
