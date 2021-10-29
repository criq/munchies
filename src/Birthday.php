<?php

namespace Fatty;

class Birthday
{
	private $datetime;

	public function __construct(\DateTime $datetime)
	{
		$this->datetime = $datetime;
	}

	public static function createFromString(string $value): ?Birthday
	{
		try {
			$datetime = \DateTime::createFromFormat('j.*n.*Y', $value);
			$datetime->setTime(0, 0, 0);

			return new static($datetime);
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getDatetime(): \DateTime
	{
		return $this->datetime;
	}

	public function isInPast(): bool
	{
		return $this->getDatetime()->getTimestamp() <= (new \DateTime)->getTimestamp();
	}

	public function isInFuture(): bool
	{
		return $this->getDatetime()->getTimestamp() > (new \DateTime)->getTimestamp();
	}

	public function getAge(): float
	{
		return $this->getDatetime()->diff(new \DateTime)->y;
	}

	public function diff(): ?\DateInterval
	{
		try {
			return $this->getDatetime()->diff(...func_get_args());
		} catch (\Throwable $e) {
			return null;
		}
	}
}
