<?php

namespace Fatty;

use Fatty\Exceptions\MissingGenderException;
use Fatty\Metrics\AmountMetric;
use Fatty\Metrics\QuantityMetric;
use Fatty\Metrics\StringMetric;
use Fatty\Strategies\Zivot20;
use Katu\Errors\Error;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;
use Katu\Tools\Validation\ValidationCollection;
use Psr\Http\Message\ServerRequestInterface;

class Calculator implements RestResponseInterface
{
	protected $activity;
	protected $birthday;
	protected $bodyFatPercentage;
	protected $diet;
	protected $gender;
	protected $goal;
	protected $params;
	protected $proportions;
	protected $referenceDate;
	protected $sportDurations;
	protected $strategy;
	protected $units = "kcal";
	protected $weight;
	protected $weightHistory;

	public static function createFromRequest(ServerRequestInterface $request): Validation
	{
		$validations = new ValidationCollection;
		$params = $request->getQueryParams();
		$calculator = new static;

		if (trim($params["gender"] ?? null)) {
			$genderValidation = Gender::validateGender(new Param("gender", $params["gender"]));
			$validations[] = $genderValidation;

			if (!$genderValidation->hasErrors()) {
				$calculator->setGender($genderValidation->getResponse());
			}
		}

		if (trim($params["birthday"] ?? null)) {
			$birthdayValidation = Birthday::validateBirthday(new Param("birthday", $params["birthday"]));
			$validations[] = $birthdayValidation;

			if (!$birthdayValidation->hasErrors()) {
				$calculator->setBirthday($birthdayValidation->getResponse());
			}
		}

		if (trim($params["weight"] ?? null)) {
			$weightValidation = Weight::validateWeight(new Param("weight", $params["weight"]));
			$validations[] = $weightValidation;

			if (!$weightValidation->hasErrors()) {
				$calculator->setWeight($weightValidation->getResponse());
			}
		}

		if (trim($params["proportions_height"] ?? null)) {
			$heightValidation = Proportions::validateHeight(new Param("proportions_height", $params["proportions_height"]));
			$validations[] = $heightValidation;

			if (!$heightValidation->hasErrors()) {
				$calculator->getProportions()->setHeight($heightValidation->getResponse());
			}
		}

		if (trim($params["proportions_waist"] ?? null)) {
			$waistValidation = Proportions::validateWaist(new Param("proportions_waist", $params["proportions_waist"]));
			$validations[] = $waistValidation;

			if (!$waistValidation->hasErrors()) {
				$calculator->getProportions()->setWaist($waistValidation->getResponse());
			}
		}

		if (trim($params["proportions_hips"] ?? null)) {
			$hipsValidation = Proportions::validateHips(new Param("proportions_hips", $params["proportions_hips"]));
			$validations[] = $hipsValidation;

			if (!$hipsValidation->hasErrors()) {
				$calculator->getProportions()->setHips($hipsValidation->getResponse());
			}
		}

		if (trim($params["proportions_neck"] ?? null)) {
			$neckValidation = Proportions::validateNeck(new Param("proportions_neck", $params["proportions_neck"]));
			$validations[] = $neckValidation;

			if (!$neckValidation->hasErrors()) {
				$calculator->getProportions()->setNeck($neckValidation->getResponse());
			}
		}

		if (trim($params["bodyFatPercentage"] ?? null)) {
			$bodyFatPercentageValidation = static::validateBodyFatPercentage(new Param("bodyFatPercentage", $params["bodyFatPercentage"]));
			$validations[] = $bodyFatPercentageValidation;

			if (!$bodyFatPercentageValidation->hasErrors()) {
				$calculator->setBodyFatPercentage($bodyFatPercentageValidation->getResponse());
			}
		}

		if (trim($params["activity"] ?? null)) {
			$activityValidation = Activity::validateActivity(new Param("activity", $params["activity"]));
			$validations[] = $activityValidation;

			if (!$activityValidation->hasErrors()) {
				$calculator->setActivity($activityValidation->getResponse());
			}
		}

		if (trim($params["sportDurations_lowFrequency"] ?? null)) {
			$lowFrequencyValidation = SportDurations::validateLowFrequency(new Param("sportDurations_lowFrequency", $params["sportDurations_lowFrequency"]));
			$validations[] = $lowFrequencyValidation;

			if (!$lowFrequencyValidation->hasErrors()) {
				$calculator->getSportDurations()->setLowFrequency($lowFrequencyValidation->getResponse());
			}
		}

		if (trim($params["sportDurations_aerobic"] ?? null)) {
			$aerobicValidation = SportDurations::validateAerobic(new Param("sportDurations_aerobic", $params["sportDurations_aerobic"]));
			$validations[] = $aerobicValidation;

			if (!$aerobicValidation->hasErrors()) {
				$calculator->getSportDurations()->setAerobic($aerobicValidation->getResponse());
			}
		}

		if (trim($params["sportDurations_anaerobic"] ?? null)) {
			$anaerobicValidation = SportDurations::validateAnaerobic(new Param("sportDurations_anaerobic", $params["sportDurations_anaerobic"]));
			$validations[] = $anaerobicValidation;

			if (!$anaerobicValidation->hasErrors()) {
				$calculator->getSportDurations()->setAnaerobic($anaerobicValidation->getResponse());
			}
		}

		if (trim($params["goal_vector"] ?? null)) {
			$goalVectorValidation = Goal::validateVector(new Param("goal_vector", $params["goal_vector"]));
			$validations[] = $goalVectorValidation;

			if (!$goalVectorValidation->hasErrors()) {
				$calculator->getGoal()->setVector($goalVectorValidation->getResponse());
			}
		}

		$calculator->getGoal()->setDuration(new Duration(new Amount(12), "weeks"));

		try {
			$goalWeightString = trim($params["goal_weight"] ?? null);
		} catch (\Throwable $e) {
			try {
				$goalWeightString = trim($params["goal_weight_{$params["goal_vector"]}"] ?? null);
			} catch (\Throwable $e) {
				$goalWeightString = null;
			}
		} catch (\Throwable $e) {
			$goalWeightString = null;
		}

		if ($goalWeightString) {
			$goalWeightValidation = Goal::validateWeight(new Param("goal_weight", $goalWeightString));
			$validations[] = $goalWeightValidation;

			if (!$goalWeightValidation->hasErrors()) {
				$calculator->getGoal()->setWeight($goalWeightValidation->getResponse());
			}
		}

		if (trim($params["diet_approach"] ?? null)) {
			$dietApproachValidation = Diet::validateApproach(new Param("diet_approach", $params["diet_approach"]));
			$validations[] = $dietApproachValidation;

			if (!$dietApproachValidation->hasErrors()) {
				$calculator->getDiet()->setApproach($dietApproachValidation->getResponse());
			}
		}

		try {
			$dietCarbsString = trim($params["diet_carbs"] ?? null);
		} catch (\Throwable $e) {
			try {
				$dietCarbsString = trim($params["diet_carbs_{$params["diet_approach"]}"]);
			} catch (\Throwable $e) {
				$dietCarbsString = null;
			}
		} catch (\Throwable $e) {
			$dietCarbsString = null;
		}

		if ($dietCarbsString) {
			$dietCarbsValidation = Diet::validateCarbs(new Param("diet_carbs", $dietCarbsString));
			$validations[] = $dietCarbsValidation;

			if (!$dietCarbsValidation->hasErrors()) {
				$calculator->getDiet()->setCarbs($dietCarbsValidation->getResponse());
			}
		}

		if (trim($params["units"] ?? null)) {
			$unitsValidation = static::validateUnits(new Param("units", $params["units"]));
			$validations[] = $unitsValidation;

			if (!$unitsValidation->hasErrors()) {
				$calculator->setUnits($unitsValidation->getResponse());
			}
		}

		$validation = $validations->getMerged();
		if ($validation->hasErrors()) {
			return $validation;
		}

		return $validation->setResponse($calculator);
	}

