<?php

namespace Fatty\Metrics;

class MetricCollection extends \ArrayObject
{
	public function merge(MetricCollection $metricCollection): MetricCollection
	{
		foreach ($metricCollection as $metric) {
			$this->append($metric);
		}

		return $this;
	}

	public function filterByName(string $name): MetricCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function ($metric) use ($name) {
			return $metric->getName() == $name;
		})));
	}

	public function getSorted(): MetricCollection
	{
		$array = $this->getArrayCopy();
		usort($array, function ($a, $b) {
			return $a->getName() > $b->getName() ? 1 : -1;
		});

		return new static($array);
	}

	// public function getResponse(?Locale $locale = null): array
	// {
	// 	return array_map(function ($metric) use ($locale) {
	// 		return $metric->getResponse($locale);
	// 	}, $this->getArrayCopy());
	// }
}
