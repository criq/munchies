<?php

namespace Fatty;

use Fatty\Nutrients\Carbs;
use Fatty\Nutrients\Fats;
use Fatty\Nutrients\Proteins;

abstract class Approach
{
	const CARBS_DEFAULT = null;
	const CARBS_MAX = null;
	const CARBS_MIN = null;
	const CODE = null;
	const ENERGY_DEFAULT = null;
	const FATS_DEFAULT = null;
	const LABEL_DECLINATED = null;
	const PROTEINS_DEFAULT = null;

	public function __toString() : string
	{
		return $this->getLabelDeclinated();
	}

	public static function createFromString(string $value) : ?Approach
	{
		try {
			$class = 'Fatty\\Approaches\\' . ucfirst($value);

			return new $class;
		} catch (\Throwable $e) {
			return null;
		}
	}

	public function getCode() : string
	{
		return (string)static::CODE;
	}

	public function getLabelDeclinated() : string
	{
		return (string)static::LABEL_DECLINATED;
	}

	public function getEnergyDefault() : ?Energy
	{
		return static::ENERGY_DEFAULT ? new Energy(new Amount((float)static::ENERGY_DEFAULT), 'kcal') : null;
	}

	public function getCarbsDefault() : ?Carbs
	{
		return static::CARBS_DEFAULT ? new Carbs(new Amount((float)static::CARBS_DEFAULT), 'g') : null;
	}

	public function getCarbsMin() : ?Carbs
	{
		return static::CARBS_MIN ? new Carbs(new Amount((float)static::CARBS_MIN), 'g') : null;
	}

	public function getCarbsMax() : ?Carbs
	{
		return static::CARBS_MAX ? new Carbs(new Amount((float)static::CARBS_MAX), 'g') : null;
	}

	public function getFatsDefault() : ?Fats
	{
		return static::FATS_DEFAULT ? new Carbs(new Amount((float)static::FATS_DEFAULT), 'g') : null;
	}

	public function getProteinsDefault() : ?Proteins
	{
		return static::PROTEINS_DEFAULT ? new Carbs(new Amount((float)static::PROTEINS_DEFAULT), 'g') : null;
	}
}
