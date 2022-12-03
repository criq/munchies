<?php

namespace Fatty\Approaches\LowEnergyTransition;

use Fatty\Weight;
use Katu\Tools\Calendar\Time;

class LowEnergyTransitionDayCollection extends \ArrayObject
{
	public function filterByDate(Time $time): LowEnergyTransitionDayCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (LowEnergyTransitionDay $day) use ($time) {
			return $day->getTime()->format("Ymd") == $time->format("Ymd");
		})));
	}

	public function filterBeforeDate(Time $time): LowEnergyTransitionDayCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (LowEnergyTransitionDay $day) use ($time) {
			return $day->getTime()->format("Ymd") < $time->format("Ymd");
		})));
	}

	public function filterDifferentWeight(Weight $weight): ?LowEnergyTransitionDayCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (LowEnergyTransitionDay $day) use ($weight) {
			return $day->getWeight()->getInUnit("g")->getAmount()->getValue() != $weight->getInUnit("g")->getAmount()->getValue();
		})));
	}

	public function getPreviousWeightDay(Time $time, Weight $weight): ?LowEnergyTransitionDay
	{
		return $this->filterBeforeDate($time)->filterDifferentWeight($weight)->sortByNewest()[0] ?? null;
	}

	public function sortByOldest(): LowEnergyTransitionDayCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function (LowEnergyTransitionDay $a, LowEnergyTransitionDay $b) {
			return ($a->getTime()->format("Ymd") > $b->getTime()->format("Ymd")) ? 1 : -1;
		});

		return new static($array);
	}

	public function sortByNewest(): LowEnergyTransitionDayCollection
	{
		return new static(array_reverse($this->sortByOldest()->getArrayCopy()));
	}
}
