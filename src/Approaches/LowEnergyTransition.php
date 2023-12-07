<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Approaches\LowEnergyTransition\LowEnergyTransitionDay;
use Fatty\Approaches\LowEnergyTransition\LowEnergyTransitionDayCollection;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Metrics\GoalNutrientsCarbsMetric;
use Fatty\Metrics\GoalNutrientsFatsMetric;
use Fatty\Metrics\GoalNutrientsProteinsMetric;
use Fatty\Metrics\MetricResultCollection;
use Fatty\Metrics\QuantityMetricResult;
use Fatty\Metrics\WeightGoalEnergyExpenditureMetric;
use Fatty\Nutrients;
use Fatty\Nutrients\Fats;
use Fatty\Nutrients\Proteins;

class LowEnergyTransition extends \Fatty\Approach
{
	const CARBS_DEFAULT = 40;
	const CARBS_MAX = 50;
	const CARBS_MIN = 0;
	const CODE = "LOW_ENERGY_TRANSITION";
	const ENERGY_DECREMENT = -150;
	const ENERGY_DEFAULT = 800;
	const ENERGY_INCREMENT = 150;
	const ENERGY_MIN = 800;
	const PROTEINS_DEFAULT = 82;

	public function calcDays(Calculator $calculator)
	{
		$energyDecrement = new Energy(new Amount(static::ENERGY_DECREMENT), static::ENERGY_UNIT);
		$energyIncrement = new Energy(new Amount(static::ENERGY_INCREMENT), static::ENERGY_UNIT);
		$energyMin = new Energy(new Amount(static::ENERGY_MIN), static::ENERGY_UNIT);
		$energyStart = new Energy(new Amount(static::ENERGY_DEFAULT), static::ENERGY_UNIT);

		$timeStart = $calculator->getDiet()->getTimeStart();
		$timeEnd = $calculator->getReferenceTime();
		$time = clone $timeStart;

		$collection = new LowEnergyTransitionDayCollection;

		while ($time->format("Ymd") <= $timeEnd->format("Ymd")) {
			$day = new LowEnergyTransitionDay($time);
			$day->setWeight($calculator->getWeights()->getForDate($time)->getWeight());

			if ($timeStart->format("Ymd") == $time->format("Ymd")) {
				// First day.
				$day->setWeightGoalEnergyExpenditure($energyStart);
				$day->setDaysToIncrease(7);
			} else {
				// Other days.
				$previousDay = $collection->filterByDate((clone $time)->modify("- 1 day"))[0];
				$previousWeightDay = $collection->getPreviousWeightDay($time, $day->getWeight());

				$day->setDaysToIncrease($previousDay->getDaysToIncrease() - 1);

				// Jedná se o den, kdy se změnila hmotnost a došlo k nárůstu hmotnosti?
				if ($previousDay
					&& $previousWeightDay
					&& $previousDay->getTime()->format("Ymd") == $previousWeightDay->getTime()->format("Ymd")
					&& $day->getWeight()->getInUnit("g")->getAmount()->getValue() > $previousWeightDay->getWeight()->getInUnit("g")->getAmount()->getValue()) {
					// Snížit energii.
					$decreasedEnergy = $previousDay->getWeightGoalEnergyExpenditure()->getModified($energyDecrement);
					$day->setWeightGoalEnergyExpenditure($decreasedEnergy);

					// Nastavit na 14 dní.
					$day->setDaysToIncrease(14);

				// Jde o den, kdy by se mělo navyšovat?
				} elseif ($day->getDaysToIncrease() <= 0) {
					// Navýšit energii.
					$increasedEnergy = $previousDay->getWeightGoalEnergyExpenditure()->getModified($energyIncrement);
					$day->setWeightGoalEnergyExpenditure($increasedEnergy);

					// Prodloužit na dalších 7 dní.
					$day->setDaysToIncrease(7);

				// Ostatní dny.
				} else {
					// Zachovat energii.
					$day->setWeightGoalEnergyExpenditure($previousDay->getWeightGoalEnergyExpenditure());
				}
			}

			// Pokud je energie menší, než minimum, nastavit na minimum.
			if ($day->getWeightGoalEnergyExpenditure()->getInUnit(static::ENERGY_UNIT)->getAmount()->getValue() < $energyMin->getInUnit(static::ENERGY_UNIT)->getAmount()->getValue()) {
				$day->setWeightGoalEnergyExpenditure($energyMin);
			}

			// Pokud jsme dosáhli KETO úrovní, jet podle KETO.
			$ketoCalculator = clone $calculator;
			$ketoCalculator->setDiet(new \Fatty\Diet(new \Fatty\Approaches\Keto));
			$ketoWeightGoalEnergyExpenditure = $ketoCalculator->calcWeightGoalEnergyExpenditure()->getResult();

			if ($day->getWeightGoalEnergyExpenditure()->getInUnit(static::ENERGY_UNIT)->getAmount()->getValue() > $ketoWeightGoalEnergyExpenditure->getInUnit(static::ENERGY_UNIT)->getNumericalValue()) {
				$day->setWeightGoalEnergyExpenditure($ketoWeightGoalEnergyExpenditure);
			}

			$collection[] = $day;

			$time = (clone $time)->modify("+ 1 day");
		}

		return $collection->sortByOldest();
	}

