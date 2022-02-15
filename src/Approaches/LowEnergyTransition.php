<?php

namespace Fatty\Approaches;

use Fatty\Amount;
use Fatty\Approaches\LowEnergyTransition\LowEnergyTransitionDay;
use Fatty\Approaches\LowEnergyTransition\LowEnergyTransitionDayCollection;
use Fatty\Calculator;
use Fatty\Energy;

class LowEnergyTransition extends \Fatty\Approach
{
	const CODE = "LOW_ENERGY_TRANSITION";
	const ENERGY_DEFAULT = 800;
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
		$dateTimeStart = $calculator->getDiet()->getDateTimeStart();
		$dateTimeEnd = new \App\Classes\DateTime("+ 30 day");
		$dateTime = clone $dateTimeStart;

		// $startEnergy = new Energy(new Amount(static::ENERGY_DEFAULT), static::ENERGY_UNIT);
		// $holdEnergyDays;
		// daysSinceEnergyChange
		// nepotřebuju vědět, co je za den, jen počet dní od poslední úpravy energie

		$collection = new LowEnergyTransitionDayCollection;

		while ($dateTime->format("Ymd") <= $dateTimeEnd->format("Ymd")) {
			$day = new LowEnergyTransitionDay($dateTime);
			$day->setWeight($calculator->getDiet()->getWeightHistory()->getForDate($dateTime)->getWeight());

			if ($dateTimeStart->format("Ymd") == $dateTime->format("Ymd")) {
				// First day.
				$day->setWeightGoalEnergyExpenditure(new Energy(new Amount(static::ENERGY_DEFAULT), static::ENERGY_UNIT));
				$day->setDaysToIncrease(7);
			} else {
				// Other days.
				$previousDay = $collection->filterByDate((clone $dateTime)->modify("- 1 day"))[0];
				$previousWeightDay = $collection->getPreviousWeightDay($dateTime, $day->getWeight());

				var_dump($previousDay, $previousWeightDay);
				die;

				// Je den, kdy se změnila hmotnost?
				// PreviousDay == PreviousWeightDay => ANO

				// ANO

						// změnila se hmotnost nahoru?
						// ANO

								// Návrat k poslední energii, po které došlo k polklesu hmotnosti
								// 2 týdny vydržet

						// NE

				// NE >

				var_dump($previousDay);
				var_dump($previousWeightDay);

				$daysToIncrease = $previousDay->getDaysToIncrease() - 1;
				$day->setDaysToIncrease($daysToIncrease);
			}




			// po týdnu od poslední změny WGEE chci navýšit WGEE
			// - jsme v chráněném období (2 týdny po snížení)? => DAYS_TO_INCREASE (7, 14...)

			// var_dump($day, $dateTime);
			// var_dump($calculator->getDiet());

			$collection[] = $day;

			$dateTime = (clone $dateTime)->modify("+ 1 day");
		}

		var_dump($collection);
	}
}
