<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Approaches\LowEnergyTransition\LowEnergyTransitionDay;
use Fatty\Approaches\LowEnergyTransition\LowEnergyTransitionDayCollection;
use Fatty\Calculator;
use Fatty\Diet;
use Fatty\Energy;

class LowEnergyTransition extends \Fatty\Approach
{
	const CODE = "LOW_ENERGY_TRANSITION";
	const ENERGY_DECREMENT = -150;
	const ENERGY_INCREMENT = 150;
	const ENERGY_MIN = 800;
	const ENERGY_START = 800;
	const ENERGY_UNIT = "kcal";

	/****************************************************************************
	 * Počítat energetický příjem pro jednotlivé dny.
	 * Potřebujeme k tomu:
	 * - datum začátku diety resp. historii WGEE
	 * - ukládat TDEE, WGEE, RDI namísto pouze energy (energy == RDI)
	 * - historii hmotnosti
	 */
	public function calcDays(Calculator $calculator)
	{
		$energyDecrement = new Energy(new Amount(static::ENERGY_DECREMENT), static::ENERGY_UNIT);
		$energyIncrement = new Energy(new Amount(static::ENERGY_INCREMENT), static::ENERGY_UNIT);
		$energyMin = new Energy(new Amount(static::ENERGY_MIN), static::ENERGY_UNIT);
		$energyStart = new Energy(new Amount(static::ENERGY_START), static::ENERGY_UNIT);

		$dateTimeStart = $calculator->getDiet()->getDateTimeStart();
		$dateTimeEnd = new \DateTime("+ 90 day");
		$dateTime = clone $dateTimeStart;

		$collection = new LowEnergyTransitionDayCollection;

		while ($dateTime->format("Ymd") <= $dateTimeEnd->format("Ymd")) {
			// Keto je max.
			// $ketoCalculator = clone $calculator;
			// $ketoCalculator->setDiet(new Diet(new Keto));
			// var_dump($ketoCalculator->calcWeightGoalEnergyExpenditure()->getResult()->getInUnit("kcal")); die;

			$day = new LowEnergyTransitionDay($dateTime);
			$day->setWeight($calculator->getDiet()->getWeightHistory()->getForDate($dateTime)->getWeight());

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
				if ($previousDay->getDateTime()->format("Ymd") == $previousWeightDay->getDateTime()->format("Ymd")
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

			$collection[] = $day;

			$dateTime = (clone $dateTime)->modify("+ 1 day");
		}

		// foreach ($collection as $day) {
		// 	echo $day->getDateTime()->format("Y-m-d") . "\t";
		// 	echo $day->getWeight()->getAmount()->getValue() . "\t";
		// 	echo $day->getWeightGoalEnergyExpenditure()->getAmount()->getValue() . "\t";
		// 	echo $day->getDaysToIncrease() . "\t";
		// 	echo "\n";
		// }
		// die;

		return $collection->sortByOldest();
	}
}
