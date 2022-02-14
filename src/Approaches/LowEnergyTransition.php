<?php

namespace Fatty\Approaches;

use Fatty\Amount;
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
	 * - datum začátku diety
	 * - historii hmotnosti
	 */
	public function calcDays(Calculator $calculator)
	{
		$dateTimeStart = $calculator->getDiet()->getDateTimeStart();
		$dateTimeEnd = new \App\Classes\DateTime("+ 1 day");
		$dateTime = clone $dateTimeStart;

		// $startEnergy = new Energy(new Amount(static::ENERGY_DEFAULT), static::ENERGY_UNIT);
		// $holdEnergyDays;

		while ($dateTime < $dateTimeEnd) {
			$daysFromStart = $dateTime->diff($dateTimeStart)->days;
			$weeksFromStart = $daysFromStart / 7;
			$isChangeDay = is_int($weeksFromStart);

			var_dump($isChangeDay);

			$dateTime->modify("+ 1 day");
		}
	}
}
