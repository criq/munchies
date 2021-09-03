<?php

namespace Fatty;

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

	public function getResponse(): array
	{
		return array_map(function ($metric) {
			return $metric->getResponse();
		}, $this->getArrayCopy());
	}
}
