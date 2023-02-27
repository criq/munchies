<?php

namespace Fatty\Metrics;

use Katu\Errors\Error;
use Katu\Errors\ErrorCollection;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Tools\Rest\RestResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class MetricResult implements MetricResultCollectionAddable, RestResponseInterface
{
	protected $errors;
	protected $formula;
	protected $metric;
	protected $result;

	public function __construct(Metric $metric)
	{
		$this->setMetric($metric);
	}

	public function getErrors(): ErrorCollection
	{
		if (is_null($this->errors)) {
			$this->errors = new ErrorCollection;
		}

		return $this->errors;
	}

	public function hasErrors(): bool
	{
		return $this->getErrors()->hasErrors();
	}

	public function addErrors(ErrorCollection $errors): MetricResult
	{
		$this->getErrors()->addErrors($errors);

		return $this;
	}

	public function addError(Error $error): MetricResult
	{
		$this->getErrors()->addError($error);

		return $this;
	}

	public function setMetric(Metric $metric): MetricResult
	{
		$this->metric = $metric;

		return $this;
	}

	public function getMetric(): ?Metric
	{
		return $this->metric;
	}

	public function setResult(?ResultInterface $result)
	{
		$this->result = $result;

		return $this;
	}

	public function getResult(): ?ResultInterface
	{
		return $this->result;
	}

	public function setFormula(?string $formula): MetricResult
	{
		$this->formula = $formula;

		return $this;
	}

	public function getFormula(): ?string
	{
		return trim(preg_replace("/\s+/", " ", preg_replace("/[\t\n]/", " ", $this->formula)));
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse([
			"metric" => $this->getMetric()->getRestResponse($request, $options),
			"errors" => $this->getErrors()->getRestResponse($request, $options),
			"result" => $this->getResult() ? $this->getResult()->getRestResponse($request, $options) : null,
		]);
	}
}
