<?php

namespace Fatty;

class Child
{
	protected $birthday;
	protected $breastfeedingMode;

	public function __construct(Birthday $birthday)
	{
		$this->setBirthday($birthday);
	}

	public function setBirthday(Birthday $birthday): Child
	{
		$this->birthday = $birthday;

		return $this;
	}

	public function getBirthday(): Birthday
	{
		return $this->birthday;
	}

	public function setBreastfeedingMode(BreastfeedingMode $breastfeedingMode): Child
	{
		$this->breastfeedingMode = $breastfeedingMode;

		return $this;
	}
}
