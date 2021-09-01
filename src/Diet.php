<?php

namespace Fatty;

use Fatty\Exceptions\FattyException;
use Fatty\Nutrients\Carbs;

class Diet
{
	protected $approach;
	protected $carbs;

	public function setApproach(?Approach $value) : Diet
	{
		$this->approach = $value;

		return $this;
	}

	public function getApproach() : ?Approach
	{
		return $this->approach;
	}

	public function setCarbs(Carbs $carbs) : Diet
	{
		if ($this->getApproach() && $this->getApproach()->getCarbsMin() && $this->getApproach()->getCarbsMax() && ($carbs->getAmount() < $this->getApproach()->getCarbsMin() || $carbs->getAmount() > $this->getApproach()->getCarbsMax())) {
			throw FattyException::createFromAbbr('invalidDietCarbs');
		}

		$this->carbs = $carbs;

		return $this;
	}

	public function getCarbs() : Carbs
	{
		return $this->carbs ?: $this->getApproach()->getCarbsDefault();
	}
}
