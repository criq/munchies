<?php

namespace Fatty;

use Fatty\Metrics\ResultInterface;
use Katu\Tools\Options\OptionCollection;
use Katu\Tools\Rest\RestResponse;
use Katu\Types\TJSON;
use Psr\Http\Message\ServerRequestInterface;

class ArrayValue implements ResultInterface
{
	protected $value;

	public function __construct(array $value = [])
	{
		$this->setValue($value);
	}

	public function __toString(): string
	{
		return "";
	}

	public function setValue(array $value): ArrayValue
	{
		$this->value = $value;

		return $this;
	}

	public function getValue(): array
	{
		return $this->value;
	}

	public function getNumericalValue(): ?float
	{
		return null;
	}

	public function getStringValue(): ?string
	{
		return "";
	}

	public function getBooleanValue(): ?bool
	{
		return (bool)count($this->getValue());
	}

	public function getArrayValue(): ?array
	{
		return $this->getValue();
	}

	public function getInUnit(string $unit): ?ResultInterface
	{
		return $this;
	}

	public function getUnit(): ?string
	{
		return null;
	}

	public function getFormatted(): ?string
	{
		return TJSON::createFromContents($this->getArrayValue());
	}

	public function getRestResponse(?ServerRequestInterface $request = null, ?OptionCollection $options = null): RestResponse
	{
		return new RestResponse($this->getValue());
	}
}
