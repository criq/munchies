<?php

namespace Fatty;

class ApproachCollection extends \ArrayObject
{
	public static function createDefault(): ApproachCollection
	{
		return new static([
			new \Fatty\Approaches\DIA150,
			new \Fatty\Approaches\DiaMama\HighCarb,
			new \Fatty\Approaches\DiaMama\LowCarb,
			new \Fatty\Approaches\DiaMama\Standard,
			new \Fatty\Approaches\Keto,
			new \Fatty\Approaches\LowCarb,
			new \Fatty\Approaches\LowEnergy,
			new \Fatty\Approaches\LowEnergyTransition,
			new \Fatty\Approaches\Mediterranean,
			new \Fatty\Approaches\Standard,
		]);
	}

	public function getAssoc(): ApproachCollection
	{
		return new static(array_combine(
			array_map(function (Approach $approach) {
				return $approach->getCode();
			}, $this->getArrayCopy()),
			array_values($this->getArrayCopy()),
		));
	}
}
