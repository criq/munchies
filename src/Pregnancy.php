<?php

namespace Fatty;

use Fatty\Pregnancy\Week;
use Fatty\Pregnancy\WeekCollection;
use Fatty\Pregnancy\Trimester;
use Fatty\Pregnancy\TrimesterCollection;
use Katu\Tools\Calendar\Interval;
use Katu\Tools\Calendar\Time;

class Pregnancy
{
	protected $childbirthDate;
	protected $numberOfChildren = 1;
	protected $weightBeforePregnancy;

	public function __construct(Time $childbirthDate)
	{
		$this->setChildbirthDate($childbirthDate);
	}

	public function setChildbirthDate(Time $time): Pregnancy
	{
		$this->childbirthDate = $time;

		return $this;
	}

	public function getChildbirthDate(): Time
	{
		return $this->childbirthDate;
	}

	public function getConceptionDate(): Time
	{
		return (clone $this->getChildbirthDate())->modify("- 280 days")->setTime(0, 0, 0, 0);
	}

	public function getInterval(): Interval
	{
		return new Interval($this->getConceptionDate(), $this->getChildbirthDate());
	}

	public function setWeightBeforePregnancy(?Weight $weight): Pregnancy
	{
		$this->weightBeforePregnancy = $weight;

		return $this;
	}

	public function getWeightBeforePregnancy(): ?Weight
	{
		return $this->weightBeforePregnancy;
	}

	public function setNumberOfChildren(int $numberOfChildren): Pregnancy
	{
		$this->numberOfChildren = $numberOfChildren;

		return $this;
	}

	public function getNumberOfChildren(): int
	{
		return $this->numberOfChildren;
	}

	public function getIsPregnant(Time $referenceTime): bool
	{
		return $this->getInterval()->fitsTime($referenceTime);
	}

	public function getWeeks(): WeekCollection
	{
		$res = new WeekCollection;
		for ($index = 1; $index <= 42; $index++) {
			$modifier = ($index - 1) * 7;
			$start = (clone $this->getConceptionDate())->modify("+ {$modifier} days");
			$end = (clone $start)->modify("+ 7 days");

			$res[] = new Week($index, new Interval($start, $end));
		}

		return $res;
	}

	public function getTrimesters(): TrimesterCollection
	{
		return new TrimesterCollection([
			new Trimester(1, new Interval(
				(clone $this->getConceptionDate()),
				(clone $this->getConceptionDate())->modify("+ 94 days"),
			)),
			new Trimester(2, new Interval(
				(clone $this->getConceptionDate())->modify("+ 94 days"),
				(clone $this->getConceptionDate())->modify("+ 187 days"),
			)),
			new Trimester(3, new Interval(
				(clone $this->getConceptionDate())->modify("+ 187 days"),
				(clone $this->getConceptionDate())->modify("+ 294 days"),
			)),
		]);
	}

	public function getCurrentWeek(Time $referenceTime): ?Week
	{
		foreach ($this->getWeeks() as $week) {
			if ($week->getInterval()->fitsTime($referenceTime)) {
				return $week;
			}
		}

		return null;
	}

	public function getCurrentTrimester(Time $referenceTime): ?Trimester
	{
		foreach ($this->getTrimesters() as $trimester) {
			if ($trimester->getInterval()->fitsTime($referenceTime)) {
				return $trimester;
			}
		}

		return null;
	}
}
