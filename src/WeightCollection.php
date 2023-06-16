<?php

namespace Fatty;

use Katu\Tools\Calendar\Time;

class WeightCollection extends \ArrayObject
{
	public function filterByDate(Time $time): WeightCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (TimeWeight $timeWeight) use ($time) {
			return $timeWeight->getTime()->format("Ymd") == $time->format("Ymd");
		})));
	}

	public function filterForDate(Time $time): WeightCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (TimeWeight $timeWeight) use ($time) {
			return $timeWeight->getTime()->format("Ymd") <= $time->format("Ymd");
		})));
	}

	public function sort(): WeightCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function (TimeWeight $a, TimeWeight $b) {
			return $a->getTime()->format("Ymd") > $b->getTime()->format("Ymd") ? 1 : -1;
		});

		return new static($array);
	}

	public function getReversed(): WeightCollection
	{
		return new static(array_reverse($this->getArrayCopy()));
	}

	public function getFirst(): ?TimeWeight
	{
		return array_values($this->getArrayCopy())[0] ?? null;
	}

	public function getLast(): ?TimeWeight
	{
		return $this[count($this) - 1] ?? null;
	}

	public function getForDate(Time $time): ?TimeWeight
	{
		return $this->filterForDate($time)->sort()->getLast();
	}

	public function getTrend()
	{
		try {
			$weights = $this->sort()->getReversed();

			if ($weights[0] ?? null && $weights[0] ?? null) {
				$last = $weights[0]->getWeight()->getAmount();
				$prev = $weights[1]->getWeight()->getAmount();

				return ($last == $prev) ? 0 : ($last > $prev ? 1 : -1);
			}
		} catch (\Throwable $e) {
			// Nevermind.
		}

		return null;
	}
}
