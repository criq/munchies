<?php

namespace Fatty;

class Birthday
{
	private $birthday;

	public function __construct(\DateTime $birthday)
	{
		$this->birthday = $birthday;
	}

	public static function createFromString(string $value) : ?Birthday
	{
		try {
			$datetime = \DateTime::createFromFormat('j.n.Y', $value);
			$datetime->setTime(0, 0, 0);

			return new static($datetime);
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getBirthday() : \DateTime
	{
		return $this->birthday;
	}

	public function isInPast() : bool
	{
		return $this->getBirthday()->getTimestamp() <= (new \DateTime)->getTimestamp();
	}

	public function isInFuture() : bool
	{
		return $this->getBirthday()->getTimestamp() > (new \DateTime)->getTimestamp();
	}

	public function getAge() : float
	{
		return $this->getBirthday()->diff(new \DateTime)->y;
	}

	public function diff() : ?\DateInterval
	{
		try {
			return $this->getBirthday()->diff(...func_get_args());
		} catch (\Throwable $e) {
			return null;
		}
	}
}
