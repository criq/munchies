<?php

namespace Fatty;

abstract class Approach
{
	const CARBS_DEFAULT = null;
	const CARBS_MAX = null;
	const CARBS_MIN = null;
	const CODE = null;
	const LABEL_DECLINATED = null;

	protected $approach;
	protected $carbs;

	public function __construct(Nutrients\Carbs $carbs = null)
	{
		$this->carbs = $carbs;
	}

	public function __toString()
	{
		return static::LABEL_DECLINATED;
	}

	public function setCarbs(Nutrients\Carbs $carbs)
	{
		if (defined('static::CARBS_MIN') && defined('static::CARBS_MAX') && static::CARBS_MIN && static::CARBS_MAX && ($carbs->getAmount() < static::CARBS_MIN || $carbs->getAmount() > static::CARBS_MAX)) {
			throw (new Exceptions\CaloricCalculatorException("Invalid diet carbs."))
				->setAbbr('invalidDietCarbs')
				;
		}

		$this->carbs = $carbs;

		return $this;
	}

	public function getCarbs()
	{
		return ($this->carbs instanceof Nutrients\Carbs) ? $this->carbs : new Nutrients\Carbs(static::CARBS_DEFAULT, 'g');
	}

	public function getArray()
	{
		return static::CODE;
	}
}
