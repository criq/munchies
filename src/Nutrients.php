<?php

namespace Fatty;

class Nutrients
{
	private $carbs;
	private $proteins;
	private $fats;

	public function setCarbs(Nutrients\Carbs $nutrient)
	{
		$this->carbs = $nutrient;

		return $this;
	}

	public function getCarbs()
	{
		return $this->carbs;
	}

	public function setProteins(Nutrients\Proteins $nutrient)
	{
		$this->proteins = $nutrient;

		return $this;
	}

	public function getProteins()
	{
		return $this->proteins;
	}

	public function setFats(Nutrients\Fats $nutrient)
	{
		$this->fats = $nutrient;

		return $this;
	}

	public function getFats()
	{
		return $this->fats;
	}

	public function getEnergy()
	{
		$amount = 0;

		if ($this->getCarbs() instanceof Nutrients\Carbs) {
			$amount += $this->getCarbs()->getEnergy()->getInKJ()->getAmount();
		}

		if ($this->getProteins() instanceof Nutrients\Proteins) {
			$amount += $this->getProteins()->getEnergy()->getInKJ()->getAmount();
		}

		if ($this->getFats() instanceof Nutrients\Fats) {
			$amount += $this->getFats()->getEnergy()->getInKJ()->getAmount();
		}

		return new Energy($amount, 'kJ');
	}
}
