<?php

namespace Fatty;

use Fatty\Nutrients\Carbs;
use Fatty\Nutrients\Fats;
use Fatty\Nutrients\Proteins;

class Nutrients
{
	private $carbs;
	private $proteins;
	private $fats;

	public function setCarbs(Carbs $nutrient)
	{
		$this->carbs = $nutrient;

		return $this;
	}

	public function getCarbs() : ?Carbs
	{
		return $this->carbs;
	}

	public function setProteins(Proteins $nutrient)
	{
		$this->proteins = $nutrient;

		return $this;
	}

	public function getProteins() : ?Proteins
	{
		return $this->proteins;
	}

	public function setFats(Fats $nutrient)
	{
		$this->fats = $nutrient;

		return $this;
	}

	public function getFats() : ?Fats
	{
		return $this->fats;
	}

	public function getEnergy() : Energy
	{
		$amount = 0;

		if ($this->getCarbs() instanceof Nutrients\Carbs) {
			$amount += $this->getCarbs()->getEnergy()->getInKJ()->getAmount()->getValue();
		}

		if ($this->getProteins() instanceof Nutrients\Proteins) {
			$amount += $this->getProteins()->getEnergy()->getInKJ()->getAmount()->getValue();
		}

		if ($this->getFats() instanceof Nutrients\Fats) {
			$amount += $this->getFats()->getEnergy()->getInKJ()->getAmount()->getValue();
		}

		return new Energy(new Amount($amount), 'kJ');
	}
}