	public function getWeightGoalEnergyExpenditure(Calculator $calculator): Energy
	{
		return $this->calcDays($calculator)->filterByDate($calculator->getReferenceTime())[0]->getWeightGoalEnergyExpenditure();
	}

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): QuantityMetricResult
	{
		$result = new QuantityMetricResult(new WeightGoalEnergyExpenditureMetric);
		$result->setResult($this->getWeightGoalEnergyExpenditure($calculator)->getInUnit($calculator->getUnits()));

		return $result;
	}

	public function calcGoalNutrients(Calculator $calculator): MetricResultCollection
	{
		$carbsResult = new QuantityMetricResult(new GoalNutrientsCarbsMetric);
		$fatsResult = new QuantityMetricResult(new GoalNutrientsFatsMetric);
		$proteinsResult = new QuantityMetricResult(new GoalNutrientsProteinsMetric);

		$rdiResult = $calculator->calcReferenceDailyIntake();
		$proteinsResult->addErrors($rdiResult->getErrors());
		$fatsResult->addErrors($rdiResult->getErrors());

		$ketoCalculator = clone $calculator;
		$ketoCalculator->setDiet(new \Fatty\Diet(new \Fatty\Approaches\Keto));
		// var_dump($ketoCalculator);
		$ketoRdiResult = $ketoCalculator->calcReferenceDailyIntake();
		$proteinsResult->addErrors($ketoRdiResult->getErrors());

		if (!$carbsResult->hasErrors() && !$fatsResult->hasErrors() && !$proteinsResult->hasErrors()) {
			$nutrients = new Nutrients;

			// Bílkoviny.
			$startEnergyValue = $this->getDefaultEnergy()->getInUnit(Energy::getBaseUnit())->getNumericalValue();
			// var_dump($startEnergyValue);
			$goalEnergyValue = $ketoRdiResult->getResult()->getInUnit(Energy::getBaseUnit())->getNumericalValue();
			// var_dump($goalEnergyValue);
			$currentEnergyValue = $rdiResult->getResult()->getInUnit(Energy::getBaseUnit())->getNumericalValue();
			// var_dump($currentEnergyValue);
			$progress = ($currentEnergyValue - $startEnergyValue) / ($goalEnergyValue - $startEnergyValue);
			// var_dump($progress);

			$startProteinsValue = $this->getDefaultProteins()->getInUnit("g")->getNumericalValue();
			// var_dump($startProteinsValue);die;
			// var_dump($ketoCalculator);
			// var_dump($ketoCalculator->calcGoalNutrients());
			// var_dump($ketoCalculator->calcGoalNutrients()->filterByCode("GOAL_NUTRIENTS_PROTEINS"));
			$goalProteinsValue = $ketoCalculator->calcGoalNutrients()->filterByCode("GOAL_NUTRIENTS_PROTEINS")->getFirst()->getResult()->getInUnit("g")->getNumericalValue();
			// var_dump($ketoCalculator);
			// var_dump($goalProteinsValue);
			$addProteins = ($goalProteinsValue - $startProteinsValue) * $progress;
			$proteins = new Proteins(new Amount($startProteinsValue + $addProteins), "g");
			$nutrients->setProteins($proteins);

			// Sacharidy.
			$carbs = $calculator->getDiet()->getCarbs();
			$nutrients->setCarbs($carbs);

			// Tuky.
			$fats = Fats::createFromEnergy(
				new Energy(
					new Amount(
						$rdiResult->getResult()->getInUnit(Energy::getBaseUnit())->getNumericalValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
					),
					Energy::getBaseUnit(),
				),
			);
			$nutrients->setFats($fats);

			$carbsResult->setResult($nutrients->getCarbs());
			$fatsResult->setResult($nutrients->getFats());
			$proteinsResult->setResult($nutrients->getProteins());
		}

		return new MetricResultCollection([
			$carbsResult,
			$fatsResult,
			$proteinsResult,
		]);
	}
}
