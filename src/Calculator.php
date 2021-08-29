<?php

namespace Fatty;

use Fatty\Exceptions\FattyException;
use Fatty\SportDurations\Aerobic;
use Fatty\SportDurations\Anaerobic;
use Fatty\SportDurations\LowFrequency;

class Calculator
{
	protected $activity;
	protected $birthday;
	protected $diet;
	protected $gender;
	protected $goal;
	protected $measurements = [];
	protected $proportions;
	protected $sportDurations;
	protected $weight;

	public function __construct(?array $params = [])
	{
		$exceptions = new \Katu\Exceptions\ExceptionCollection;

		if (trim($params['gender'] ?? null)) {
			try {
				$value = \Fatty\Gender::createFromString($params['gender']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("Neplatné pohlaví.");
				}

				$this->setGender($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['birthday'] ?? null)) {
			try {
				$value = \Fatty\Birthday::createFromString($params['birthday']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("Neplatné datum narození.");
				}

				$this->setBirthday($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['weight'] ?? null)) {
			try {
				$value = Weight::createFromString($params['weight']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("Neplatná hmotnost.");
				}

				$this->setWeight($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['proportions_height'] ?? null)) {
			try {
				$value = Length::createFromString($params['proportions_height']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("Neplatná výška.");
				}

				$this->getProportions()->setHeight($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['proportions_waist'] ?? null)) {
			try {
				$value = Length::createFromString($params['proportions_waist']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("Neplatný obvod pasu.");
				}

				$this->getProportions()->setWaist($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['proportions_hips'] ?? null)) {
			try {
				$value = Length::createFromString($params['proportions_hips']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("Neplatný obvod boků.");
				}

				$this->getProportions()->setHips($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['proportions_neck'] ?? null)) {
			try {
				$value = Length::createFromString($params['proportions_neck']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("Neplatný obvod boků.");
				}

				$this->getProportions()->setNeck($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['bodyFatPercentage'] ?? null)) {
			try {
				$value = Percentage::createFromString($params['bodyFatPercentage']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("Neplatné procento tělesného tuku.");
				}

				$this->setMeasurementBodyFatPercentage($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['activity'] ?? null)) {
			try {
				$value = Activity::createFromString($params['activity']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("Neplatná uroveň aktivity.");
				}

				$this->setActivity($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['sportDurations_lowFrequency'] ?? null)) {
			try {
				$value = LowFrequency::createFromString($params['sportDurations_lowFrequency']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("invalid sportDurations_lowFrequency");
				}

				$this->getSportDurations()->setLowFrequency($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['sportDurations_aerobic'] ?? null)) {
			try {
				$value = Aerobic::createFromString($params['sportDurations_aerobic']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("invalid sportDurations_aerobic");
				}

				$this->getSportDurations()->setAerobic($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['sportDurations_anaerobic'] ?? null)) {
			try {
				$value = Anaerobic::createFromString($params['sportDurations_anaerobic']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("invalid sportDurations_anaerobic");
				}

				$this->getSportDurations()->setAnaerobic($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		$this->getGoal()->setDuration(new Duration(new Amount(12), 'weeks'));

		if (trim($params['goal_vector'] ?? null)) {
			try {
				$value = Vector::createFromString($params['goal_vector']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("invalid goal_vector");
				}

				$this->getGoal()->setVector($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['goal_weight'] ?? null)) {
			try {
				$value = Weight::createFromString($params['goal_weight']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("invalid goal_weight");
				}

				$this->getGoal()->setWeight($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		if (trim($params['diet_approach'] ?? null)) {
			try {
				$value = Approach::createFromString($params['diet_approach']);
				if (!$value) {
					throw new \Katu\Exceptions\InputErrorException("invalid diet_approach");
				}

				$this->getDiet()->setApproach($value);
			} catch (\Throwable $e) {
				$exceptions->add($e);
			}
		}

		// if (trim($params['diet_carbs'] ?? null)) {
		// 	try {
		// 		$this->setDietCarbs($params['diet_carbs']);
		// 	} catch (\Throwable $e) {
		// 		$exceptions->add($e);
		// 	}
		// }

		// if ($this->getGender() instanceof \App\Classes\Profile\Genders\Female) {
		// 	if (isset($params['pregnancyIsPregnant']) && $params['pregnancyIsPregnant']) {
		// 		$this->getGender()->setIsPregnant(true);

		// 		if (isset($params['pregnancyChildbirthDate'])) {
		// 			try {
		// 				$this->getGender()->setPregnancyChildbirthDate($params['pregnancyChildbirthDate']);
		// 			} catch (\Throwable $e) {
		// 				$exceptions->add($e);
		// 			}
		// 		}
		// 	}

		// 	if (isset($params['breastfeedingIsBreastfeeding']) && $params['breastfeedingIsBreastfeeding']) {
		// 		$this->getGender()->setIsBreastfeeding(true);

		// 		if (isset($params['breastfeeding']['childbirthDate'])) {
		// 			try {
		// 				$this->getGender()->setBreastfeedingChildbirthDate($params['breastfeeding']['childbirthDate']);
		// 			} catch (\Throwable $e) {
		// 				$exceptions->add($e);
		// 			}
		// 		}

		// 		if (isset($params['breastfeedingMode'])) {
		// 			try {
		// 				$this->getGender()->setBreastfeedingMode($params['breastfeedingMode']);
		// 			} catch (\Throwable $e) {
		// 				$exceptions->add($e);
		// 			}
		// 		}
		// 	}
		// }

		if ($exceptions->has()) {
			throw $exceptions;
		}
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
	 * Hmotnost.
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
	 * Proporce.
	 */
	public function getProportions() : Proportions
	{
		$this->proportions = $this->proportions instanceof Proportions ? $this->proportions : new Proportions;

		return $this->proportions;
	}

	/*****************************************************************************
	 * Naměřené hodnoty.
	 */
	public function setMeasurementBodyFatPercentage(?Percentage $value) : Calculator
	{
		$this->measurements['bodyFatPercentage'] = $value;

		return $this;
	}

	public function getMeasurementBodyFatPercentage() : ?Percentage
	{
		return $this->measurements['bodyFatPercentage'];
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

	public function getSportDurations() : SportDurations
	{
		$this->sportDurations = $this->sportDurations instanceof SportDurations ? $this->sportDurations : new SportDurations;

		return $this->sportDurations;
	}

	public function getSportActivityAmount()
	{
		return $this->getSportDurations()->getActivityAmount();
	}

	/*****************************************************************************
	 * Physical activity level.
	 */
	public function getPhysicalActivityLevel()
	{
		$ec = new \Katu\Exceptions\ExceptionCollection;

		try {
			$activityAmount = $this->getActivityAmount();
		} catch (\Throwable $e) {
			$ec->add($e);
		}

		try {
			$sportActivityAmount = $this->getSportActivityAmount();
		} catch (\Throwable $e) {
			$ec->add($e);
		}

		if ($ec->has()) {
			throw $ec;
		}

		return new ActivityAmount($activityAmount->getAmount() + $sportActivityAmount->getAmount());
	}

	public function getPhysicalActivityLevelFormula()
	{
		$result = $this->getPhysicalActivityLevel()->getAmount();

		return 'activityPal[' . $this->getActivityAmount()->getAmount() . '] + sportPal[' . $this->getSportActivityAmount()->getAmount() . '] = ' . $result;
	}

	/*****************************************************************************
	 * Cíle.
	 */
	public function getGoal() : Goal
	{
		$this->goal = $this->goal instanceof Goal ? $this->goal : new Goal;

		return $this->goal;
	}

	/*****************************************************************************
	 * Dieta.
	 */
	public function getDiet() : Diet
	{
		$this->diet = $this->diet instanceof Diet ? $this->diet : new Diet;

		return $this->diet;
	}

	/*****************************************************************************
	 * Body mass index - BMI.
	 */
	public function getBodyMassIndex()
	{
		$ec = new \Katu\Exceptions\ExceptionCollection;

		if (!($this->getWeight() instanceof Weight)) {
			$ec->add((new CaloricCalculatorException("Missing weight."))
				->setAbbr('missingWeight'));
		}

		if (!($this->getProportions()->getHeight() instanceof Length)) {
			$ec->add((new CaloricCalculatorException("Missing height."))
				->setAbbr('missingHeight'));
		}

		if ($ec->has()) {
			throw $ec;
		}

		return new Amount($this->getWeight()->getInKg()->getAmount() / pow($this->getProportions()->getHeight()->getInM()->getAmount(), 2));
	}

	public function getBodyMassIndexFormula()
	{
		$result = $this->getBodyMassIndex();

		return 'weight[' . $this->getWeight()->getInKg()->getAmount() . '] / pow(height[' . $this->getProportions()->getHeight()->getInM()->getAmount() . '], 2) = ' . $result;
	}

	public function getBodyMassIndexDeviation()
	{
		return static::getDeviation($this->getBodyMassIndex()->getAmount(), 22, [17.7, 40]);
	}

	/*****************************************************************************
	 * Waist-hip ratio - WHR.
	 */
	public function getWaistHipRatio()
	{
		$ec = new \Katu\Exceptions\ExceptionCollection;

		if (!($this->getProportions()->getWaist() instanceof Length)) {
			$ec->add((new CaloricCalculatorException("Missing waist."))
				->setAbbr('missingWaist'));
		}

		if (!($this->getProportions()->getHips() instanceof Length)) {
			$ec->add((new CaloricCalculatorException("Missing hips."))
				->setAbbr('missingHips'));
		}

		if ($ec->has()) {
			throw $ec;
		}

		return new Amount($this->getProportions()->getWaist()->getInCm()->getAmount() / $this->getProportions()->getHips()->getInCm()->getAmount());
	}

	public function getWaistHipRatioFormula()
	{
		$result = $this->getWaistHipRatio();

		return 'waist[' . $this->getProportions()->getWaist()->getInCm()->getAmount() . '] / hips[' . $this->getProportions()->getHips()->getInCm()->getAmount() . '] = ' . $result;
	}

	public function getWaistHipRatioDeviation()
	{
		$waistHipRatio = $this->getWaistHipRatio()->getAmount();

		if ($this->getGender() instanceof Genders\Male) {
			return static::getDeviation($waistHipRatio, .8, [.8, .95]);
		} elseif ($this->getGender() instanceof Genders\Female) {
			return static::getDeviation($waistHipRatio, .9, [.9, 1]);
		}
	}

	/*****************************************************************************
	 * Míra rizika.
	 */
	public function getRiskDeviation()
	{
		$gender = $this->getGender();
		$bodyMassIndex = $this->getBodyMassIndex()->getAmount();
		$waistHipRatio = $this->getWaistHipRatio()->getAmount();
		$isOverweight = (bool)$this->getFatOverOptimalWeight()->getMax()->getAmount();

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

		return $matrix[$column][$row];
	}

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	public function getBodyFatPercentage()
	{
		$ec = new \Katu\Exceptions\ExceptionCollection;

		if (!($this->getGender() instanceof Gender)) {
			$ec->add((new CaloricCalculatorException("Missing gender."))
				->setAbbr('missingGender'));
		}

		if ($ec->has()) {
			throw $ec;
		}

		return $this->getGender()->getBodyFatPercentage($this);
	}

	public function getBodyFatPercentageFormula()
	{
		return $this->getGender()->getBodyFatPercentageFormula($this);
	}

	public function getBodyFatWeight()
	{
		if (!$this->getWeight()) {
			throw (new CaloricCalculatorException("Missing weight."))
				->setAbbr('missingWeight')
				;
		}

		return new Weight($this->getWeight()->getInKg()->getAmount() * $this->getBodyFatPercentage()->getAmount());
	}

	public function getActiveBodyMassPercentage()
	{
		return new Percentage(1 - $this->getBodyFatPercentage()->getAmount());
	}

	public function getActiveBodyMassWeight()
	{
		$weight = $this->getWeight();
		if (!($weight instanceof Weight)) {
			throw (new CaloricCalculatorException("Missing weight."))
				->setAbbr('missingWeight')
				;
		}

		return new Weight($weight->getInKg()->getAmount() * $this->getActiveBodyMassPercentage()->getAmount());
	}

	public function getOptimalFatPercentage()
	{
		if (!($this->getBirthday() instanceof Birthday)) {
			throw (new CaloricCalculatorException("Invalid birthday."))
				->setAbbr('invalidBirthday')
				;
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

	public function getOptimalFatWeight()
	{
		$weight = $this->getWeight();
		if (!($weight instanceof Weight)) {
			throw (new CaloricCalculatorException("Missing weight."))
				->setAbbr('missingWeight')
				;
		}

		$optimalFatPercentage = $this->getOptimalFatPercentage();
		if (!($optimalFatPercentage instanceof Interval)) {
			throw (new CaloricCalculatorException("Missing weight."))
				->setAbbr('unableOptimalFatPercentage')
				;
		}

		return new Interval(new Weight($weight->getInKg()->getAmount() * $this->getOptimalFatPercentage()->getMin()->getAmount()), new Weight($weight->getInKg()->getAmount() * $this->getOptimalFatPercentage()->getMax()->getAmount()));
	}

	public function getOptimalWeight()
	{
		$activeBodyMassWeight = $this->getActiveBodyMassWeight();
		$optimalFatWeight = $this->getOptimalFatWeight();

		return new Interval(new Weight($activeBodyMassWeight->getInKg()->getAmount() + $optimalFatWeight->getMin()->getInKg()->getAmount()), new Weight($activeBodyMassWeight->getInKg()->getAmount() + $optimalFatWeight->getMax()->getInKg()->getAmount()));
	}

	public function getEssentialFatPercentage()
	{
		$gender = $this->getGender();

		if ($gender instanceof Genders\Male) {
			return new Percentage(.05);
		} elseif ($gender instanceof Genders\Female) {
			return new Percentage(.13);
		}
	}

	public function getEssentialFatWeight()
	{
		$weight = $this->getWeight();
		if (!($weight instanceof Weight)) {
			throw (new CaloricCalculatorException("Missing weight."))
				->setAbbr('missingWeight')
				;
		}

		$essentialFatPercentage = $this->getEssentialFatPercentage();
		if (!($essentialFatPercentage instanceof Percentage)) {
			throw (new CaloricCalculatorException("Nelze spočítat procento esenciálního tuku."))
				->setAbbr('unableEssentialFatPercentage')
				;
		}

		return new Weight($weight->getInKg()->getAmount() * $essentialFatPercentage->getAmount());
	}

	public function getFatWithinOptimalPercentage()
	{
		$bodyFatWeight = $this->getBodyFatWeight();
		$optimalFatWeight = $this->getOptimalFatWeight();

		$min = $optimalFatWeight->getMin()->getInKg()->getAmount() / $bodyFatWeight->getInKg()->getAmount();
		$max = $optimalFatWeight->getMax()->getInKg()->getAmount() / $bodyFatWeight->getInKg()->getAmount();

		return new Interval(new Percentage($min <= 1 ? $min : 1), new Percentage($max <= 1 ? $max : 1));
	}

	public function getFatWithinOptimalWeight()
	{
		$bodyFatWeight = $this->getBodyFatWeight();
		$optimalFatWeight = $this->getOptimalFatWeight();

		$min = $bodyFatWeight->getInKg()->getAmount() - $optimalFatWeight->getMin()->getInKg()->getAmount();
		$max = $bodyFatWeight->getInKg()->getAmount() - $optimalFatWeight->getMax()->getInKg()->getAmount();

		return new Interval(new Weight($bodyFatWeight->getInKg()->getAmount() - ($min >= 0 ? $min : 0)), new Weight($bodyFatWeight->getInKg()->getAmount() - ($max >= 0 ? $max : 0)));
	}

	public function getFatOverOptimalPercentage()
	{
		$bodyFatWeight = $this->getBodyFatWeight();
		$fatOverOptimalWeight = $this->getFatOverOptimalWeight();

		$min = $fatOverOptimalWeight->getMin()->getInKg()->getAmount() / $bodyFatWeight->getInKg()->getAmount();
		$max = $fatOverOptimalWeight->getMax()->getInKg()->getAmount() / $bodyFatWeight->getInKg()->getAmount();

		return new Interval(new Percentage($min), new Percentage($max));
	}

	public function getFatOverOptimalWeight()
	{
		$bodyFatWeight = $this->getBodyFatWeight();
		// print_r($bodyFatWeight);die;
		$optimalFatWeight = $this->getOptimalFatWeight();
		// print_r($optimalFatWeight);die;

		$min = $bodyFatWeight->getInKg()->getAmount() - $optimalFatWeight->getMin()->getInKg()->getAmount();
		$max = $bodyFatWeight->getInKg()->getAmount() - $optimalFatWeight->getMax()->getInKg()->getAmount();

		return new Interval(new Weight($min >= 0 ? $min : 0), new Weight($max >= 0 ? $max : 0));
	}

	public function getBodyFatDeviation()
	{
		$gender = $this->getGender();
		$bodyMassIndex = $this->getBodyMassIndex()->getAmount();
		$bodyMassIndexDeviation = $this->getBodyMassIndexDeviation();
		$isOverweight = (bool)$this->getFatOverOptimalWeight()->getMax()->getAmount();

		if ($gender instanceof Genders\Male && $bodyMassIndex >= .95 && !$isOverweight) {
			return 0;
		}

		return $bodyMassIndexDeviation;
	}

	/*****************************************************************************
	 * Beztuková tělesná hmotnost - FFM.
	 */
	public function getFatFreeMass()
	{
		if (!($this->getWeight() instanceof Weight)) {
			throw (new CaloricCalculatorException("Missing weight."))
				->setAbbr('missingWeight')
				;
		}

		return new Weight($this->getWeight()->getInKg()->getAmount() - ($this->getBodyFatPercentage()->getAsPercentage() * $this->getWeight()->getInKg()->getAmount()));
	}

	public function getFatFreeMassFormula()
	{
		$result = $this->getFatFreeMass()->getInKg()->getAmount();

		return 'weight[' . $this->getWeight()->getInKg()->getAmount() . '] - (bodyFatPercentage[' . $this->getBodyFatPercentage()->getAsPercentage() . '] * weight[' . $this->getWeight()->getInKg()->getAmount() . ']) = ' . $result;
	}

	/*****************************************************************************
	 * Bazální metabolismus - BMR.
	 */
	public function getBasalMetabolicRate()
	{
		if (!($this->getGender() instanceof Gender)) {
			throw (new CaloricCalculatorException("Missing gender."))
				->setAbbr('missingGender')
				;
		}

		return $this->getGender()->getBasalMetabolicRate($this);
	}

	public function getBasalMetabolicRateFormula()
	{
		$result = $this->getBasalMetabolicRate()->getAmount();

		return $this->getGender()->getBasalMetabolicRateFormula($this) . ' = ' . $result;
	}

	/*****************************************************************************
	 * Total Energy Expenditure - Termický efekt pohybu - TEE.
	 */
	public function getTotalEnergyExpenditure()
	{
		return new Energy($this->getBasalMetabolicRate()->getAmount() * $this->getPhysicalActivityLevel()->getAmount(), 'kCal');
	}

	public function getTotalEnergyExpenditureFormula()
	{
		$result = $this->getTotalEnergyExpenditure()->getAmount();

		return 'basalMetabolicRate[' . $this->getBasalMetabolicRate()->getAmount() . '] * physicalActivityLevel[' . $this->getPhysicalActivityLevel()->getAmount() . '] = ' . $result;
	}

	/*****************************************************************************
	 * Total Daily Energy Expenditure - Celkový doporučený denní příjem - TDEE.
	 */
	public function getTotalDailyEnergyExpenditure()
	{
		if (!($this->getGoal()->getTrend() instanceof Vector)) {
			throw (new CaloricCalculatorException("Missing goal trend."))
				->setAbbr('missingGoalTrend')
				;
		}

		return new Energy($this->getTotalEnergyExpenditure()->getAmount() * $this->getGoal()->getTrend()->getTdeeQuotient($this), 'kCal');
	}

	public function getTotalDailyEnergyExpenditureFormula()
	{
		$result = $this->getTotalDailyEnergyExpenditure()->getAmount();

		return 'totalEnergyExpenditure[' . $this->getTotalEnergyExpenditure()->getAmount() . '] * weightGoalQuotient[' . $this->getGoal()->getTrend()->getTdeeQuotient($this) . '] = ' . $result;
	}

	/*****************************************************************************
	 * Reference Daily Intake - Doporučený denní příjem - DDP.
	 */
	public function getReferenceDailyIntake()
	{
		$ec = new \Katu\Exceptions\ExceptionCollection;

		try {
			$totalDailyEnergyExpenditure = $this->getTotalDailyEnergyExpenditure();
		} catch (\Throwable $e) {
			$ec->add($e);
		}

		try {
			$gender = $this->getGender();
			if (!($gender instanceof Gender)) {
				throw (new FattyException("Missing gender."))
					->setAbbr('missingGender')
					;
			}

			$referenceDailyIntakeBonus = $gender->getReferenceDailyIntakeBonus();
		} catch (\Throwable $e) {
			$ec->add($e);
		}

		if ($ec->has()) {
			throw $ec;
		}

		if ($this->getDiet() instanceof Diets\Ned) {
			return new Energy(Diets\Ned::ENERGY_DEFAULT, 'kCal');
		} else {
			return new Energy($totalDailyEnergyExpenditure->getAmount() + $referenceDailyIntakeBonus->getAmount(), 'kCal');
		}
	}

	public function getReferenceDailyIntakeFormula()
	{
		$result = $this->getReferenceDailyIntake()->getAmount();

		if ($this->getDiet() instanceof Diets\Ned) {
			return $result;
		} else {
			return 'totalDailyEnergyExpenditure[' . $this->getTotalDailyEnergyExpenditure()->getAmount() . '] + referenceDailyIntakeBonus[' . $this->gender->getReferenceDailyIntakeBonus()->getAmount() . '] = ' . $result;
		}
	}

	/*****************************************************************************
	 * Body type - typ postavy.
	 */
	public function getBodyType()
	{
		$gender = $this->getGender();
		if (!($gender instanceof Gender)) {
			throw (new CaloricCalculatorException("Missing gender."))
				->setAbbr('missingGender')
				;
		}

		return $gender->getBodyType($this);
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
		if ($this->getSportDurations()->getTotalDuration() > 60 || $this->getPhysicalActivityLevel()->getAmount() >= 1.9) {
			// 13
			if ($this->getGender() instanceof Genders\Male) {
				// 14
				if ($this->getFatOverOptimalWeight()->getMax()->getInKg()->getAmount()) {
					$optimalWeight = $this->getOptimalWeight()->getMax();

				// 15
				} else {
					$optimalWeight = $this->getWeight();
				}

				$matrix = [
					'fit'   => [1.5, 2.2, 1.8],
					'unfit' => [1.5, 2,   1.7],
				];
				$matrixSet = ($this->getBodyFatPercentage()->getAmount() > .19 || $this->getBodyMassIndex()->getAmount() > 25) ? 'unfit' : 'fit';

				$optimalNutrients = [];
				foreach ($this->getSportDurations()->getMaxDurations() as $sportDuration) {
					if ($sportDuration instanceof SportDurations\LowFrequency) {
						$optimalNutrients[] = $optimalWeight->getAmount() * $matrix[$matrixSet][0];
					} elseif ($sportDuration instanceof SportDurations\Anaerobic) {
						$optimalNutrients[] = $optimalWeight->getAmount() * $matrix[$matrixSet][1];
					} elseif ($sportDuration instanceof SportDurations\Aerobic) {
						$optimalNutrients[] = $optimalWeight->getAmount() * $matrix[$matrixSet][2];
					}
				}

				if ($this->getPhysicalActivityLevel()->getAmount() >= 1.9) {
					$optimalNutrients[] = $optimalWeight->getAmount() * $matrix[$matrixSet][1];
				}

				$nutrients->setProteins(new Nutrients\Proteins(max($optimalNutrients), 'g'));

			// 12
			} elseif ($this->getGender() instanceof Genders\Female) {
				// 20
				if ($this->getGender()->isPregnant()) {
					// @TODO

				// 16
				} else {
					// 17
					if ($this->getFatOverOptimalWeight()->getMax()->getInKg()->getAmount()) {
						$optimalWeight = $this->getOptimalWeight()->getMax();

					// 18
					} else {
						$optimalWeight = $this->getWeight();
					}

					$matrix = [
						'fit'   => [1.4, 1.8, 1.6],
						'unfit' => [1.5, 1.8, 1.8],
					];
					$matrixSet = ($this->getBodyFatPercentage()->getAmount() > .25 || $this->getBodyMassIndex()->getAmount() > 25) ? 'unfit' : 'fit';

					$optimalNutrients = [];
					foreach ($this->getSportDurations()->getMaxDurations() as $sportDuration) {
						if ($sportDuration instanceof SportDurations\LowFrequency) {
							$optimalNutrients[] = $optimalWeight->getAmount() * $matrix[$matrixSet][0];
						} elseif ($sportDuration instanceof SportDurations\Anaerobic) {
							$optimalNutrients[] = $optimalWeight->getAmount() * $matrix[$matrixSet][1];
						} elseif ($sportDuration instanceof SportDurations\Aerobic) {
							$optimalNutrients[] = $optimalWeight->getAmount() * $matrix[$matrixSet][2];
						}
					}

					if ($this->getPhysicalActivityLevel()->getAmount() >= 1.9) {
						$optimalNutrients[] = $optimalWeight->getAmount() * $matrix[$matrixSet][1];
					}

					$nutrients->setProteins(new Nutrients\Proteins(max($optimalNutrients), 'g'));

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
				$nutrients->setProteins(new Nutrients\Proteins(min(($this->getWeight()->getInKg()->getAmount() * 1.4) + 20, 90), 'g'));

			// 4
			} else {
				// 5
				if ($this->getGender() instanceof Genders\Male) {
					// 7
					if ($this->getFatOverOptimalWeight()->getMax()->getInKg()->getAmount()) {
						$nutrients->setProteins(new Nutrients\Proteins($this->getOptimalWeight()->getMax()->getInKg()->getAmount() * 1.5, 'g'));

					// 8
					} else {
						$nutrients->setProteins(new Nutrients\Proteins($this->getWeight()->getInKg()->getAmount() * 1.5, 'g'));
					}
				// 6
				} elseif ($this->getGender() instanceof Genders\Female) {
					// 9
					if ($this->getFatOverOptimalWeight()->getMax()->getInKg()->getAmount()) {
						$nutrients->setProteins(new Nutrients\Proteins($this->getOptimalWeight()->getMax()->getInKg()->getAmount() * 1.4, 'g'));

					// 10
					} else {
						$nutrients->setProteins(new Nutrients\Proteins($this->getWeight()->getInKg()->getAmount() * 1.4, 'g'));
					}
				}
			}
		}

		/***************************************************************************
		 * Carbs and fats.
		 */
		$goalTdee = $this->getGoal()->getGoalTdee($this);
		if (!($this->getDiet() instanceof Diet)) {
			throw (new CaloricCalculatorException("Missing diet."))
				->setAbbr('missingDiet')
				;
		}

		// 1
		if ($this->getDiet() instanceof Diets\Standard) {
			// 4
			if ($this->getSportDurations()->getAnaerobic() instanceof SportDuration && $this->getSportDurations()->getAnaerobic()->getAmount() >= 100) {
				$nutrients->setCarbs(Nutrients\Carbs::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() * .58, 'kJ')));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() - $nutrients->getEnergy()->getInKJ()->getAmount())));
			// 5
			} elseif ($this->getGender() instanceof Genders\Female && ($this->getGender()->isPregnant() || $this->getGender()->isBreastfeeding())) {
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() * .35, 'kJ')));
				$nutrients->setCarbs(Nutrients\Carbs::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() - $nutrients->getEnergy()->getInKJ()->getAmount())));
			} else {
				$nutrients->setCarbs(Nutrients\Carbs::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() * .55, 'kJ')));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() - $nutrients->getEnergy()->getInKJ()->getAmount())));
			}

		// Mediterranean diet.
		} elseif ($this->getDiet() instanceof Diets\Standard) {
			$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() * .4, 'kJ')));
			$nutrients->setCarbs(Nutrients\Carbs::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() - $nutrients->getEnergy()->getInKJ()->getAmount())));

		// 2
		} elseif ($this->getDiet() instanceof Diets\LowCarb) {
			// 7
			if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
				$dietCarbs = $this->getDiet()->getCarbs();
				$nutrients->setCarbs(new Nutrients\Carbs($dietCarbs->getAmount(), $dietCarbs->getUnit()));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() - $nutrients->getEnergy()->getInKJ()->getAmount())));
				// @TODO - message
			// 8
			} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
				$dietCarbs = $this->getDiet()->getCarbs();
				$nutrients->setCarbs(new Nutrients\Carbs($dietCarbs->getAmount(), $dietCarbs->getUnit()));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() - $nutrients->getEnergy()->getInKJ()->getAmount())));
				// @TODO - message
			// 9
			} else {
				$dietCarbs = $this->getDiet()->getCarbs();
				$nutrients->setCarbs(new Nutrients\Carbs($dietCarbs->getAmount(), $dietCarbs->getUnit()));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() - $nutrients->getEnergy()->getInKJ()->getAmount())));
			}

		// 3
		} elseif ($this->getDiet() instanceof Diets\Keto) {
			// 7
			if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
				// @TODO - message
			// 8
			} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
				// @TODO - message
			// 9
			} else {
				$dietCarbs = $this->getDiet()->getCarbs();
				$nutrients->setCarbs(new Nutrients\Carbs($dietCarbs->getAmount(), $dietCarbs->getUnit()));
				$nutrients->setFats(Nutrients\Fats::createFromEnergy(new Energy($goalTdee->getInKJ()->getAmount() - $nutrients->getEnergy()->getInKJ()->getAmount())));
			}
		// NED diet.
		} elseif ($this->getDiet() instanceof Diets\Ned) {
			$nutrients->setCarbs(new Nutrients\Carbs(Diets\Ned::CARBS_DEFAULT, 'g'));
			$nutrients->setFats(new Nutrients\Fats(Diets\Ned::FATS_DEFAULT, 'g'));
			$nutrients->setProteins(new Nutrients\Proteins(Diets\Ned::PROTEINS_DEFAULT, 'g'));
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
		if ($this->getPhysicalActivityLevel()->getAmount() >= 2 && ($this->getSportDurations()->getAerobic()->getAmount() || $this->getSportDurations()->getAnaerobic()->getAmount())) {
			$messages[] = [
				'message' => \Katu\Config::get('caloricCalculator', 'messages', 'highSportPhysicalActivityLevel'),
				'fields' => ['sportDurations[aerobic]', 'sportDurations[anaerobic]'],
			];
		}

		return $messages;
	}

	public function getBodyMassIndexMessages()
	{
		$messages = [];

		$gender = $this->getGender();
		$bodyMassIndexAmount = $this->getBodyMassIndex()->getAmount();
		$bodyFatPercentageAmount = $this->getBodyFatPercentage()->getAmount();

		$bodyMassIndexAmount = 28;
		$gender = new Genders\Male;
		$bodyFatPercentageAmount = .18;

		if ($bodyMassIndexAmount <= 25) {
			if ($gender instanceof Genders\Male) {
				if ($bodyFatPercentageAmount < .19) {
					if ($bodyMassIndexAmount <= 18.5) {
						if ($bodyMassIndexAmount < 17) {
							$messages[]['message'] = "Těžká podvýživa, poruchy příjmu potravy!";
						} else {
							$messages[]['message'] = "Pozor, BMI není v normě, podváha!";
						}

						if ($bodyFatPercentageAmount <= .05) {
							$messages[]['message'] = "Pozor, množství esenciálního tuku u mužů je 3-5 %. Jsi na hraně!";
						}
					} else {
						$messages[]['message'] = "Super, BMI i podíl tělesného tuku je jak má být.";
					}
				} else {
					$messages[]['message'] = "BMI je v pořádku, ale máte více tělesného tuku, než by mělo být.";
				}
			} elseif ($gender instanceof Genders\Female) {
				if ($bodyFatPercentageAmount < .25) {
					if ($bodyMassIndexAmount <= 18.5) {
						if ($bodyMassIndexAmount < 17) {
							$messages[]['message'] = "Těžká podvýživa, poruchy příjmu potravy!";
						} else {
							$messages[]['message'] = "Pozor, BMI není v normě, podváha!";
						}

						if ($bodyFatPercentageAmount <= .13) {
							$messages[]['message'] = "Pozor, množství esenciálního tuku u žen je 11-13 %. Jsi na hraně!";
						}
					} else {
						$messages[]['message'] = "Super, BMI i podíl tělesného tuku je jak má být.";
					}
				} else {
					$messages[]['message'] = "BMI je v pořádku, ale máte více tělesného tuku, než by mělo být.";
				}
			}
		} else {
			if ($gender instanceof Genders\Male) {
				if ($bodyFatPercentageAmount < .19) {
					$messages[]['message'] = "BMI sice v normě není, ale vše v pořádku, ty asi hodně cvičíš, takže na to nekoukej.";
				} else {
					if ($bodyMassIndexAmount < 25) {
						$messages[]['message'] = "BMI je v pořádku, ale máte více tělesného tuku, než by mělo být.";
					} elseif ($bodyMassIndexAmount < 30) {
						$messages[]['message'] = "Pozor, máš nadváhu.";
					} elseif ($bodyMassIndexAmount < 35) {
						$messages[]['message'] = "Obezita 1. stupně, pozor, hrozí riziko vzniku chorob.";
					} elseif ($bodyMassIndexAmount < 40) {
						$messages[]['message'] = "Obezita 2. stupně, vysoké riziko vzniku chorob.";
					} else {
						$messages[]['message'] = "Obezita 3. stupně, morbidní obezita.";
					}
				}
			} elseif ($gender instanceof Genders\Female) {
				if ($bodyFatPercentageAmount < .25) {
					$messages[]['message'] = "BMI sice v normě není, ale vše v pořádku, ty asi hodně cvičíš, takže na to nekoukej.";
				} else {
					if ($bodyMassIndexAmount < 25) {
						$messages[]['message'] = "BMI je v pořádku, ale máte více tělesného tuku, než by mělo být.";
					} elseif ($bodyMassIndexAmount < 30) {
						$messages[]['message'] = "Pozor, máš nadváhu.";
					} elseif ($bodyMassIndexAmount < 35) {
						$messages[]['message'] = "Obezita 1. stupně, pozor, hrozí riziko vzniku chorob.";
					} elseif ($bodyMassIndexAmount < 40) {
						$messages[]['message'] = "Obezita 2. stupně, vysoké riziko vzniku chorob.";
					} else {
						$messages[]['message'] = "Obezita 3. stupně, morbidní obezita.";
					}
				}
			}
		}

		return $messages;
	}

	public function getGoalMessages()
	{
		$ec = new \Katu\Exceptions\ExceptionCollection;

		$messages = [];

		// Is pregnant.
		if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
			$messages[] = [
				'message' => \Katu\Config::get('caloricCalculator', 'messages', 'isPregnant'),
				'fields' => ['pregnancy[isPregnant]'],
			];
		}

		// Is breastfeeding.
		if ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
			$messages[] = [
				'message' => \Katu\Config::get('caloricCalculator', 'messages', 'isBreastfeeding'),
				'fields' => ['pregnancy[isBreastfeeding]'],
			];
		}

		// Is loosing weight.
		if ($this->getGoal()->getTrend() instanceof Vectors\Loose) {
			// Is loosing weight while pregnant.
			if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
				$messages[] = [
					'message' => \Katu\Config::get('caloricCalculator', 'messages', 'isPregnantAndLoosingWeight'),
					'fields' => ['pregnancy[isPregnant]', 'goalTrend', 'goalWeight'],
				];

			// Is loosing weight while breastfeeding.
			} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
				$messages[] = [
					'message' => \Katu\Config::get('caloricCalculator', 'messages', 'isBreastfeedingAndLoosingWeight'),
					'fields' => ['pregnancy[isBreastfeeding]', 'goalTrend', 'goalWeight'],
				];
			} else {
				if (!($this->getWeight() instanceof Weight)) {
					$ec->add(
						(new CaloricCalculatorException("Missing weight."))
							->setAbbr('missingWeight')
					);
				}

				if (!($this->getGoal()->getWeight() instanceof Weight)) {
					$ec->add(
						(new CaloricCalculatorException("Missing weight target."))
							->setAbbr('missingGoalWeight')
					);
				}

				// Is loosing realistic?
				if (!$ec->has()) {
					// Unrealistic loosing.
					if ($this->getGoal()->getDifference($this) > 0) {
						$realisticGoalWeight = $this->getGoal()->getFinal($this);

						$messages[] = [
							'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'loosingWeightUnrealistic'), [
								'%realisticGoalWeight%' => $realisticGoalWeight,
							]),
							'fields' => ['goalWeight'],
						];

					// Realistic loosing.
					} else {
						$weightChange = new Weight($this->getWeight()->getInKg()->getAmount() - $this->getGoal()->getWeight()->getInKg()->getAmount());

						$messages[] = [
							'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'loosingWeightRealistic'), [
								'%weightChange%' => $weightChange,
							]),
							'fields' => ['goalWeight'],
						];

						$slowLooseTdee = (new Vectors\SlowLoose)->getGoalTdee($this);
						$looseTdee = (new Vectors\Loose)->getGoalTdee($this);

						$messages[] = [
							'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'loosingWeightTdeeRecommendations'), [
								'%slowLooseTdee%' => $slowLooseTdee,
								'%looseTdee%' => $looseTdee,
							]),
							'fields' => ['goalWeight'],
						];
					}
				}
			}
		} elseif ($this->getGoal()->getTrend() instanceof Vectors\Gain) {
			if (!($this->getWeight() instanceof Weight)) {
				$ec->add(
					(new CaloricCalculatorException("Missing weight."))
						->setAbbr('missingWeight')
				);
			}

			if (!($this->getGoal()->getWeight() instanceof Weight)) {
				$ec->add(
					(new CaloricCalculatorException("Missing weight target."))
						->setAbbr('missingGoalWeight')
				);
			}

			// Is gaining realistic?
			if (!$ec->has()) {
				// Unrealistic gaining.
				if ($this->getGoal()->getDifference($this) > 0) {
					$realisticGoalWeight = $this->getGoal()->getFinal($this);

					$messages[] = [
						'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'gainingWeightUnrealistic'), [
							'%realisticGoalWeight%' => $realisticGoalWeight,
						]),
						'fields' => ['goalWeight'],
					];

				// Realistic gaining.
				} else {
					$weightChange = new Weight($this->getGoal()->getWeight()->getInKg()->getAmount() - $this->getWeight()->getInKg()->getAmount());

					$messages[] = [
						'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'gainingWeightRealistic'), [
							'%weightChange%' => $weightChange,
						]),
						'fields' => ['goalWeight'],
					];

					$slowGainTdee = (new Vectors\SlowGain)->getGoalTdee($this);
					$gainTdee = (new Vectors\Gain)->getGoalTdee($this);

					$messages[] = [
						'message' => strtr(\Katu\Config::get('caloricCalculator', 'messages', 'gainingWeightTdeeRecommendations'), [
							'%slowGainTdee%' => $slowGainTdee,
							'%gainTdee%' => $gainTdee,
						]),
						'fields' => ['goalWeight'],
					];
				}

				$messages[] = [
					'message' => \Katu\Config::get('caloricCalculator', 'messages', 'gainingRecommendations'),
					'fields' => ['goalWeight'],
				];
			}
		}

		if ($ec->has()) {
			throw $ec;
		}

		return $messages;
	}

	public function getGoalNutrientMessages()
	{
		$messages = [];

		if ($this->getDiet() instanceof Diets\LowCarb) {
			// 7
			if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
				$messages[] = [
					'message' => \Katu\Config::get('caloricCalculator', 'messages', 'lowCarbButPregnant'),
					'fields' => ['diet'],
				];

			// 8
			} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
				$messages[] = [
					'message' => \Katu\Config::get('caloricCalculator', 'messages', 'lowCarbButBreastfeeding'),
					'fields' => ['diet'],
				];
			}

		// 3
		} elseif ($this->getDiet() instanceof Diets\Keto) {
			// 7
			if ($this->getGender() instanceof Genders\Female && $this->getGender()->isPregnant()) {
				$messages[] = [
					'message' => \Katu\Config::get('caloricCalculator', 'messages', 'ketoButPregnant'),
					'fields' => ['diet'],
				];

			// 8
			} elseif ($this->getGender() instanceof Genders\Female && $this->getGender()->isBreastfeeding()) {
				$messages[] = [
					'message' => \Katu\Config::get('caloricCalculator', 'messages', 'ketoButBreastfeeding'),
					'fields' => ['diet'],
				];
			}
		}

		return $messages;
	}

	public function getMessages()
	{
		$ec = new \Katu\Exceptions\ExceptionCollection;

		$messages = [];

		try {
			$messages = array_merge($messages, $this->getBodyFatMessages());
		} catch (\Throwable $e) {
			$ec->add($e);
		}

		try {
			$messages = array_merge($messages, $this->getBodyMassIndexMessages());
		} catch (\Throwable $e) {
			$ec->add($e);
		}

		try {
			$messages = array_merge($messages, $this->getGoalMessages());
		} catch (\Throwable $e) {
			$ec->add($e);
		}

		try {
			$messages = array_merge($messages, $this->getGoalNutrientMessages());
		} catch (\Throwable $e) {
			$ec->add($e);
		}

		if ($ec->has()) {
			throw $ec;
		}

		return $messages;
	}

	public static function getDeviation($value, $ideal, $extremes)
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

			return $res;
		} catch (\Throwable $e) {
			return 0;
		}
	}
}
