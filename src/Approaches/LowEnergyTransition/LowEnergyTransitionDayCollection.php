<?php

namespace Fatty\Approaches\LowEnergyTransition;

use Fatty\Weight;

class LowEnergyTransitionDayCollection extends \ArrayObject
{
	public function filterByDate(\DateTime $dateTime): LowEnergyTransitionDayCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (LowEnergyTransitionDay $day) use ($dateTime) {
			return $day->getDateTime()->format("Ymd") == $dateTime->format("Ymd");
		})));
	}

	public function getPreviousWeightDay(\DateTime $dateTime, Weight $weight): ?LowEnergyTransitionDay
	{
		foreach ($this->sortByNewest() as $lowEnergyTransitionDay) {
			if ($lowEnergyTransitionDay->getDateTime()->format("Ymd") < $dateTime->format("Ymd")) {
				if ($lowEnergyTransitionDay->getWeight()->getInUnit("g")->getAmount()->getValue() != $weight->getInUnit("g")->getAmount()->getValue()) {
					return $lowEnergyTransitionDay;
				}
			}
		}

		return null;
	}

	public function sortByOldest(): LowEnergyTransitionDayCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function (LowEnergyTransitionDay $a, LowEnergyTransitionDay $b) {
			return ($a->getDateTime()->format("Ymd") > $b->getDateTime()->format("Ymd")) ? 1 : -1;
		});

		return new static($array);
	}

	public function sortByNewest(): LowEnergyTransitionDayCollection
	{
		return new static(array_reverse($this->sortByOldest()->getArrayCopy()));
	}
}
