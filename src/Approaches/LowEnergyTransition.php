<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Approaches\LowEnergyTransition\LowEnergyTransitionDay;
use Fatty\Approaches\LowEnergyTransition\LowEnergyTransitionDayCollection;
use Fatty\Calculator;
use Fatty\Energy;
use Fatty\Genders\Female;
use Fatty\Metrics\AmountWithUnitMetric;
use Fatty\Nutrients;
use Fatty\Nutrients\Fats;

class LowEnergyTransition extends \Fatty\Approach
{
	const CARBS_DEFAULT = 40;
	const CARBS_MAX = 50;
	const CARBS_MIN = 0;
	const CODE = "LOW_ENERGY_TRANSITION";
	const ENERGY_DECREMENT = -150;
	const ENERGY_INCREMENT = 150;
	const ENERGY_MIN = 800;
	const ENERGY_START = 800;
	const ENERGY_UNIT = "kcal";
	// const PROTEINS_DEFAULT = 82;

	public function calcDays(Calculator $calculator)
	{
		$energyDecrement = new Energy(new Amount(static::ENERGY_DECREMENT), static::ENERGY_UNIT);
		$energyIncrement = new Energy(new Amount(static::ENERGY_INCREMENT), static::ENERGY_UNIT);
		$energyMin = new Energy(new Amount(static::ENERGY_MIN), static::ENERGY_UNIT);
		$energyStart = new Energy(new Amount(static::ENERGY_START), static::ENERGY_UNIT);

		$dateTimeStart = $calculator->getDiet()->getDateTimeStart();
		$dateTimeEnd = $calculator->getReferenceDate();
		$dateTime = clone $dateTimeStart;

		$collection = new LowEnergyTransitionDayCollection;

		while ($dateTime->format("Ymd") <= $dateTimeEnd->format("Ymd")) {
			$day = new LowEnergyTransitionDay($dateTime);
			$day->setWeight($calculator->getWeightHistory()->getForDate($dateTime)->getWeight());

			if ($dateTimeStart->format("Ymd") == $dateTime->format("Ymd")) {
				// First day.
				$day->setWeightGoalEnergyExpenditure($energyStart);
				$day->setDaysToIncrease(7);
			} else {
				// Other days.
				$previousDay = $collection->filterByDate((clone $dateTime)->modify("- 1 day"))[0];
				$previousWeightDay = $collection->getPreviousWeightDay($dateTime, $day->getWeight());

				$day->setDaysToIncrease($previousDay->getDaysToIncrease() - 1);

				// Jedná se o den, kdy se změnila hmotnost a došlo k nárůstu hmotnosti?
				if ($previousDay
					&& $previousWeightDay
					&& $previousDay->getDateTime()->format("Ymd") == $previousWeightDay->getDateTime()->format("Ymd")
					&& $day->getWeight()->getInUnit("g")->getAmount()->getValue() > $previousWeightDay->getWeight()->getInUnit("g")->getAmount()->getValue()) {
					// Snížit energii.
					$decreasedEnergy = $previousDay->getWeightGoalEnergyExpenditure()->modify($energyDecrement);
					$day->setWeightGoalEnergyExpenditure($decreasedEnergy);

					// Nastavit na 14 dní.
					$day->setDaysToIncrease(14);

				// Jde o den, kdy by se mělo navyšovat?
				} elseif ($day->getDaysToIncrease() <= 0) {
					// Navýšit energii.
					$increasedEnergy = $previousDay->getWeightGoalEnergyExpenditure()->modify($energyIncrement);
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
			$ketoCalculator->getDiet()->setApproach(new \Fatty\Approaches\Keto);
			$ketoWeightGoalEnergyExpenditure = $ketoCalculator->calcWeightGoalEnergyExpenditure()->getResult();

			if ($day->getWeightGoalEnergyExpenditure()->getInUnit(static::ENERGY_UNIT)->getAmount()->getValue() > $ketoWeightGoalEnergyExpenditure->getInUnit(static::ENERGY_UNIT)->getAmount()->getValue()) {
				$day->setWeightGoalEnergyExpenditure($ketoWeightGoalEnergyExpenditure);
			}

			$collection[] = $day;

			$dateTime = (clone $dateTime)->modify("+ 1 day");
		}

		return $collection->sortByOldest();
	}

	public function getWeightGoalEnergyExpenditure(Calculator $calculator): Energy
	{
		return $this->calcDays($calculator)->filterByDate($calculator->getReferenceDate())[0]->getWeightGoalEnergyExpenditure();
	}

	public function calcWeightGoalEnergyExpenditure(Calculator $calculator): AmountWithUnitMetric
	{
		$result = $this->getWeightGoalEnergyExpenditure($calculator)->getInUnit($calculator->getUnits());

		return new AmountWithUnitMetric("weightGoalEnergyExpenditure", $result);
	}

	public function getGoalNutrients(Calculator $calculator): Nutrients
	{
		$nutrients = new Nutrients;
		$nutrients->setProteins($this->calcGoalNutrientProteins($calculator)->getResult());

		$wgee = $calculator->calcWeightGoalEnergyExpenditure();

		// TODO
		if (false && $calculator->getGender() instanceof Female && $calculator->getGender()->isPregnant()) {
		// TODO
		} elseif (false && $calculator->getGender() instanceof Female && $calculator->getGender()->isBreastfeeding()) {
		} else {
			$dietCarbs = $calculator->getDiet()->getCarbs();
			$nutrients->setCarbs($dietCarbs);
			$nutrients->setFats(
				Fats::createFromEnergy(
					new Energy(
						new Amount(
							$wgee->getResult()->getInBaseUnit()->getAmount()->getValue() - $nutrients->getEnergy()->getInBaseUnit()->getAmount()->getValue()
						),
						Energy::getBaseUnit(),
					),
				),
			);
		}
		return $nutrients;
	}
}
