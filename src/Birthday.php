<?php

namespace Fatty;

class Birthday
{
	private $birthday;

	public function __construct($birthday)
	{
		$this->birthday = $birthday->setHour(0)->setMinute(0)->setSecond(0);
	}

	public function getBirthday()
	{
		return $this->birthday;
	}

	public function isInPast()
	{
		return $this->birthday->isInPast();
	}

	public function isInFuture()
	{
		return $this->birthday->isInFuture();
	}

	public function getAge()
	{
		$diff = $this->birthday->diff(new \Katu\Utils\DateTime);

		return $diff->y;
	}

	public function diff()
	{
		return call_user_func_array([$this->birthday, 'diff'], func_get_args());
	}
}
