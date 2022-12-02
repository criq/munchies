<?php

namespace Fatty;

class Child
{
	protected $birthday;
	protected $breastfeedingMode;

	public function setBirthday(Birthday $birthday): Child
	{
		$this->birthday = $birthday;

		return $this;
	}

	public function setBreastfeedingMode(BreastfeedingMode $breastfeedingMode): Child
	{
		$this->breastfeedingMode = $breastfeedingMode;

		return $this;
	}
}
