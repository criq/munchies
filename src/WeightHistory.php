<?php

namespace Fatty;

use Katu\Tools\Calendar\Time;

class WeightHistory extends \ArrayObject
{
	public function filterByDate(Time $time): WeightHistory
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (TimeWeight $timeWeight) use ($time) {
			return $timeWeight->getTime()->format("Ymd") == $time->format("Ymd");
		})));
	}

	public function filterForDate(Time $time): WeightHistory
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (TimeWeight $timeWeight) use ($time) {
			return $timeWeight->getTime()->format("Ymd") <= $time->format("Ymd");
		})));
	}

	public function sortByNewest(): WeightHistory
	{
		$array = $this->getArrayCopy();
		usort($array, function (TimeWeight $a, TimeWeight $b) {
			return $a->getTime()->format("Ymd") < $b->getTime()->format("Ymd") ? 1 : -1;
		});

		return new static($array);
	}

	public function getForDate(Time $time): ?TimeWeight
	{
		return $this->filterForDate($time)->sortByNewest()[0] ?? null;
	}
}