	public function setParams(array $params): Calculator
	{
		$this->params = $params;

		return $this;
	}

	public function getParams(): array
	{
		return $this->params;
	}

	public static function getDeviation(float $value, float $ideal, array $extremes): Amount
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
		} catch (\Fatty\Exceptions\FattyException $e) {
			return new Amount;
		} catch (\Throwable $e) {
			return new Amount;
		}
	}

	public function setStrategy(Strategy $strategy): Calculator
	{
		$this->strategy = $strategy;

		return $this;
	}

	public function getStrategy(): Strategy
	{
		return $this->strategy ?: new Zivot20;
	}

	/****************************************************************************
	 * Reference date.
	 */
	public function setReferenceDate(?Time $value): Calculator
	{
		$this->referenceDate = $value;

		return $this;
	}

	public function getReferenceDate(): Time
	{
		return $this->referenceDate ?: new Time;
	}

	/*****************************************************************************
	 * Units.
	 */
	public static function validateUnits(Param $units): Validation
	{
		$output = trim($units);
		if (!in_array($output, ["kJ", "kcal"])) {
			return (new Validation)->addError((new Error("Invalid units."))->addParam($units));
		} else {
			return (new Validation)->setResponse($output)->addParam($units->setOutput($output));
		}
	}

	public function setUnits(string $value): Calculator
	{
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
		$weight = $this->getWeight();
		if (!$weight) {
			throw new \Fatty\Exceptions\MissingWeightException;
		}

		$weightValue = $weight->getAmount()->getValue();

		$formula = "weight[{$weightValue}] = {$weightValue}";

		return new QuantityMetric("weight", $weight, $formula);
	}

	public function setWeightHistory(?WeightHistory $value): Calculator
	{
		$this->weightHistory = $value;

		return $this;
	}

	public function getWeightHistory(): WeightHistory
	{
		return $this->weightHistory ?: new WeightHistory;
	}

	/*****************************************************************************
	 * Proportions.
	 */
	public function setProportions(Proportions $proportions): Calculator
	{
		$this->proportions = $proportions;

		return $this;
	}

	public function getProportions(): Proportions
	{
		$this->proportions = $this->proportions instanceof Proportions ? $this->proportions : new Proportions;

		return $this->proportions;
	}

	/*****************************************************************************
	 * Body fat percentage.
	 */
	public static function validateBodyFatPercentage(Param $bodyFatPercentage): Validation
	{
		$output = Percentage::createFromPercent($bodyFatPercentage);
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid body fat percentage."))->addParam($bodyFatPercentage));
		} else {
			return (new Validation)->setResponse($output)->addParam($bodyFatPercentage->setOutput($output));
		}
	}

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
			throw new \Fatty\Exceptions\MissingGenderException;
		}

		return $this->getGender()->calcBodyFatPercentage($this);
	}

	/*****************************************************************************
	 * Body type - typ postavy.
	 */
	public function calcBodyType(Calculator $calculator): StringMetric
	{
		$gender = $this->getGender();
		if (!$gender) {
			throw new \Fatty\Exceptions\MissingGenderException;
		}

		return $gender->calcBodyType($this);
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

	public function calcActivity(): AmountMetric
	{
		$activity = $this->activity;
		if (!$activity) {
			throw new \Fatty\Exceptions\MissingActivityException;
		}

		return new AmountMetric("activity", $activity);
	}

	public function getSportDurations(): SportDurations
	{
		$this->sportDurations = $this->sportDurations instanceof SportDurations ? $this->sportDurations : new SportDurations;

		return $this->sportDurations;
	}

	public function calcSportActivity(): AmountMetric
	{
		return $this->getSportDurations()->calcSportActivity();
	}

	public function getSportProteinMatrix(): array
	{
		$gender = $this->getGender();
		if (!$gender) {
			throw new \Fatty\Exceptions\MissingGenderException;
		}

		return $gender->getSportProteinMatrix();
	}

	public function calcSportProteinCoefficientKey(): StringMetric
	{
		try {
			$value = $this->getSportDurations()->getMaxProteinSportDuration()->getSportProteinCoefficientKey();
		} catch (\Throwable $e) {
			$value = null;
		}

		return new StringMetric("sportProteinCoefficientKey", (string)$value);
	}

	public function calcSportProteinCoefficient(): AmountMetric
	{
		$maxSportDuration = $this->getSportDurations()->getMaxProteinSportDuration();

		// Velká fyzická zátěž.
		if (($maxSportDuration && $maxSportDuration->getAmount()->getValue() >= 60) || $this->calcPhysicalActivityLevel()->getResult()->getValue() >= 1.9) {
			$fitnessLevel = $this->calcFitnessLevel()->getResult();
			$sportProteinMatrix = $this->getSportProteinMatrix();
			$sportProteinCoefficientKey = $this->calcSportProteinCoefficientKey()->getResult() ?: \Fatty\SportDurations\Anaerobic::getCode();
			$proteinCoefficient = $sportProteinMatrix[$fitnessLevel][$sportProteinCoefficientKey];
		// Normální fyzická zátěž.
		} else {
			if (!$this->getGender()) {
				throw new \Fatty\Exceptions\MissingGenderException;
			}

			$proteinCoefficient = $this->getGender()->getSportProteinCoefficient();
		}

		return new AmountMetric("sportProteinCoefficient", new Amount($proteinCoefficient));
	}

	/*****************************************************************************
	 * Physical activity level.
	 */
	public function calcPhysicalActivityLevel(): AmountMetric
	{
		$exceptions = new \Fatty\Exceptions\FattyExceptionCollection;

		try {
			$activity = $this->calcActivity();
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$sportActivity = $this->calcSportActivity();
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		if ($exceptions->hasExceptions()) {
			throw $exceptions;
		}

		$activityValue = $activity->getResult()->getValue();
		$sportActivityValue = $sportActivity->getResult()->getValue();

		$result = new Activity($activityValue + $sportActivityValue);
		$formula = "activityPal[{$activityValue}] + sportPal[{$sportActivityValue}] = {$result->getValue()}";

		return new AmountMetric("physicalActivityLevel", $result, $formula);
	}

	/*****************************************************************************
	 * Goal.
	 */
	public function setGoal(Goal $goal): Calculator
	{
		$this->goal = $goal;

		return $this;
	}

	public function getGoal(): Goal
	{
		$this->goal = $this->goal instanceof Goal ? $this->goal : new Goal;

		return $this->goal;
	}

	/*****************************************************************************
	 * Diet.
	 */
	public function setDiet(Diet $diet): Calculator
	{
		$this->diet = $diet;

		return $this;
	}

	public function getDiet(): Diet
	{
		$this->diet = $this->diet instanceof Diet ? $this->diet : new Diet;

		return $this->diet;
	}

	/*****************************************************************************
	 * Body mass index - BMI.
	 */
	public function getIsOverweight(): bool
	{
		return (bool)$this->calcFatOverOptimalWeight()->filterByName("fatOverOptimalWeightMax")[0]->getResult()->getAmount()->getValue();
	}

	public function calcBodyMassIndex(): AmountMetric
	{
		$exceptions = new \Fatty\Exceptions\FattyExceptionCollection;

		if (!($this->getWeight() instanceof Weight)) {
			$exceptions->addException(new \Fatty\Exceptions\MissingWeightException);
		}

		if (!($this->getProportions()->getHeight() instanceof Length)) {
			$exceptions->addException(new \Fatty\Exceptions\MissingHeightException);
		}

		if ($exceptions->hasExceptions()) {
			throw $exceptions;
		}

		$weight = $this->getWeight()->getInUnit("kg")->getAmount()->getValue();
		$height = $this->getProportions()->getHeight()->getInUnit("m")->getAmount()->getValue();

		$resultValue = $weight / pow($height, 2);
		$result = new Amount($resultValue);
		$formula = "
			weight[{$weight}] / pow(height[{$height}], 2)
			= {$weight} / " . (pow($height, 2)) . "
			= {$resultValue}
		";

		return new AmountMetric("bodyMassIndex", $result, $formula);
	}

	public function calcBodyMassIndexDeviation(): AmountMetric
	{
		$result = static::getDeviation($this->calcBodyMassIndex()->getResult()->getValue(), 22, [17.7, 40]);

		return new AmountMetric("bodyMassIndexDeviation", $result);
	}

	/*****************************************************************************
	 * Waist-hip ratio - WHR.
	 */
	public function calcWaistHipRatio(): AmountMetric
	{
		$exceptions = new \Fatty\Exceptions\FattyExceptionCollection;

		if (!($this->getProportions()->getWaist() instanceof Length)) {
			$exceptions->addException(new \Fatty\Exceptions\MissingWaistException);
		}

		if (!($this->getProportions()->getHips() instanceof Length)) {
			$exceptions->addException(new \Fatty\Exceptions\MissingHipsException);
		}

		if ($exceptions->hasExceptions()) {
			throw $exceptions;
		}

		$waist = $this->getProportions()->getWaist()->getInUnit("cm")->getAmount()->getValue();
		$hips = $this->getProportions()->getHips()->getInUnit("cm")->getAmount()->getValue();

		$result = new Amount($waist / $hips);
		$formula = "waist[{$waist}] / hips[{$hips}] = {$result->getValue()}";

		return new AmountMetric("waistHipRatio", $result, $formula);
	}

	public function calcWaistHipRatioDeviation(): AmountMetric
	{
		$gender = $this->getGender();
		if (!$gender) {
			throw new \Fatty\Exceptions\MissingGenderException;
		}

		$waistHipRatioValue = $this->calcWaistHipRatio()->getResult()->getValue();

		if ($gender instanceof Genders\Male) {
			$result = new Amount(static::getDeviation($waistHipRatioValue, .8, [.8, .95])->getValue() - 1);
		} elseif ($gender instanceof Genders\Female) {
			$result = new Amount(static::getDeviation($waistHipRatioValue, .9, [.9, 1])->getValue() - 1);
		}

		return new AmountMetric("waistHipRatioDeviation", $result);
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
			"A" => [1 => -1, -.5,   0,   0,   0,   0,   0],
			"B" => [1 =>  1,  .5,  .5,  .5,   1,   1,   1],
			"C" => [1 =>  1,   1,  .5,  .5,  .5,  .5,  .5],
			"D" => [1 =>  1,   1,  .5,  .5,   1,   1,   1],
			"E" => [1 =>  1,   1,  .5,   1,   1,   1,   1],
		];

		return new AmountMetric("riskDeviation", new Amount($matrix[$column][$row]));
	}

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	public function calcBodyFatWeight(): QuantityMetric
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new \Fatty\Exceptions\MissingWeightException;
		}

		$gender = $this->getGender();
		if (!$gender) {
			throw new \Fatty\Exceptions\MissingGenderException;
		}

		$weightValue = $weight->getInUnit("kg")->getAmount()->getValue();
		$bodyFatPercentageValue = $gender->calcBodyFatPercentage($this)->getResult()->getValue();

		$result = new Weight(
			new Amount($weightValue * $bodyFatPercentageValue),
			"kg",
		);

		$formula = "
			weight[{$weightValue}] * bodyFatPercentageValue[{$bodyFatPercentageValue}]
			= {$result->getAmount()->getValue()} kg
		";

		return new QuantityMetric("bodyFatWeight", $result, $formula);
	}

	public function calcActiveBodyMassPercentage(): AmountMetric
	{
		$bodyFatPercentageValue = $this->calcBodyFatPercentage($this)->getResult()->getValue();

		$result = new Percentage(1 - $bodyFatPercentageValue);
		$formula = "1 - bodyFatPercentage[$bodyFatPercentageValue] = {$result->getValue()}";

		return new AmountMetric("activeBodyMassPercentage", $result, $formula);
	}

	public function calcActiveBodyMassWeight(): QuantityMetric
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new \Fatty\Exceptions\MissingWeightException;
		}

		$result = new Weight(
			new Amount(
				$weight->getInUnit("kg")->getAmount()->getValue() * $this->calcActiveBodyMassPercentage()->getResult()->getValue()
			),
			"kg",
		);

		return new QuantityMetric("activeBodyMassWeight", $result);
	}

	public function calcOptimalFatPercentage(): MetricCollection
	{
		$gender = $this->getGender();
		if (!$gender) {
			throw new \Fatty\Exceptions\MissingGenderException;
		}

		if (!$this->getBirthday()) {
			throw new \Fatty\Exceptions\MissingBirthdayException;
		}
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
			new AmountMetric("optimalFatPercentageMin", $result->getMin()),
			new AmountMetric("optimalFatPercentageMax", $result->getMax()),
		]);
	}

	public function calcOptimalFatWeight(): MetricCollection
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new \Fatty\Exceptions\MissingWeightException;
		}

		$optimalFatPercentage = $this->calcOptimalFatPercentage();
		if (!$optimalFatPercentage) {
			throw new \Fatty\Exceptions\UnableToCalcOptimalFatPercentageException;
		}

		return new MetricCollection([
			new QuantityMetric(
				"optimalFatWeightMin",
				new Weight(
					new Amount(
						$weight->getInUnit("kg")->getAmount()->getValue() * $this->calcOptimalFatPercentage()->filterByName("optimalFatPercentageMin")[0]->getResult()->getValue()
					),
					"kg",
				)
			),
			new QuantityMetric(
				"optimalFatWeightMax",
				new Weight(
					new Amount(
						$weight->getInUnit("kg")->getAmount()->getValue() * $this->calcOptimalFatPercentage()->filterByName("optimalFatPercentageMax")[0]->getResult()->getValue()
					),
					"kg",
				)
			),
		]);
	}

	public function getOptimalWeight(): Interval
	{
		$activeBodyMassWeightValue = $this->calcActiveBodyMassWeight()->getResult()->getInUnit("kg")->getAmount()->getValue();
		$optimalFatWeight = $this->calcOptimalFatWeight();
		$optimalFatWeightMinValue = $optimalFatWeight->filterByName("optimalFatWeightMin")[0]->getResult()->getInUnit("kg")->getAmount()->getValue();
		$optimalFatWeightMaxValue = $optimalFatWeight->filterByName("optimalFatWeightMax")[0]->getResult()->getInUnit("kg")->getAmount()->getValue();

		return new Interval(
			new Weight(
				new Amount($activeBodyMassWeightValue + $optimalFatWeightMinValue),
				"kg",
			),
			new Weight(
				new Amount($activeBodyMassWeightValue + $optimalFatWeightMaxValue),
				"kg",
			),
		);
	}

	public function calcEssentialFatPercentage(): AmountMetric
	{
		$gender = $this->getGender();
		if (!$gender) {
			throw new \Fatty\Exceptions\MissingGenderException;
		}

		return $this->getGender()->calcEssentialFatPercentage();
	}

	public function calcEssentialFatWeight(): QuantityMetric
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new \Fatty\Exceptions\MissingWeightException;
		}

		$essentialFatPercentage = $this->calcEssentialFatPercentage();
		if (!$essentialFatPercentage) {
			throw new \Fatty\Exceptions\UnableToCalcEssentialFatPercentageException;
		}

		return new QuantityMetric(
			"essentialFatWeight",
			new Weight(
				new Amount(
					$weight->getInUnit("kg")->getAmount()->getValue() * $essentialFatPercentage->getResult()->getValue()
				),
				"kg",
			)
		);
	}

	public function calcFatWithinOptimalPercentage(): MetricCollection
	{
		$optimalFatWeight = $this->calcOptimalFatWeight();
		$bodyFatWeight = $this->calcBodyFatWeight();

		$min = $optimalFatWeight->filterByName("optimalFatWeightMin")[0]->getResult()->getInUnit("kg")->getAmount()->getValue() / $bodyFatWeight->getResult()->getInUnit("kg")->getAmount()->getValue();
		$max = $optimalFatWeight->filterByName("optimalFatWeightMax")[0]->getResult()->getInUnit("kg")->getAmount()->getValue() / $bodyFatWeight->getResult()->getInUnit("kg")->getAmount()->getValue();

		return new MetricCollection([
			new AmountMetric("fatWithinOptimalPercentageMin", new Percentage($min <= 1 ? $min : 1)),
			new AmountMetric("fatWithinOptimalPercentageMax", new Percentage($max <= 1 ? $max : 1)),
		]);
	}

	public function calcFatWithinOptimalWeight(): MetricCollection
	{
		$bodyFatWeight = $this->calcBodyFatWeight();
		$optimalFatWeight = $this->calcOptimalFatWeight();

		$min = $bodyFatWeight->getResult()->getInUnit("kg")->getAmount()->getValue() - $optimalFatWeight->filterByName("optimalFatWeightMin")[0]->getResult()->getInUnit("kg")->getAmount()->getValue();
		$max = $bodyFatWeight->getResult()->getInUnit("kg")->getAmount()->getValue() - $optimalFatWeight->filterByName("optimalFatWeightMax")[0]->getResult()->getInUnit("kg")->getAmount()->getValue();

		return new MetricCollection([
			new QuantityMetric("fatWithinOptimalWeightMin", new Weight(
				new Amount(
					$bodyFatWeight->getResult()->getInUnit("kg")->getAmount()->getValue() - ($min >= 0 ? $min : 0)
				),
				"kg",
			)),
			new QuantityMetric("fatWithinOptimalWeightMax", new Weight(
				new Amount(
					$bodyFatWeight->getResult()->getInUnit("kg")->getAmount()->getValue() - ($max >= 0 ? $max : 0)
				),
				"kg",
			)),
		]);
	}

	public function calcFatOverOptimalWeight(): MetricCollection
	{
		$bodyFatWeight = $this->calcBodyFatWeight();
		$optimalFatWeight = $this->calcOptimalFatWeight();

		$min = $bodyFatWeight->getResult()->getInUnit("kg")->getAmount()->getValue() - $optimalFatWeight->filterByName("optimalFatWeightMin")[0]->getResult()->getInUnit("kg")->getAmount()->getValue();
		$max = $bodyFatWeight->getResult()->getInUnit("kg")->getAmount()->getValue() - $optimalFatWeight->filterByName("optimalFatWeightMax")[0]->getResult()->getInUnit("kg")->getAmount()->getValue();

		return new MetricCollection([
			new QuantityMetric("fatOverOptimalWeightMin", new Weight(
				new Amount(
					$min >= 0 ? $min : 0
				),
				"kg",
			)),
			new QuantityMetric("fatOverOptimalWeightMax", new Weight(
				new Amount(
					$max >= 0 ? $max : 0
				),
				"kg",
			)),
		]);
	}

	public function calcMaxOptimalWeight(): QuantityMetric
	{
		$gender = $this->getGender();
		if (!$gender) {
			throw new \Fatty\Exceptions\MissingGenderException;
		}

		return $gender->calcMaxOptimalWeight($this);
	}

	public function calcFatOverOptimalPercentage(): MetricCollection
	{
		$fatOverOptimalWeight = $this->calcFatOverOptimalWeight();
		$bodyFatWeight = $this->calcBodyFatWeight();

		$min = $fatOverOptimalWeight->filterByName("fatOverOptimalWeightMin")[0]->getResult()->getInUnit("kg")->getAmount()->getValue() / $bodyFatWeight->getResult()->getInUnit("kg")->getAmount()->getValue();
		$max = $fatOverOptimalWeight->filterByName("fatOverOptimalWeightMax")[0]->getResult()->getInUnit("kg")->getAmount()->getValue() / $bodyFatWeight->getResult()->getInUnit("kg")->getAmount()->getValue();

		return new MetricCollection([
			new AmountMetric("fatOverOptimalPercentageMin", new Percentage($min)),
			new AmountMetric("fatOverOptimalPercentageMax", new Percentage($max)),
		]);
	}

	public function calcBodyFatDeviation(): AmountMetric
	{
		$gender = $this->getGender();
		$bodyMassIndex = $this->calcBodyMassIndex();
		$bodyMassIndexDeviation = $this->calcBodyMassIndexDeviation();
		$isOverweight = $this->getIsOverweight();

		if ($gender instanceof Genders\Male && $bodyMassIndex->getResult()->getValue() >= .95 && !$isOverweight) {
			return new AmountMetric("bodyFatDeviation", new Amount);
		}

		$result = $bodyMassIndexDeviation->getResult();

		return new AmountMetric("bodyFatDeviation", $result);
	}

	public function calcFitnessLevel(): StringMetric
	{
		if (!$this->getGender()) {
			throw new \Fatty\Exceptions\MissingGenderException;
		}

		return $this->getGender()->getFitnessLevel($this);
	}

	/*****************************************************************************
	 * Beztuková tělesná hmotnost - FFM.
	 */
	public function calcFatFreeMass(): QuantityMetric
	{
		$weight = $this->getWeight();
		if (!$weight) {
			throw new \Fatty\Exceptions\MissingWeightException;
		}

		$weightValue = $weight->getInUnit("kg")->getAmount()->getValue();
		$bodyFatPercentageValue = $this->calcBodyFatPercentage()->getResult()->getValue();

		$resultValue = $weightValue - ($bodyFatPercentageValue * $weightValue);
		$result = new Weight(
			new Amount($resultValue),
			"kg",
		);

		$formula = "
			weight[" . $weightValue . "] - (bodyFatPercentage[" . $bodyFatPercentageValue . "] * weight[" . $weightValue . "])
			= $weightValue - " . ($bodyFatPercentageValue * $weightValue) . "
			= {$resultValue} kg
		";

		return new QuantityMetric("fatFreeMass", $result, $formula);
	}

	/*****************************************************************************
	 * Bazální metabolismus - BMR.
	 */
	public function calcBasalMetabolicRate(): QuantityMetric
	{
		if (!$this->getGender()) {
			throw new MissingGenderException;
		}

		return $this->getGender()->calcBasalMetabolicRate($this);
	}

	/*****************************************************************************
	 * Total (Daily) Energy Expenditure - Termický efekt pohybu - TEE.
	 */
	public function calcTotalDailyEnergyExpenditure(): QuantityMetric
	{
		$basalMetabolicRate = $this->calcBasalMetabolicRate()->getResult()->getInUnit("kcal");
		$basalMetabolicRateValue = $basalMetabolicRate->getAmount()->getValue();
		$physicalActivityLevel = $this->calcPhysicalActivityLevel()->getResult()->getValue();

		$resultValue = $basalMetabolicRateValue * $physicalActivityLevel;
		$result = (new Energy(
			new Amount($resultValue),
			"kcal",
		))->getInUnit($this->getUnits());

		$formula = "
			basalMetabolicRate[" . $basalMetabolicRate . "] * physicalActivityLevel[" . $physicalActivityLevel . "]
			= {$result->getInUnit("kcal")->getAmount()->getValue()} kcal
			= {$result->getInUnit("kJ")->getAmount()->getValue()} kJ
		";

		return new QuantityMetric("totalDailyEnergyExpenditure", $result, $formula);
	}

	/*****************************************************************************
	 * Total Daily Energy Expenditure - Celkový doporučený denní příjem - TDEE.
	 */
	public function calcWeightGoalEnergyExpenditure(): QuantityMetric
	{
		return $this->getStrategy()->calcWeightGoalEnergyExpenditure($this);
	}

	/*****************************************************************************
	 * Reference Daily Intake - Doporučený denní příjem - DDP.
	 */
	public function calcReferenceDailyIntake(): QuantityMetric
	{
		$exceptions = new \Fatty\Exceptions\FattyExceptionCollection;

		try {
			$weightGoalEnergyExpenditure = $this->calcWeightGoalEnergyExpenditure()->getResult()->getInUnit("kcal");
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$gender = $this->getGender();
			if (!$gender) {
				throw new \Fatty\Exceptions\MissingGenderException;
			}

			$referenceDailyIntakeBonus = $gender->calcReferenceDailyIntakeBonus($this);
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		if ($exceptions->hasExceptions()) {
			throw $exceptions;
		}

		$weightGoalEnergyExpenditureValue = $weightGoalEnergyExpenditure->getAmount()->getValue();

		$referenceDailyIntakeBonus = $referenceDailyIntakeBonus->getResult()->getInUnit("kcal");
		$referenceDailyIntakeBonusValue = $referenceDailyIntakeBonus->getAmount()->getValue();

		$resultValue = $weightGoalEnergyExpenditureValue + $referenceDailyIntakeBonusValue;
		$result = (new Energy(
			new Amount($resultValue),
			"kcal",
		))->getInUnit($this->getUnits());

		$formula = "
			weightGoalEnergyExpenditure[" . $weightGoalEnergyExpenditure . "] + referenceDailyIntakeBonus[" . $referenceDailyIntakeBonus . "]
			= {$result->getInUnit("kcal")->getAmount()->getValue()} kcal
			= {$result->getInUnit("kJ")->getAmount()->getValue()} kJ
		";

		return new QuantityMetric("referenceDailyIntake", $result, $formula);
	}

	/*****************************************************************************
	 * Živiny.
	 */
	public function calcGoalNutrients(): MetricCollection
	{
		if (!$this->getDiet()->getApproach()) {
			throw new \Fatty\Exceptions\MissingDietApproachException;
		}

		return $this->getDiet()->getApproach()->calcGoalNutrients($this);
	}

	public function getResponse(): array
	{
		$exceptions = new \Fatty\Exceptions\FattyExceptionCollection;

		$res = [];

		/**************************************************************************
		 * Input.
		 */
		$res["input"]["gender"] = $this->getGender() ? $this->getGender()->getCode() : null;
		$res["input"]["birthday"] = $this->getBirthday() ? $this->getBirthday()->getTime()->format("Y-m-d") : null;
		$res["input"]["weight"] = $this->getWeight() ? $this->getWeight()->getAmount()->getValue() : null;
		$res["input"]["proportions_height"] = $this->getProportions()->getHeight() ? $this->getProportions()->getHeight()->getAmount()->getValue() : null;
		$res["input"]["proportions_waist"] = $this->getProportions()->getWaist() ? $this->getProportions()->getWaist()->getAmount()->getValue() : null;
		$res["input"]["proportions_hips"] = $this->getProportions()->getHips() ? $this->getProportions()->getHips()->getAmount()->getValue() : null;
		$res["input"]["proportions_neck"] = $this->getProportions()->getNeck() ? $this->getProportions()->getNeck()->getAmount()->getValue() : null;
		$res["input"]["bodyFatPercentage"] = $this->getBodyFatPercentage() ? $this->getBodyFatPercentage()->getValue() : null;
		$res["input"]["activity"] = $this->getActivity() ? $this->getActivity()->getValue() : null;
		$res["input"]["sportDurations_lowFrequency"] = $this->getSportDurations()->getLowFrequency() ? $this->getSportDurations()->getLowFrequency()->getAmount()->getValue() : null;
		$res["input"]["sportDurations_aerobic"] = $this->getSportDurations()->getAerobic() ? $this->getSportDurations()->getAerobic()->getAmount()->getValue() : null;
		$res["input"]["sportDurations_anaerobic"] = $this->getSportDurations()->getAnaerobic() ? $this->getSportDurations()->getAnaerobic()->getAmount()->getValue() : null;
		$res["input"]["goal_vector"] = $this->getGoal()->getVector() ? $this->getGoal()->getVector()->getCode() : null;
		$res["input"]["goal_weight"] = $this->getGoal()->getWeight() ? $this->getGoal()->getWeight()->getAmount()->getValue() : null;
		$res["input"]["diet_approach"] = $this->getDiet()->getApproach() ? $this->getDiet()->getApproach()->getCode() : null;

		try {
			$res["input"]["diet_carbs"] = $this->getDiet()->getCarbs() ? $this->getDiet()->getCarbs()->getAmount()->getValue() : null;
		} catch (\Throwable $e) {
			// Nevermind.
		}

		// $res["input"]["pregnancyIsPregnant"] =
		// 			$this->getGender() instanceof \App\Classes\Profile\Genders\Female
		// 	&& $this->getGender()->getIsPregnant()
		// 		? true : false;

		// $res["input"]["childbirthDate"] =
		// 			$this->getGender() instanceof \App\Classes\Profile\Genders\Female
		// 	&& $this->getGender()->getIsPregnant()
		// 	&& $this->getGender()->getChildbirthDate() instanceof \App\Classes\Profile\Birthday
		// 		? $this->getGender()->getChildbirthDate()->getBirthday()->format("Y-m-d") : null;

		// $res["input"]["breastfeedingIsBreastfeeding"] =
		// 			$this->getGender() instanceof \App\Classes\Profile\Genders\Female
		// 	&& $this->getGender()->isBreastfeeding()
		// 		? true : false;

		// $res["input"]["breastfeeding"]["childbirthDate"] =
		// 			$this->getGender() instanceof \App\Classes\Profile\Genders\Female
		// 	&& $this->getGender()->isBreastfeeding()
		// 	&& $this->getGender()->getBreastfeedingChildbirthDate() instanceof \App\Classes\Profile\Birthday
		// 		? $this->getGender()->getBreastfeedingChildbirthDate()->getBirthday()->format("Y-m-d") : null;

		// $res["input"]["breastfeedingMode"] =
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
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->getProportions()->calcHeight());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcBodyMassIndex());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcBodyMassIndexDeviation());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcWaistHipRatio());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcWaistHipRatioDeviation());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcBodyFatPercentage());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcBodyFatWeight());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcActiveBodyMassPercentage());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->merge($this->calcOptimalFatPercentage());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->merge($this->calcOptimalFatWeight());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcEssentialFatPercentage());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcEssentialFatWeight());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->merge($this->calcFatWithinOptimalPercentage());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->merge($this->calcFatWithinOptimalWeight());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->merge($this->calcFatOverOptimalPercentage());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->merge($this->calcFatOverOptimalWeight());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcBodyFatDeviation());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcRiskDeviation());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcActiveBodyMassWeight());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcFatFreeMass());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcBasalMetabolicRate());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcPhysicalActivityLevel());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcTotalDailyEnergyExpenditure());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcWeightGoalEnergyExpenditure());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcReferenceDailyIntake());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->getGoal()->calcGoalVector());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->getGoal()->calcGoalWeight());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		// try {
		// 	$metricCollection->append($this->getGoal()->calcWeightGoalEnergyExpenditure($this));
		// } catch (\Fatty\Exceptions\FattyException $e) {
		// 	$exceptions->addException($e);
		// }

		try {
			$metricCollection->append($this->getDiet()->calcDietApproach());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->getGoal()->calcGoalDuration());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcBodyType($this));
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcMaxOptimalWeight());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcFitnessLevel());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->append($this->calcSportProteinCoefficient());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		try {
			$metricCollection->merge($this->calcGoalNutrients());
		} catch (\Fatty\Exceptions\FattyException $e) {
			$exceptions->addException($e);
		}

		if ($exceptions->hasExceptions()) {
			throw $exceptions;
		}

		$locale = new Locale("cs_CZ");
		$res["output"]["metrics"] = $metricCollection->getSorted()->getResponse($locale);

		return $res;
	}

	public function getRestResponse(?ServerRequestInterface $request = null): RestResponse
	{
		return new RestResponse($this->getResponse());
	}
}
