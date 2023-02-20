<?php

namespace Fatty;

use Katu\Tools\Calendar\Time;

class WeightHistory extends \ArrayObject
{
	public function filterByDate(Time $time): WeightHistory
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (DateTimeWeight $dateTimeWeight) use ($time) {
			return $dateTimeWeight->getTime()->format("Ymd") == $time->format("Ymd");
		})));
	}

	public function filterForDate(Time $time): WeightHistory
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (DateTimeWeight $dateTimeWeight) use ($time) {
			return $dateTimeWeight->getTime()->format("Ymd") <= $time->format("Ymd");
		})));
	}

	public function sortByNewest(): WeightHistory
	{
		$array = $this->getArrayCopy();
		usort($array, function (DateTimeWeight $a, DateTimeWeight $b) {
			return $a->getTime()->format("Ymd") < $b->getTime()->format("Ymd") ? 1 : -1;
		});

		return new static($array);
	}

	public function getForDate(Time $time): ?DateTimeWeight
	{
		return $this->filterForDate($time)->sortByNewest()[0] ?? null;
	}
}
