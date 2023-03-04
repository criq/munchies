<?php

namespace Fatty;

use Fatty\Errors\MissingActivityError;
use Fatty\Errors\MissingBirthdayError;
use Fatty\Errors\MissingDietApproachError;
use Fatty\Errors\MissingGenderError;
use Fatty\Errors\MissingHeightError;
use Fatty\Errors\MissingHipsError;
use Fatty\Errors\MissingPregnancyError;
use Fatty\Errors\MissingWaistError;
use Fatty\Errors\MissingWeightError;
use Fatty\Exceptions\FattyException;
use Fatty\Metrics\ActiveBodyMassPercentageMetric;
use Fatty\Metrics\ActiveBodyMassWeightMetric;
use Fatty\Metrics\ActivityMetric;
use Fatty\Metrics\AmountMetricResult;
use Fatty\Metrics\ArrayMetricResult;
use Fatty\Metrics\BasalMetabolicRateMetric;
use Fatty\Metrics\BasalMetabolicRateStrategyMetric;
use Fatty\Metrics\BodyFatDeviationMetric;
use Fatty\Metrics\BodyFatPercentageMetric;
use Fatty\Metrics\BodyFatWeightMetric;
use Fatty\Metrics\BodyMassIndexDeviationMetric;
use Fatty\Metrics\BodyMassIndexMetric;
use Fatty\Metrics\BodyTypeMetric;
use Fatty\Metrics\BooleanMetricResult;
use Fatty\Metrics\EssentialFatPercentageMetric;
use Fatty\Metrics\EssentialFatWeightMetric;
use Fatty\Metrics\EstimatedFunctionalMassMetric;
use Fatty\Metrics\FatFreeMassMetric;
use Fatty\Metrics\FatOverOptimalPercentageMaxMetric;
use Fatty\Metrics\FatOverOptimalPercentageMinMetric;
use Fatty\Metrics\FatOverOptimalWeightMaxMetric;
use Fatty\Metrics\FatOverOptimalWeightMinMetric;
use Fatty\Metrics\FatWithinOptimalPercentageMaxMetric;
use Fatty\Metrics\FatWithinOptimalPercentageMinMetric;
use Fatty\Metrics\FatWithinOptimalWeightMaxMetric;
use Fatty\Metrics\FatWithinOptimalWeightMinMetric;
use Fatty\Metrics\FitnessLevelMetric;
use Fatty\Metrics\GoalNutrientsCarbsMetric;
use Fatty\Metrics\GoalNutrientsFatsMetric;
use Fatty\Metrics\GoalNutrientsProteinsMetric;
use Fatty\Metrics\IsOverweightMetric;
use Fatty\Metrics\MaxOptimalWeightMetric;
use Fatty\Metrics\MetricResultCollection;
use Fatty\Metrics\OptimalFatPercentageMaxMetric;
use Fatty\Metrics\OptimalFatPercentageMinMetric;
use Fatty\Metrics\OptimalFatWeightMaxMetric;
use Fatty\Metrics\OptimalFatWeightMinMetric;
use Fatty\Metrics\PhysicalActivityLevelMetric;
use Fatty\Metrics\PregnancyTrimesterMetric;
use Fatty\Metrics\PregnancyWeekMetric;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\ReferenceDailyIntakeMetric;
use Fatty\Metrics\RiskDeviationMetric;
use Fatty\Metrics\SportProteinCoefficientKeyMetric;
use Fatty\Metrics\SportProteinCoefficientMetric;
use Fatty\Metrics\SportProteinSetKeyMetric;
use Fatty\Metrics\StringMetricResult;
use Fatty\Metrics\TotalDailyEnergyExpenditureMetric;
use Fatty\Metrics\WaistHipRatioDeviationMetric;
use Fatty\Metrics\WaistHipRatioMetric;
use Fatty\Metrics\WeightMetric;
use Katu\Errors\Error;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Params\UserInput;
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
	protected $referenceTime;
	protected $sportDurations;
	protected $strategy;
	protected $units = "kcal";
	protected $weight;
	protected $weightHistory;

	public static function createFromRequest(ServerRequestInterface $request): Validation
	{
		$validations = new ValidationCollection;
		$calculator = new static;

		$params = $request->getQueryParams();

		if (trim($params["gender"] ?? null)) {
			$genderValidation = Gender::validateGender(new UserInput("gender", $params["gender"]));
			$validations[] = $genderValidation;

			if (!$genderValidation->hasErrors()) {
				$calculator->setGender($genderValidation->getResponse());
			}
		}

		if ($calculator->getGender() instanceof \Fatty\Genders\Female && trim($params["pregnancy_childbirthDate"] ?? null)) {
			$childbirtDateValidation = Birthday::validateBirthday(new UserInput("pregnancy_childbirthDate", $params["pregnancy_childbirthDate"]));
			$validations[] = $childbirtDateValidation;

			if (!$childbirtDateValidation->hasErrors()) {
				$calculator->getGender()->setPregnancy(new Pregnancy($childbirtDateValidation->getResponse()->getTime()));
			}
		}

		if ($calculator->getGender() instanceof \Fatty\Genders\Female && trim($params["pregnancy_weightBeforePregnancy"] ?? null)) {
			$weightBeforePregnancyValidation = Weight::validateWeight(new UserInput("pregnancy_weightBeforePregnancy", $params["pregnancy_weightBeforePregnancy"]));
			$validations[] = $weightBeforePregnancyValidation;

			if (!$weightBeforePregnancyValidation->hasErrors()) {
				$calculator->getGender()->getPregnancy()->setWeightBeforePregnancy($weightBeforePregnancyValidation->getResponse());
			}
		}

		$children = new ChildCollection;
		foreach ($params as $key => $value) {
			if (preg_match("/^children_[0-9]+_birthday$/", $key)) {
				// var_dump($value);die;
			}
		}

		if (trim($params["birthday"] ?? null)) {
			$birthdayValidation = Birthday::validateBirthday(new UserInput("birthday", $params["birthday"]));
			$validations[] = $birthdayValidation;

			if (!$birthdayValidation->hasErrors()) {
				$calculator->setBirthday($birthdayValidation->getResponse());
			}
		}

		if (trim($params["weight"] ?? null)) {
			$weightValidation = Weight::validateWeight(new UserInput("weight", $params["weight"]));
			$validations[] = $weightValidation;

			if (!$weightValidation->hasErrors()) {
				$calculator->setWeight($weightValidation->getResponse());
			}
		}

		if (trim($params["proportions_height"] ?? null)) {
			$heightValidation = Proportions::validateHeight(new UserInput("proportions_height", $params["proportions_height"]));
			$validations[] = $heightValidation;

			if (!$heightValidation->hasErrors()) {
				$calculator->getProportions()->setHeight($heightValidation->getResponse());
			}
		}

		if (trim($params["proportions_waist"] ?? null)) {
			$waistValidation = Proportions::validateWaist(new UserInput("proportions_waist", $params["proportions_waist"]));
			$validations[] = $waistValidation;

			if (!$waistValidation->hasErrors()) {
				$calculator->getProportions()->setWaist($waistValidation->getResponse());
			}
		}

		if (trim($params["proportions_hips"] ?? null)) {
			$hipsValidation = Proportions::validateHips(new UserInput("proportions_hips", $params["proportions_hips"]));
			$validations[] = $hipsValidation;

			if (!$hipsValidation->hasErrors()) {
				$calculator->getProportions()->setHips($hipsValidation->getResponse());
			}
		}

		if (trim($params["proportions_neck"] ?? null)) {
			$neckValidation = Proportions::validateNeck(new UserInput("proportions_neck", $params["proportions_neck"]));
			$validations[] = $neckValidation;

			if (!$neckValidation->hasErrors()) {
				$calculator->getProportions()->setNeck($neckValidation->getResponse());
			}
		}

		if (trim($params["bodyFatPercentage"] ?? null)) {
			$bodyFatPercentageValidation = static::validateBodyFatPercentage(new UserInput("bodyFatPercentage", $params["bodyFatPercentage"]));
			$validations[] = $bodyFatPercentageValidation;

			if (!$bodyFatPercentageValidation->hasErrors()) {
				$calculator->setBodyFatPercentage($bodyFatPercentageValidation->getResponse());
			}
		}

		if (trim($params["activity"] ?? null)) {
			$activityValidation = Activity::validateActivity(new UserInput("activity", $params["activity"]));
			$validations[] = $activityValidation;

			if (!$activityValidation->hasErrors()) {
				$calculator->setActivity($activityValidation->getResponse());
			}
		}

		if (trim($params["sportDurations_lowFrequency"] ?? null)) {
			$lowFrequencyValidation = SportDurations::validateLowFrequency(new UserInput("sportDurations_lowFrequency", $params["sportDurations_lowFrequency"]));
			$validations[] = $lowFrequencyValidation;

			if (!$lowFrequencyValidation->hasErrors()) {
				$calculator->getSportDurations()->setLowFrequency($lowFrequencyValidation->getResponse());
			}
		}

		if (trim($params["sportDurations_aerobic"] ?? null)) {
			$aerobicValidation = SportDurations::validateAerobic(new UserInput("sportDurations_aerobic", $params["sportDurations_aerobic"]));
			$validations[] = $aerobicValidation;

			if (!$aerobicValidation->hasErrors()) {
				$calculator->getSportDurations()->setAerobic($aerobicValidation->getResponse());
			}
		}

		if (trim($params["sportDurations_anaerobic"] ?? null)) {
			$anaerobicValidation = SportDurations::validateAnaerobic(new UserInput("sportDurations_anaerobic", $params["sportDurations_anaerobic"]));
			$validations[] = $anaerobicValidation;

			if (!$anaerobicValidation->hasErrors()) {
				$calculator->getSportDurations()->setAnaerobic($anaerobicValidation->getResponse());
			}
		}

		if (trim($params["goal_vector"] ?? null)) {
			$goalVectorValidation = Goal::validateVector(new UserInput("goal_vector", $params["goal_vector"]));
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
			$goalWeightValidation = Goal::validateWeight(new UserInput("goal_weight", $goalWeightString));
			$validations[] = $goalWeightValidation;

			if (!$goalWeightValidation->hasErrors()) {
				$calculator->getGoal()->setWeight($goalWeightValidation->getResponse());
			}
		}

		if (trim($params["diet_approach"] ?? null)) {
			$dietApproachValidation = Diet::validateApproach(new UserInput("diet_approach", $params["diet_approach"]));
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
			$dietCarbsValidation = Diet::validateCarbs(new UserInput("diet_carbs", $dietCarbsString));
			$validations[] = $dietCarbsValidation;

			if (!$dietCarbsValidation->hasErrors()) {
				$calculator->getDiet()->setCarbs($dietCarbsValidation->getResponse());
			}
		}

		if (trim($params["units"] ?? null)) {
			$unitsValidation = static::validateUnits(new UserInput("units", $params["units"]));
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
		} catch (FattyException $e) {
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
		if ($this->strategy) {
			return $this->strategy;
		}

		if ($this->getGender() instanceof \Fatty\Genders\Female && ($this->getGender()->getIsPregnant($this) || $this->getGender()->getIsNewMother($this))) {
			return new \Fatty\Strategies\DiaMama;
		}

		return new \Fatty\Strategies\Zivot20;
	}

	/****************************************************************************
	 * Reference date.
	 */
	public function setReferenceTime(?Time $value): Calculator
	{
		$this->referenceTime = $value;

		return $this;
	}

	public function getReferenceTime(): Time
	{
		return $this->referenceTime ?: new Time;
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

	public function calcWeight(): ?QuantityMetricResult
	{
		$result = new QuantityMetricResult(new WeightMetric);

		$weight = $this->getWeight();
		if (!$weight) {
			$result->addError(new MissingWeightError);
		} else {
			$weightValue = $weight->getAmount()->getValue();
			$formula = "weight[{$weightValue}] = {$weightValue}";

			$result->setResult($weight)->setFormula($formula);
		}

		return $result;
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

	public function calcHeight(): QuantityMetricResult
	{
		return $this->getProportions()->calcHeight();
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

	public function calcBodyFatPercentage(): AmountMetricResult
	{
		$result = new AmountMetricResult(new BodyFatPercentageMetric);

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		} else {
			return $this->getGender()->calcBodyFatPercentage($this);
		}

		return $result;
	}

	/*****************************************************************************
	 * Body type - typ postavy.
	 */
	public function calcBodyType(): StringMetricResult
	{
		$result = new StringMetricResult(new BodyTypeMetric);

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		} else {
			return $gender->calcBodyType($this);
		}

		return $result;
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

	public function calcActivity(): AmountMetricResult
	{
		$result = new AmountMetricResult(new ActivityMetric);

		$activity = $this->activity;
		if (!$activity) {
			$result->addError(new MissingActivityError);
		} else {
			$result->setResult($activity);
		}

		return $result;
	}

	public function setSportDurations(?SportDurations $sportDurations): Calculator
	{
		$this->sportDurations = $sportDurations;

		return $this;
	}

	public function getSportDurations(): SportDurations
	{
		$this->sportDurations = $this->sportDurations instanceof SportDurations ? $this->sportDurations : new SportDurations;

		return $this->sportDurations;
	}

	public function calcSportActivity(): AmountMetricResult
	{
		return $this->getSportDurations()->calcSportActivity();
	}

	public function calcSportProteinMatrix(): ArrayMetricResult
	{
		if (!$this->getGender()) {
			throw new \Fatty\Exceptions\MissingGenderException;
		}

		return $this->getGender()->calcSportProteinMatrix();
	}

	public function calcSportProteinSetKey(): StringMetricResult
	{
		$result = new StringMetricResult(new SportProteinSetKeyMetric);

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		} else {
			return $gender->calcSportProteinSetKey($this);
		}

		return $result;
	}

	public function calcSportProteinCoefficientKey(): StringMetricResult
	{
		$result = new StringMetricResult(new SportProteinCoefficientKeyMetric);

		try {
			$value = $this->getSportDurations()->getMaxProteinSportDuration()->getSportProteinCoefficientKey();
		} catch (\Throwable $e) {
			$value = "NO_ACTIVITY";
		}

		$result->setResult(new StringValue((string)$value));

		return $result;
	}

	public function calcSportProteinCoefficient(): AmountMetricResult
	{
		$result = new AmountMetricResult(new SportProteinCoefficientMetric);

		$sportProteinCoefficientKeyResult = $this->calcSportProteinCoefficientKey();
		$result->addErrors($sportProteinCoefficientKeyResult->getErrors());

		$sportProteinSetKeyResult = $this->calcSportProteinSetKey();
		$result->addErrors($sportProteinSetKeyResult->getErrors());

		$sportProteinMatrixResult = $this->calcSportProteinMatrix();
		$result->addErrors($sportProteinMatrixResult->getErrors());

		if (!$result->hasErrors()) {
			$sportProteinMatrix = $sportProteinMatrixResult->getResult()->getArrayValue();
			$sportProteinSetKey = $sportProteinSetKeyResult->getResult()->getStringValue();
			$sportProteinCoefficientKey = $sportProteinCoefficientKeyResult->getResult()->getStringValue();

			$proteinCoefficient = $sportProteinMatrix[$sportProteinSetKey][$sportProteinCoefficientKey];

			$result->setResult(new Amount($proteinCoefficient));
		}

		return $result;
	}

	/*****************************************************************************
	 * Physical activity level.
	 */
	public function calcPhysicalActivityLevel(): AmountMetricResult
	{
		$result = new AmountMetricResult(new PhysicalActivityLevelMetric);

		$activityResult = $this->calcActivity();
		$result->addErrors($activityResult->getErrors());

		$sportActivityResult = $this->calcSportActivity();
		$result->addErrors($sportActivityResult->getErrors());

		if (!$result->hasErrors()) {
			$activityValue = $activityResult->getResult()->getNumericalValue();
			$sportActivityValue = $sportActivityResult->getResult()->getNumericalValue();

			$activity = new Activity($activityValue + $sportActivityValue);
			$formula = "activityPal[{$activityValue}] + sportPal[{$sportActivityValue}] = {$activity->getValue()}";

			$result->setResult($activity)->setFormula($formula);
		}

		return $result;
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
	public function calcIsOverweight(): BooleanMetricResult
	{
		$result = new BooleanMetricResult(new IsOverweightMetric);

		$fatOverOptimalWeightResult = $this->calcFatOverOptimalWeight()->filterByMetric(new FatOverOptimalWeightMaxMetric)->getFirst();
		$result->addErrors($fatOverOptimalWeightResult->getErrors());

		if (!$result->hasErrors()) {
			$result->setResult(new BooleanValue(!!$fatOverOptimalWeightResult->getResult()));
		}

		return $result;
	}

	public function calcBodyMassIndex(): AmountMetricResult
	{
		$result = new AmountMetricResult(new BodyMassIndexMetric);

		if (!($this->getWeight() instanceof Weight)) {
			$result->addError(new MissingWeightError);
		}
		if (!($this->getProportions()->getHeight() instanceof Length)) {
			$result->addError(new MissingHeightError);
		}

		if (!$result->hasErrors()) {
			$weightValue = $this->getWeight()->getInUnit("kg")->getAmount()->getValue();
			$heightValue = $this->getProportions()->getHeight()->getInUnit("m")->getAmount()->getValue();

			$resultValue = $weightValue / pow($heightValue, 2);
			$amount = new Amount($resultValue);
			$formula = "
				weight[{$weightValue}] / pow(height[{$heightValue}], 2)
				= {$weightValue} / " . (pow($heightValue, 2)) . "
				= {$resultValue}
			";

			$result->setResult($amount)->setFormula($formula);
		}

		return $result;
	}

	public function calcBodyMassIndexDeviation(): AmountMetricResult
	{
		$result = new AmountMetricResult(new BodyMassIndexDeviationMetric);

		$bodyMassIndexResult = $this->calcBodyMassIndex();
		$result->addErrors($bodyMassIndexResult->getErrors());

		if (!$result->hasErrors()) {
			$deviation = static::getDeviation($bodyMassIndexResult->getResult()->getNumericalValue(), 22, [17.7, 40]);
			$result->setResult($deviation);
		}

		return $result;
	}

	/*****************************************************************************
	 * Waist-hip ratio - WHR.
	 */
	public function calcWaistHipRatio(): AmountMetricResult
	{
		$result = new AmountMetricResult(new WaistHipRatioMetric);

		if (!($this->getProportions()->getWaist() instanceof Length)) {
			$result->addError(new MissingWaistError);
		}
		if (!($this->getProportions()->getHips() instanceof Length)) {
			$result->addError(new MissingHipsError);
		}

		if (!$result->hasErrors()) {
			$waistValue = $this->getProportions()->getWaist()->getInUnit("cm")->getAmount()->getValue();
			$hipsValue = $this->getProportions()->getHips()->getInUnit("cm")->getAmount()->getValue();

			$amount = new Amount($waistValue / $hipsValue);
			$formula = "waist[{$waistValue}] / hips[{$hipsValue}] = {$amount->getValue()}";

			$result->setResult($amount)->setFormula($formula);
		}

		return $result;
	}

	public function calcWaistHipRatioDeviation(): AmountMetricResult
	{
		$result = new AmountMetricResult(new WaistHipRatioDeviationMetric);

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		}

		$waistHipRatioResult = $this->calcWaistHipRatio();
		$result->addErrors($waistHipRatioResult->getErrors());

		if (!$result->hasErrors()) {
			$waistHipRatioValue = $waistHipRatioResult->getResult()->getNumericalValue();

			if ($gender instanceof Genders\Male) {
				$amount = new Amount(static::getDeviation($waistHipRatioValue, .8, [.8, .95])->getValue() - 1);
			} elseif ($gender instanceof Genders\Female) {
				$amount = new Amount(static::getDeviation($waistHipRatioValue, .9, [.9, 1])->getValue() - 1);
			}

			$result->setResult($amount);
		}

		return $result;
	}

	/*****************************************************************************
	 * Míra rizika.
	 */
	public function calcRiskDeviation(): AmountMetricResult
	{
		$result = new AmountMetricResult(new RiskDeviationMetric);

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		}

		$bodyMassIndexResult = $this->calcBodyMassIndex();
		$result->addErrors($bodyMassIndexResult->getErrors());

		$waistHipRatioResult = $this->calcWaistHipRatio();
		$result->addErrors($waistHipRatioResult->getErrors());

		$isOverweight = $this->calcIsOverweight();

		if (!$result->hasErrors()) {
			$bodyMassIndexValue = $bodyMassIndexResult->getResult()->getNumericalValue();
			$waistHipRatioValue = $waistHipRatioResult->getResult()->getNumericalValue();

			if (($gender instanceof Genders\Male && $waistHipRatioValue < .8 && !$isOverweight)
				|| ($gender instanceof Genders\Female && $waistHipRatioValue < .9 && !$isOverweight)
			) {
				$column = "A";
			} elseif (($gender instanceof Genders\Male && $waistHipRatioValue < .8 && $isOverweight)
				|| ($gender instanceof Genders\Female && $waistHipRatioValue < .9 && $isOverweight)
			) {
				$column = "B";
			} elseif (($gender instanceof Genders\Male && $waistHipRatioValue >= .8 && $waistHipRatioValue <= .95 && !$isOverweight)
				|| ($gender instanceof Genders\Female && $waistHipRatioValue >= .9 && $waistHipRatioValue <= 1 && !$isOverweight)
			) {
				$column = "C";
			} elseif (($gender instanceof Genders\Male && $waistHipRatioValue >= .8 && $waistHipRatioValue <= .95 && $isOverweight)
				|| ($gender instanceof Genders\Female && $waistHipRatioValue >= .9 && $waistHipRatioValue <= 1 && $isOverweight)
			) {
				$column = "D";
			} else {
				$column = "E";
			}

			if ($bodyMassIndexValue < 17.7) {
				$row = 1;
			} elseif ($bodyMassIndexValue >= 17.7 && $bodyMassIndexValue < 18) {
				$row = 2;
			} elseif ($bodyMassIndexValue >= 18 && $bodyMassIndexValue < 25) {
				$row = 3;
			} elseif ($bodyMassIndexValue >= 25 && $bodyMassIndexValue < 30) {
				$row = 4;
			} elseif ($bodyMassIndexValue >= 30 && $bodyMassIndexValue < 35) {
				$row = 5;
			} elseif ($bodyMassIndexValue >= 35 && $bodyMassIndexValue < 40) {
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

			$result->setResult(new Amount($matrix[$column][$row]));
		}

		return $result;
	}

	/*****************************************************************************
	 * Procento tělesného tuku - BFP.
	 */
	public function calcBodyFatWeight(): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new BodyFatWeightMetric);

		$weight = $this->getWeight();
		if (!$weight) {
			$result->addError(new MissingWeightError);
		}

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		}

		if (!$result->hasErrors()) {
			$weightValue = $weight->getInUnit("kg")->getAmount()->getValue();
			$bodyFatPercentageValue = $gender->calcBodyFatPercentage($this)->getResult()->getNumericalValue();

			$bodyFatWeight = new Weight(
				new Amount($weightValue * $bodyFatPercentageValue),
				"kg",
			);

			$formula = "
				weight[{$weightValue}] * bodyFatPercentageValue[{$bodyFatPercentageValue}]
				= {$weight->getAmount()->getValue()} kg
			";

			$result->setResult($bodyFatWeight)->setFormula($formula);
		}

		return $result;
	}

	public function calcActiveBodyMassPercentage(): AmountMetricResult
	{
		$result = new AmountMetricResult(new ActiveBodyMassPercentageMetric);

		$bodyFatPercentageResult = $this->calcBodyFatPercentage($this);
		$result->addErrors($bodyFatPercentageResult->getErrors());

		if (!$result->hasErrors()) {
			$bodyFatPercentageValue = $bodyFatPercentageResult->getResult()->getNumericalValue();

			$percentage = new Percentage(1 - $bodyFatPercentageValue);
			$formula = "1 - bodyFatPercentage[$bodyFatPercentageValue] = {$percentage->getValue()}";

			$result->setResult($percentage)->setFormula($formula);
		}

		return $result;
	}

	public function calcActiveBodyMassWeight(): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new ActiveBodyMassWeightMetric);

		$weight = $this->getWeight();
		if (!$weight) {
			$result->addError(new MissingWeightError);
		} else {
			$weightValue = $weight->getInUnit("kg")->getNumericalValue();
			$activeBodyMassPercentageValue = $this->calcActiveBodyMassPercentage()->getResult()->getNumericalValue();
			$resultValue = $weightValue * $activeBodyMassPercentageValue;
			$formula = "weight[$weightValue] * activeBodyMassPercentage[$activeBodyMassPercentageValue]";

			$result->setResult(new Weight(new Amount($resultValue), "kg"))->setFormula($formula);
		}

		return $result;
	}

	public function calcOptimalFatPercentage(): MetricResultCollection
	{
		$minResult = new AmountMetricResult(new OptimalFatPercentageMinMetric);
		$maxResult = new AmountMetricResult(new OptimalFatPercentageMaxMetric);

		$gender = $this->getGender();
		if (!$gender) {
			$minResult->addError(new MissingGenderError);
			$maxResult->addError(new MissingGenderError);
		}

		$birthday = $this->getBirthday();
		if (!$birthday) {
			$minResult->addError(new MissingBirthdayError);
			$maxResult->addError(new MissingBirthdayError);
		}

		if (!$minResult->hasErrors() && !$maxResult->hasErrors()) {
			$age = $birthday->getAge($this->getReferenceTime());

			if ($gender instanceof Genders\Male) {
				if ($age < 18) {
					$interval = new Interval(new Percentage(0), new Percentage(0));
				} elseif ($age >= 18 && $age < 30) {
					$interval = new Interval(new Percentage(.10), new Percentage(.15));
				} elseif ($age >= 30 && $age < 50) {
					$interval = new Interval(new Percentage(.11), new Percentage(.17));
				} else {
					$interval = new Interval(new Percentage(.12), new Percentage(.19));
				}
			} elseif ($gender instanceof Genders\Female) {
				if ($age < 18) {
					$interval = new Interval(new Percentage(0), new Percentage(0));
				} elseif ($age >= 18 && $age < 30) {
					$interval = new Interval(new Percentage(.14), new Percentage(.21));
				} elseif ($age >= 30 && $age < 50) {
					$interval = new Interval(new Percentage(.15), new Percentage(.23));
				} else {
					$interval = new Interval(new Percentage(.16), new Percentage(.25));
				}
			}

			$minResult->setResult($interval->getMin());
			$maxResult->setResult($interval->getMax());
		}

		return new MetricResultCollection([
			$minResult,
			$maxResult,
		]);
	}

	public function calcOptimalFatWeight(): MetricResultCollection
	{
		$minResult = new QuantityMetricResult(new OptimalFatWeightMinMetric);
		$maxResult = new QuantityMetricResult(new OptimalFatWeightMaxMetric);

		$weight = $this->getWeight();
		if (!$weight) {
			$minResult->addError(new MissingWeightError);
			$maxResult->addError(new MissingWeightError);
		}

		$optimalFatPercentageResult = $this->calcOptimalFatPercentage();
		$optimalFatPercentageMinResult = $optimalFatPercentageResult->filterByMetric(new OptimalFatPercentageMinMetric)->getFirst();
		$optimalFatPercentageMaxResult = $optimalFatPercentageResult->filterByMetric(new OptimalFatPercentageMaxMetric)->getFirst();

		$minResult->addErrors($optimalFatPercentageMinResult->getErrors());
		$maxResult->addErrors($optimalFatPercentageMaxResult->getErrors());

		if (!$minResult->hasErrors() && !$maxResult->hasErrors()) {
			$weightValue = $weight->getInUnit("kg")->getNumericalValue();
			$optimalFatPercentageValue = $optimalFatPercentageResult->filterByMetric(new OptimalFatPercentageMinMetric)->getFirst()->getResult()->getNumericalValue();
			$minResult->setResult(new Weight(new Amount($weightValue * $optimalFatPercentageValue), "kg"));

			$weightValue = $weight->getInUnit("kg")->getNumericalValue();
			$optimalFatPercentageValue = $optimalFatPercentageResult->filterByMetric(new OptimalFatPercentageMaxMetric)->getFirst()->getResult()->getNumericalValue();
			$maxResult->setResult(new Weight(new Amount($weightValue * $optimalFatPercentageValue), "kg"));
		}

		return new MetricResultCollection([
			$minResult,
			$maxResult,
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

	public function calcEssentialFatPercentage(): AmountMetricResult
	{
		$result = new AmountMetricResult(new EssentialFatPercentageMetric);

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		} else {
			return $this->getGender()->calcEssentialFatPercentage();
		}

		return $result;
	}

	public function calcEssentialFatWeight(): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new EssentialFatWeightMetric);

		$weight = $this->getWeight();
		if (!$weight) {
			$result->addError(new MissingWeightError);
		}

		$essentialFatPercentageResult = $this->calcEssentialFatPercentage();
		$result->addErrors($essentialFatPercentageResult->getErrors());

		if (!$result->hasErrors()) {
			$result->setResult(new Weight(
				new Amount(
					$weight->getInUnit("kg")->getAmount()->getValue() * $essentialFatPercentageResult->getResult()->getNumericalValue()
				),
				"kg",
			));
		}

		return $result;
	}

	public function calcFatWithinOptimalPercentage(): MetricResultCollection
	{
		$minResult = new AmountMetricResult(new FatWithinOptimalPercentageMinMetric);
		$maxResult = new AmountMetricResult(new FatWithinOptimalPercentageMaxMetric);

		$optimalFatWeightResults = $this->calcOptimalFatWeight();
		$minResult->addErrors($optimalFatWeightResults->filterByMetric(new OptimalFatWeightMinMetric)->getFirst()->getErrors());
		$maxResult->addErrors($optimalFatWeightResults->filterByMetric(new OptimalFatWeightMaxMetric)->getFirst()->getErrors());

		$bodyFatWeightResult = $this->calcBodyFatWeight();
		$minResult->addErrors($bodyFatWeightResult->getErrors());
		$maxResult->addErrors($bodyFatWeightResult->getErrors());

		if (!$minResult->hasErrors() && !$maxResult->hasErrors()) {
			$value = $optimalFatWeightResults->filterByMetric(new OptimalFatWeightMinMetric)->getFirst()->getResult()->getNumericalValue() / $bodyFatWeightResult->getResult()->getInUnit("kg")->getAmount()->getValue();
			$minResult->setResult(new Percentage($value <= 1 ? $value : 1));

			$value = $optimalFatWeightResults->filterByMetric(new OptimalFatWeightMaxMetric)->getFirst()->getResult()->getNumericalValue() / $bodyFatWeightResult->getResult()->getInUnit("kg")->getAmount()->getValue();
			$maxResult->setResult(new Percentage($value <= 1 ? $value : 1));
		}

		return new MetricResultCollection([
			$minResult,
			$maxResult,
		]);
	}

	public function calcFatWithinOptimalWeight(): MetricResultCollection
	{
		$minResult = new QuantityMetricResult(new FatWithinOptimalWeightMinMetric);
		$maxResult = new QuantityMetricResult(new FatWithinOptimalWeightMaxMetric);

		$bodyFatWeightResult = $this->calcBodyFatWeight();
		$minResult->addErrors($bodyFatWeightResult->getErrors());
		$maxResult->addErrors($bodyFatWeightResult->getErrors());

		$optimalFatWeightResults = $this->calcOptimalFatWeight();
		$minResult->addErrors($optimalFatWeightResults->filterByMetric(new OptimalFatWeightMinMetric)->getFirst()->getErrors());
		$maxResult->addErrors($optimalFatWeightResults->filterByMetric(new OptimalFatWeightMaxMetric)->getFirst()->getErrors());

		if (!$minResult->hasErrors() && !$maxResult->hasErrors()) {
			$value = $bodyFatWeightResult->getResult()->getInUnit("kg")->getNumericalValue() - $optimalFatWeightResults->filterByMetric(new OptimalFatWeightMinMetric)->getFirst()->getResult()->getInUnit("kg")->getNumericalValue();
			$minResult->setResult(new Weight(new Amount($bodyFatWeightResult->getResult()->getInUnit("kg")->getNumericalValue() - ($value >= 0 ? $value : 0)), "kg"));

			$value = $bodyFatWeightResult->getResult()->getInUnit("kg")->getNumericalValue() - $optimalFatWeightResults->filterByMetric(new OptimalFatWeightMaxMetric)->getFirst()->getResult()->getInUnit("kg")->getNumericalValue();
			$maxResult->setResult(new Weight(new Amount($bodyFatWeightResult->getResult()->getInUnit("kg")->getNumericalValue() - ($value >= 0 ? $value : 0)), "kg"));
		}

		return new MetricResultCollection([
			$minResult,
			$maxResult,
		]);
	}

	public function calcFatOverOptimalWeight(): MetricResultCollection
	{
		$minResult = new QuantityMetricResult(new FatOverOptimalWeightMinMetric);
		$maxResult = new QuantityMetricResult(new FatOverOptimalWeightMaxMetric);

		$bodyFatWeightResult = $this->calcBodyFatWeight();
		$minResult->addErrors($bodyFatWeightResult->getErrors());
		$maxResult->addErrors($bodyFatWeightResult->getErrors());

		$optimalFatWeightResults = $this->calcOptimalFatWeight();
		$minResult->addErrors($optimalFatWeightResults->filterByMetric(new OptimalFatWeightMinMetric)->getFirst()->getErrors());
		$maxResult->addErrors($optimalFatWeightResults->filterByMetric(new OptimalFatWeightMaxMetric)->getFirst()->getErrors());

		if (!$minResult->hasErrors() && !$maxResult->hasErrors()) {
			$value = $bodyFatWeightResult->getResult()->getInUnit("kg")->getNumericalValue() - $optimalFatWeightResults->filterByMetric(new OptimalFatWeightMinMetric)->getFirst()->getResult()->getInUnit("kg")->getNumericalValue();
			$minResult->setResult(new Weight(new Amount($value >= 0 ? $value : 0), "kg"));

			$value = $bodyFatWeightResult->getResult()->getInUnit("kg")->getNumericalValue() - $optimalFatWeightResults->filterByMetric(new OptimalFatWeightMaxMetric)->getFirst()->getResult()->getInUnit("kg")->getNumericalValue();
			$maxResult->setResult(new Weight(new Amount($value >= 0 ? $value : 0), "kg"));
		}

		return new MetricResultCollection([
			$minResult,
			$maxResult,
		]);
	}

	public function calcMaxOptimalWeight(): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new MaxOptimalWeightMetric);

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		} else {
			return $gender->calcMaxOptimalWeight($this);
		}

		return $result;
	}

	public function calcFatOverOptimalPercentage(): MetricResultCollection
	{
		$minResult = new AmountMetricResult(new FatOverOptimalPercentageMinMetric);
		$maxResult = new AmountMetricResult(new FatOverOptimalPercentageMaxMetric);

		$fatOverOptimalWeightResults = $this->calcFatOverOptimalWeight();
		$minResult->addErrors($fatOverOptimalWeightResults->filterByMetric(new FatOverOptimalWeightMinMetric)->getFirst()->getErrors());
		$maxResult->addErrors($fatOverOptimalWeightResults->filterByMetric(new FatOverOptimalWeightMaxMetric)->getFirst()->getErrors());

		$bodyFatWeightResult = $this->calcBodyFatWeight();
		$minResult->addErrors($bodyFatWeightResult->getErrors());
		$maxResult->addErrors($bodyFatWeightResult->getErrors());

		if (!$minResult->hasErrors() && !$maxResult->hasErrors()) {
			$minResult->setResult(new Percentage($fatOverOptimalWeightResults->filterByMetric(new FatOverOptimalWeightMinMetric)->getFirst()->getResult()->getInUnit("kg")->getNumericalValue() / $bodyFatWeightResult->getResult()->getInUnit("kg")->getNumericalValue()));
			$maxResult->setResult(new Percentage($fatOverOptimalWeightResults->filterByMetric(new FatOverOptimalWeightMaxMetric)->getFirst()->getResult()->getInUnit("kg")->getNumericalValue() / $bodyFatWeightResult->getResult()->getInUnit("kg")->getNumericalValue()));
		}

		return new MetricResultCollection([
			$minResult,
			$maxResult,
		]);
	}

	public function calcBodyFatDeviation(): AmountMetricResult
	{
		$result = new AmountMetricResult(new BodyFatDeviationMetric);

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		}

		$bodyMassIndexResult = $this->calcBodyMassIndex();
		$result->addErrors($bodyMassIndexResult->getErrors());

		$bodyMassIndexDeviationResult = $this->calcBodyMassIndexDeviation();
		$result->addErrors($bodyMassIndexDeviationResult->getErrors());

		if (!$result->hasErrors()) {
			$isOverweight = $this->calcIsOverweight();

			if ($gender instanceof Genders\Male && $bodyMassIndexResult->getResult()->getNumericalValue() >= .95 && !$isOverweight) {
				$result->setResult(new Amount);
			} else {
				$result->setResult($bodyMassIndexDeviationResult->getResult());
			}
		}

		return $result;
	}

	public function calcFitnessLevel(): StringMetricResult
	{
		$result = new StringMetricResult(new FitnessLevelMetric);

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		} else {
			return $gender->calcFitnessLevel($this);
		}

		return $result;
	}

	public function calcEstimatedFunctionalMass(): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new EstimatedFunctionalMassMetric);

		$heightResult = $this->calcHeight();
		$result->addErrors($heightResult->getErrors());

		if (!$result->hasErrors()) {
			$heightValue = $heightResult->getResult()->getNumericalValue();
			$resultValue = $heightValue - 105;

			$weight = new Weight(new Amount($resultValue), "kg");
			$formula = "height[$heightValue] - 105 = $resultValue";

			$result->setResult($weight)->setFormula($formula);
		}

		return $result;
	}

	/*****************************************************************************
	 * Beztuková tělesná hmotnost - FFM.
	 */
	public function calcFatFreeMass(): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new FatFreeMassMetric);

		$weight = $this->getWeight();
		if (!$weight) {
			$result->addError(new MissingWeightError);
		}

		$bodyFatPercentageResult = $this->calcBodyFatPercentage();
		$result->addErrors($bodyFatPercentageResult->getErrors());

		if (!$result->hasErrors()) {
			$weightValue = $weight->getInUnit("kg")->getNumericalValue();
			$bodyFatPercentageValue = $bodyFatPercentageResult->getResult()->getNumericalValue();

			$resultValue = $weightValue - ($bodyFatPercentageValue * $weightValue);

			$formula = "
				weight[{$weightValue}] - (bodyFatPercentage[{$bodyFatPercentageValue}] * weight[{$weightValue}])
				= {$weightValue} - " . ($bodyFatPercentageValue * $weightValue) . "
				= {$resultValue} kg
			";

			$result->setResult(new Weight(new Amount($resultValue), "kg"))->setFormula($formula);
		}

		return $result;
	}

	/*****************************************************************************
	 * Bazální metabolismus - BMR.
	 */
	public function calcBasalMetabolicRateStrategy(): StringMetricResult
	{
		$result = new StringMetricResult(new BasalMetabolicRateStrategyMetric);

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		} else {
			return $gender->calcBasalMetabolicRateStrategy($this);
		}

		return $result;
	}

	public function calcBasalMetabolicRate(): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new BasalMetabolicRateMetric);

		if (!$this->getGender()) {
			$result->addError(new MissingGenderError);
		} else {
			return $this->getGender()->calcBasalMetabolicRate($this);
		}

		return $result;
	}

	/*****************************************************************************
	 * Total (Daily) Energy Expenditure - Termický efekt pohybu - TEE.
	 */
	public function calcTotalDailyEnergyExpenditure(): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new TotalDailyEnergyExpenditureMetric);

		$basalMetabolicRateResult = $this->calcBasalMetabolicRate();
		$result->addErrors($basalMetabolicRateResult->getErrors());

		$physicalActivityLevelResult = $this->calcPhysicalActivityLevel();
		$result->addErrors($physicalActivityLevelResult->getErrors());

		if (!$result->hasErrors()) {
			$basalMetabolicRateValue = $basalMetabolicRateResult->getResult()->getInUnit("kcal")->getNumericalValue();
			$physicalActivityLevelValue = $physicalActivityLevelResult->getResult()->getNumericalValue();

			$energyValue = $basalMetabolicRateValue * $physicalActivityLevelValue;
			$energy = (new Energy(
				new Amount($energyValue),
				"kcal",
			))->getInUnit($this->getUnits());

			$formula = "
				basalMetabolicRate[{$basalMetabolicRateValue}] * physicalActivityLevel[{$physicalActivityLevelValue}]
				= {$energy->getInUnit("kcal")->getAmount()->getValue()} kcal
				= {$energy->getInUnit("kJ")->getAmount()->getValue()} kJ
			";

			$result->setResult($energy)->setFormula($formula);
		}

		return $result;
	}

	/*****************************************************************************
	 * Total Daily Energy Expenditure - Celkový doporučený denní příjem - TDEE.
	 */
	public function calcWeightGoalQuotient(): AmountMetricResult
	{
		return $this->getStrategy()->calcWeightGoalQuotient($this);
	}

	public function calcWeightGoalEnergyExpenditure(): QuantityMetricResult
	{
		return $this->getStrategy()->calcWeightGoalEnergyExpenditure($this);
	}

	/*****************************************************************************
	 * Reference Daily Intake - Doporučený denní příjem - DDP.
	 */
	public function calcReferenceDailyIntake(): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new ReferenceDailyIntakeMetric);

		$weightGoalEnergyExpenditureResult = $this->calcWeightGoalEnergyExpenditure();
		$result->addErrors($weightGoalEnergyExpenditureResult->getErrors());

		$gender = $this->getGender();
		if (!$gender) {
			$result->addError(new MissingGenderError);
		}

		$referenceDailyIntakeBonusResult = $gender->calcReferenceDailyIntakeBonus($this);
		$result->addErrors($referenceDailyIntakeBonusResult->getErrors());

		if (!$result->hasErrors()) {
			$weightGoalEnergyExpenditureValue = $weightGoalEnergyExpenditureResult->getResult()->getInUnit("kcal")->getNumericalValue();
			$referenceDailyIntakeBonusValue = $referenceDailyIntakeBonusResult->getResult()->getInUnit("kcal")->getNumericalValue();

			$energyValue = $weightGoalEnergyExpenditureValue + $referenceDailyIntakeBonusValue;
			$energy = (new Energy(
				new Amount($energyValue),
				"kcal",
			))->getInUnit($this->getUnits());

			$formula = "
				weightGoalEnergyExpenditure[{$weightGoalEnergyExpenditureValue}] + referenceDailyIntakeBonus[{$referenceDailyIntakeBonusValue}]
				= {$energy->getInUnit("kcal")->getAmount()->getValue()} kcal
				= {$energy->getInUnit("kJ")->getAmount()->getValue()} kJ
			";

			$result->setResult($energy)->setFormula($formula);
		}

		return $result;
	}

	/*****************************************************************************
	 * Živiny.
	 */
	public function calcGoalNutrients(): MetricResultCollection
	{
		$carbs = new QuantityMetricResult(new GoalNutrientsCarbsMetric);
		$fats = new QuantityMetricResult(new GoalNutrientsFatsMetric);
		$proteins = new QuantityMetricResult(new GoalNutrientsProteinsMetric);

		$approach = $this->getDiet()->getApproach();
		if (!$approach) {
			$carbs->addError(new MissingDietApproachError);
			$fats->addError(new MissingDietApproachError);
			$proteins->addError(new MissingDietApproachError);
		} else {
			return $this->getDiet()->getApproach()->calcGoalNutrients($this);
		}

		return new MetricResultCollection([
			$carbs,
			$fats,
			$proteins,
		]);
	}

	/****************************************************************************
	 * Těhotenství.
	 */
	public function calcPregnancyWeek(): AmountMetricResult
	{
		$result = new AmountMetricResult(new PregnancyWeekMetric);

		$pregnancy = $this->getGender()->getPregnancy();
		if (!$pregnancy) {
			$result->addError(new MissingPregnancyError);
		} else {
			$result->setResult(new Amount($pregnancy->getCurrentWeek($this->getReferenceTime())->getIndex()));
		}

		return $result;
	}

	public function calcPregnancyTrimester(): AmountMetricResult
	{
		$result = new AmountMetricResult(new PregnancyTrimesterMetric);

		$pregnancy = $this->getGender()->getPregnancy();
		if (!$pregnancy) {
			$result->addError(new MissingPregnancyError);
		} else {
			$result->setResult(new Amount($pregnancy->getCurrentTrimester($this->getReferenceTime())->getIndex()));
		}

		return $result;
	}

	/****************************************************************************
	 * RestResponse.
	 */
	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		// $res = [];

		// /**************************************************************************
		//  * Input.
		//  */
		// $res["input"]["gender"] = $this->getGender() ? $this->getGender()->getCode() : null;
		// $res["input"]["birthday"] = $this->getBirthday() ? $this->getBirthday()->getTime()->format("Y-m-d") : null;
		// $res["input"]["weight"] = $this->getWeight() ? $this->getWeight()->getAmount()->getValue() : null;
		// $res["input"]["proportions_height"] = $this->getProportions()->getHeight() ? $this->getProportions()->getHeight()->getAmount()->getValue() : null;
		// $res["input"]["proportions_waist"] = $this->getProportions()->getWaist() ? $this->getProportions()->getWaist()->getAmount()->getValue() : null;
		// $res["input"]["proportions_hips"] = $this->getProportions()->getHips() ? $this->getProportions()->getHips()->getAmount()->getValue() : null;
		// $res["input"]["proportions_neck"] = $this->getProportions()->getNeck() ? $this->getProportions()->getNeck()->getAmount()->getValue() : null;
		// $res["input"]["bodyFatPercentage"] = $this->getBodyFatPercentage() ? $this->getBodyFatPercentage()->getValue() : null;
		// $res["input"]["activity"] = $this->getActivity() ? $this->getActivity()->getValue() : null;
		// $res["input"]["sportDurations_lowFrequency"] = $this->getSportDurations()->getLowFrequency() ? $this->getSportDurations()->getLowFrequency()->getAmount()->getValue() : null;
		// $res["input"]["sportDurations_aerobic"] = $this->getSportDurations()->getAerobic() ? $this->getSportDurations()->getAerobic()->getAmount()->getValue() : null;
		// $res["input"]["sportDurations_anaerobic"] = $this->getSportDurations()->getAnaerobic() ? $this->getSportDurations()->getAnaerobic()->getAmount()->getValue() : null;
		// $res["input"]["goal_vector"] = $this->getGoal()->getVector() ? $this->getGoal()->getVector()->getCode() : null;
		// $res["input"]["goal_weight"] = $this->getGoal()->getWeight() ? $this->getGoal()->getWeight()->getAmount()->getValue() : null;
		// $res["input"]["diet_approach"] = $this->getDiet()->getApproach() ? $this->getDiet()->getApproach()->getCode() : null;

		// try {
		// 	$res["input"]["diet_carbs"] = $this->getDiet()->getCarbs() ? $this->getDiet()->getCarbs()->getAmount()->getValue() : null;
		// } catch (\Throwable $e) {
		// 	// Nevermind.
		// }

		/**************************************************************************
		 * Output.
		 */
		$metricResults = (new MetricResultCollection)
			->add($this->calcActiveBodyMassPercentage())
			->add($this->calcActiveBodyMassWeight())
			->add($this->calcBasalMetabolicRate())
			->add($this->calcBasalMetabolicRateStrategy())
			->add($this->calcBodyFatDeviation())
			->add($this->calcBodyFatPercentage())
			->add($this->calcBodyFatWeight())
			->add($this->calcBodyMassIndex())
			->add($this->calcBodyMassIndexDeviation())
			->add($this->calcBodyType())
			->add($this->calcEssentialFatPercentage())
			->add($this->calcEssentialFatWeight())
			->add($this->calcEstimatedFunctionalMass())
			->add($this->calcFatFreeMass())
			->add($this->calcFatOverOptimalPercentage())
			->add($this->calcFatOverOptimalWeight())
			->add($this->calcFatWithinOptimalPercentage())
			->add($this->calcFatWithinOptimalWeight())
			->add($this->calcFitnessLevel())
			->add($this->calcGoalNutrients())
			->add($this->calcHeight())
			->add($this->calcIsOverweight())
			->add($this->calcMaxOptimalWeight())
			->add($this->calcOptimalFatPercentage())
			->add($this->calcOptimalFatWeight())
			->add($this->calcPhysicalActivityLevel())
			->add($this->calcPregnancyTrimester())
			->add($this->calcPregnancyWeek())
			->add($this->calcReferenceDailyIntake())
			->add($this->calcRiskDeviation())
			->add($this->calcSportProteinCoefficient())
			->add($this->calcTotalDailyEnergyExpenditure())
			->add($this->calcWaistHipRatio())
			->add($this->calcWaistHipRatioDeviation())
			->add($this->calcWeight())
			->add($this->calcWeightGoalEnergyExpenditure())
			->add($this->getDiet()->calcDietApproach())
			->add($this->getGoal()->calcGoalDuration())
			->add($this->getGoal()->calcGoalVector())
			->add($this->getGoal()->calcGoalWeight())
			;

		return $metricResults->getRestResponse($request, $options);
	}
}
