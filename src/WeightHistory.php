<?php

namespace Fatty;

class WeightHistory extends \ArrayObject
{
	public function filterByDate(\DateTime $dateTime): WeightHistory
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (DateTimeWeight $dateTimeWeight) use ($dateTime) {
			return $dateTimeWeight->getDateTime()->format("Ymd") == $dateTime->format("Ymd");
		})));
	}

	public function filterForDate(\DateTime $dateTime): WeightHistory
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (DateTimeWeight $dateTimeWeight) use ($dateTime) {
			return $dateTimeWeight->getDateTime()->format("Ymd") <= $dateTime->format("Ymd");
		})));
	}

	public function sortByNewest(): WeightHistory
	{
		$array = $this->getArrayCopy();
		usort($array, function (DateTimeWeight $a, DateTimeWeight $b) {
			return $a->getDateTime()->format("Ymd") < $b->getDateTime()->format("Ymd") ? 1 : -1;
		});

		return new static($array);
	}

	public function getForDate(\DateTime $dateTime): ?DateTimeWeight
	{
		return $this->filterForDate($dateTime)->sortByNewest()[0] ?? null;
	}
}
