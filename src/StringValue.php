<?php

namespace Fatty;

use Fatty\Metrics\ResultInterface;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Psr\Http\Message\ServerRequestInterface;

class StringValue implements ResultInterface
{
	protected $value;

	public function __construct(string $value = "")
	{
		$this->setValue($value);
	}

	public function __toString(): string
	{
		return $this->value;
	}

	public function setValue(string $value): StringValue
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): string
	{
		return $this->value;
	}

	public function getNumericalValue(): ?float
	{
		return null;
	}

	public function getStringValue(): ?string
	{
		return $this->getValue();
	}

	public function getBooleanValue(): ?bool
	{
		return (bool)trim($this->getStringValue());
	}

	public function getArrayValue(): ?array
	{
		return null;
	}

	public function getInUnit(string $unit): ?ResultInterface
	{
		return $this;
	}

	public function getUnit(): ?string
	{
		return null;
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse((string)$this->getValue());
	}
}
