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

	public function setCarbs(Carbs $nutrient): Nutrients
	{
		$this->carbs = $nutrient;

		return $this;
	}

	public function getCarbs(): ?Carbs
	{
		return $this->carbs;
	}

	public function setProteins(Proteins $nutrient): Nutrients
	{
		$this->proteins = $nutrient;

		return $this;
	}

	public function getProteins(): ?Proteins
	{
		return $this->proteins;
	}

	public function setFats(Fats $nutrient): Nutrients
	{
		$this->fats = $nutrient;

		return $this;
	}

	public function getFats(): ?Fats
	{
		return $this->fats;
	}

	public function getEnergy(): Energy
	{
		$amount = 0;

		if ($this->getCarbs() instanceof Nutrients\Carbs) {
			$amount += $this->getCarbs()->getEnergy()->getInUnit('kJ')->getAmount()->getValue();
		}

		if ($this->getProteins() instanceof Nutrients\Proteins) {
			$amount += $this->getProteins()->getEnergy()->getInUnit('kJ')->getAmount()->getValue();
		}

		if ($this->getFats() instanceof Nutrients\Fats) {
			$amount += $this->getFats()->getEnergy()->getInUnit('kJ')->getAmount()->getValue();
		}

		return new Energy(new Amount($amount), 'kJ');
	}
}
