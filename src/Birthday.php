<?php

namespace Fatty;

use Katu\Errors\Error;
use Katu\Tools\Calendar\Time;
use Katu\Tools\Validation\Param;
use Katu\Tools\Validation\Validation;

class Birthday
{
	private $time;

	public function __construct(Time $time)
	{
		$this->time = $time;
	}

	public static function createFromString(string $value): ?Birthday
	{
		try {
			$time = Time::createFromFormat("j.*n.*Y", $value)->setTime(0, 0, 0);

			return new static($time);
		} catch (\Throwable $e) {
			return null;
		}
	}

	public static function validateBirthday(Param $birthday): Validation
	{
		$output = static::createFromString($birthday);
		if (!$output) {
			return (new Validation)->addError((new Error("Invalid birthday."))->addParam($birthday));
		} else {
			return (new Validation)->setResponse($output)->addParam($birthday->setOutput($output));
		}
	}

	public function getTime(): Time
	{
		return $this->time;
	}

	public function isInPast(): bool
	{
		return $this->getTime()->getTimestamp() <= (new Time)->getTimestamp();
	}

	public function isInFuture(): bool
	{
		return $this->getTime()->getTimestamp() > (new Time)->getTimestamp();
	}

	public function getAge(Time $referenceTime): float
	{
		$diff = $this->getTime()->diff($referenceTime);

		return $diff->days / 365.24;
	}

	public function getAgeInMonths(Time $referenceTime): float
	{
		return $this->getAge($referenceTime) * 12;
	}

	public function getWholeAge(Time $referenceTime): int
	{
		return floor($this->getAge($referenceTime));
	}

	public function diff(): ?\DateInterval
	{
		try {
			return $this->getTime()->diff(...func_get_args());
		} catch (\Throwable $e) {
			return null;
		}
	}
}
