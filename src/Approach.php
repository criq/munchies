<?php

namespace Fatty;

use Fatty\Metrics\AmountWithUnitMetric;
use Fatty\Nutrients\Carbs;
use Fatty\Nutrients\Fats;
use Fatty\Nutrients\Proteins;
use Fatty\SportDurations\Aerobic;
use Fatty\SportDurations\Anaerobic;
use Fatty\SportDurations\LowFrequency;

abstract class Approach
{
	const CARBS_DEFAULT = null;
	const CARBS_MAX = null;
	const CARBS_MIN = null;
	const CODE = null;
	const ENERGY_DEFAULT = null;
	const FATS_DEFAULT = null;
	const LABEL_DECLINATED = null;
	const PROTEINS_DEFAULT = null;

	abstract public function getGoalNutrients(Calculator $calculator): Nutrients;

	public function __toString(): string
	{
		return $this->getDeclinatedLabel();
	}

	public static function createFromCode(string $value): ?Approach
	{
		try {
			$class = "Fatty\\Approaches\\" . ucfirst($value);

			return new $class;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public static function getAvailableClasses(): array
	{
		return [
			new \Fatty\Approaches\Keto,
			new \Fatty\Approaches\LowCarb,
			new \Fatty\Approaches\LowEnergy,
			new \Fatty\Approaches\LowEnergyTransition,
			new \Fatty\Approaches\Mediterranean,
			new \Fatty\Approaches\Standard,
		];
	}

	public function getCode(): string
	{
		return (string)static::CODE;
	}

	public function getDeclinatedLabel(): string
	{
		return (string)static::LABEL_DECLINATED;
	}

	public function getDefaultEnergy(): ?Energy
	{
		return static::ENERGY_DEFAULT ? new Energy(new Amount((float)static::ENERGY_DEFAULT), "kcal") : null;
	}

	public function getDefaultCarbs(): ?Carbs
	{
		return static::CARBS_DEFAULT ? new Carbs(new Amount((float)static::CARBS_DEFAULT), "g") : null;
	}

	public function getMinCarbs(): ?Carbs
	{
		return static::CARBS_MIN ? new Carbs(new Amount((float)static::CARBS_MIN), "g") : null;
	}

	public function getMaxCarbs(): ?Carbs
	{
		return static::CARBS_MAX ? new Carbs(new Amount((float)static::CARBS_MAX), "g") : null;
	}

	public function getDefaultFats(): ?Fats
	{
		return static::FATS_DEFAULT ? new Fats(new Amount((float)static::FATS_DEFAULT), "g") : null;
	}

	public function getDefaultProteins(): ?Proteins
	{
		return static::PROTEINS_DEFAULT ? new Proteins(new Amount((float)static::PROTEINS_DEFAULT), "g") : null;
	}

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): AmountWithUnitMetric
	{
		if (!$calculator->getGoal()->getVector()) {
			throw new \Fatty\Exceptions\MissingGoalVectorException;
		}

		$totalDailyEnergyExpenditure = $calculator->calcTotalDailyEnergyExpenditure()->getResult();
		$totalDailyEnergyExpenditureValue = $totalDailyEnergyExpenditure->getAmount()->getValue();
		$tdeeQuotientValue = $calculator->getGoal()->getVector()->calcTdeeQuotient($calculator)->getResult()->getValue();

		$result = (new Energy(
			new Amount($totalDailyEnergyExpenditureValue * $tdeeQuotientValue),
			$totalDailyEnergyExpenditure->getUnit(),
		))->getInUnit($calculator->getUnits());

		$formula = "totalDailyEnergyExpenditure[" . $totalDailyEnergyExpenditure . "] * weightGoalQuotient[" . $tdeeQuotientValue . "] = " . $result;

		return new AmountWithUnitMetric("weightGoalEnergyExpenditure", $result, $formula);
	}

	public function calcGoalNutrientProteins(Calculator $calculator): AmountWithUnitMetric
	{
		// Velká fyzická zátěž.
		if ($calculator->getSportDurations()->getTotalDuration() > 60 || $calculator->calcPhysicalActivityLevel()->getResult()->getValue() >= 1.9) {
			// Muž.
			if ($calculator->getGender() instanceof Genders\Male) {
				if ($calculator->calcFatOverOptimalWeight()->filterByName("fatOverOptimalWeightMax")[0]->getResult()->getInUnit("kg")->getAmount()) {
					$optimalWeight = $calculator->getOptimalWeight()->getMax();
				} else {
					$optimalWeight = $calculator->getWeight();
				}

				$matrix = [
					"fit"   => [1.5, 2.2, 1.8],
					"unfit" => [1.5, 2,   1.7],
				];
				$matrixSet = ($calculator->calcBodyFatPercentage()->getResult()->getValue() > .19 || $calculator->calcBodyMassIndex()->getResult()->getValue() > 25) ? "unfit" : "fit";

				$optimalNutrients = [];
				foreach ($calculator->getSportDurations()->getMaxDurations() as $sportDuration) {
					if ($sportDuration instanceof LowFrequency) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][0];
					} elseif ($sportDuration instanceof Anaerobic) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][1];
					} elseif ($sportDuration instanceof Aerobic) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][2];
					}
				}

				if ($calculator->calcPhysicalActivityLevel()->getResult()->getValue() >= 1.9) {
					$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][1];
				}

				$proteins = new Nutrients\Proteins(new Amount(max($optimalNutrients)), "g");

			// Žena.
			} elseif ($calculator->getGender() instanceof Genders\Female) {
				if (false && $calculator->getGender()->isPregnant()) { // FALSE - opravit
				} else {
					if ($calculator->calcFatOverOptimalWeight()->filterByName("fatOverOptimalWeightMax")[0]->getResult()->getInUnit("kg")->getAmount()) {
						$optimalWeight = $calculator->getOptimalWeight()->getMax();
					} else {
						$optimalWeight = $calculator->getWeight();
					}

					$matrix = [
						"fit"   => [1.4, 1.8, 1.6],
						"unfit" => [1.5, 1.8, 1.8],
					];
					$matrixSet = ($calculator->calcBodyFatPercentage()->getResult()->getValue() > .25 || $calculator->calcBodyMassIndex()->getResult()->getValue() > 25) ? "unfit" : "fit";

					$optimalNutrients = [];
					foreach ($calculator->getSportDurations()->getMaxDurations() as $sportDuration) {
						if ($sportDuration instanceof LowFrequency) {
							$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][0];
						} elseif ($sportDuration instanceof Anaerobic) {
							$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][1];
						} elseif ($sportDuration instanceof Aerobic) {
							$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][2];
						}
					}

					if ($calculator->calcPhysicalActivityLevel()->getResult()->getValue() >= 1.9) {
						$optimalNutrients[] = $optimalWeight->getAmount()->getValue() * $matrix[$matrixSet][1];
					}

					$proteins = new Nutrients\Proteins(new Amount(max($optimalNutrients)), "g");

					if ($calculator->getGender()->isPregnant() || $calculator->getGender()->isBreastfeeding()) {
						$proteins = new Nutrients\Proteins(new Amount($proteins->getInUnit("g")->getAmount()->getValue() + 20), "g");
					}
				}
			}

		// Normální fyzická zátěž.
		} else {
			if ($calculator->getGender() instanceof Genders\Female && ($calculator->getGender()->isPregnant() || $calculator->getGender()->isBreastfeeding())) {
				$res = new Nutrients\Proteins(min(($calculator->getWeight()->getInUnit("kg")->getAmount()->getValue() * 1.4) + 20, 90), "g");
			} else {
				if ($calculator->getGender() instanceof Genders\Male) {
					if ($calculator->calcFatOverOptimalWeight()->filterByName("fatOverOptimalWeightMax")[0]->getResult()->getInUnit("kg")->getAmount()) {
						$res = new Nutrients\Proteins(new Amount($calculator->getOptimalWeight()->getMax()->getInUnit("kg")->getAmount()->getValue() * 1.5), "g");
					} else {
						$res = new Nutrients\Proteins(new Amount($calculator->getWeight()->getInUnit("kg")->getAmount()->getValue() * 1.5), "g");
					}
				} elseif ($calculator->getGender() instanceof Genders\Female) {
					if ($calculator->calcFatOverOptimalWeight()->filterByName("fatOverOptimalWeightMax")[0]->getResult()->getInUnit("kg")->getAmount()) {
						$res = new Nutrients\Proteins(new Amount($calculator->getOptimalWeight()->getMax()->getInUnit("kg")->getAmount()->getValue() * 1.4), "g");
					} else {
						$res = new Nutrients\Proteins(new Amount($calculator->getWeight()->getInUnit("kg")->getAmount()->getValue() * 1.4), "g");
					}
				}
			}
		}

		return new \Fatty\Metrics\AmountWithUnitMetric("goalNutrientsProteins", $res);
	}

	public function calcGoalNutrients(Calculator $calculator): MetricCollection
	{
		$dietApproach = $calculator->getDiet()->getApproach();
		if (!$dietApproach) {
			throw new \Fatty\Exceptions\MissingDietApproachException;
		}

		$nutrients = $this->getGoalNutrients($calculator);
		var_dump($nutrients);die;

		return new MetricCollection([
			new AmountWithUnitMetric("goalNutrientsCarbs", $nutrients->getCarbs()),
			new AmountWithUnitMetric("goalNutrientsFats", $nutrients->getFats()),
			new AmountWithUnitMetric("goalNutrientsProteins", $nutrients->getProteins()),
		]);
	}
}
