<?php

namespace Fatty\Metrics;

use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MetricResultCollection extends \ArrayObject implements MetricResultCollectionAddable, RestResponseInterface
{
	public function add(MetricResultCollectionAddable $addable): MetricResultCollection
	{
		if (is_iterable($addable)) {
			foreach ($addable as $addableItem) {
				$this->add($addableItem);
			}
		} else {
			$this[] = $addable;
		}

		return $this;
	}

	public function filterByCode(string $code): MetricResultCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (MetricResult $metricResult) use ($code) {
			// var_dump($metricResult->getMetric()->getCode());
			return $metricResult->getMetric()->getCode() == $code;
		})));
	}

	public function filterByMetric(Metric $metric): MetricResultCollection
	{
		return new static(array_values(array_filter($this->getArrayCopy(), function (MetricResult $metricResult) use ($metric) {
			return $metricResult->getMetric() instanceof $metric;
		})));
	}

	public function getFirst(): ?MetricResult
	{
		return $this[0] ?? null;
	}

	public function getAssoc(): MetricResultCollection
	{
		$res = [];
		foreach ($this as $metricResult) {
			$res[$metricResult->getMetric()->getCode()->getCamelCaseFormat()] = $metricResult;
		}

		ksort($res);

		return new static($res);
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse(array_map(function (MetricResult $metricResult) use ($request, $options) {
			return $metricResult->getRestResponse($request, $options);
		}, $this->getAssoc()->getArrayCopy()));
	}
}
