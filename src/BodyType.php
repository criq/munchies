<?php

namespace Fatty;

abstract class BodyType
{
	const LABEL_CATEGORY = null;
	const LABEL_TYPE = null;

	public function __toString(): string
	{
		return $this->getLabel();
	}

	public function getCode(): string
	{
		return lcfirst(array_slice(explode('\\', get_called_class()), -1, 1)[0]);
	}

	public function getLabel(): string
	{
		return implode(": ", array_filter([
			$this->getLabelCategory(),
			$this->getLabelType(),
		]));
	}

	public function getLabelCategory(): ?string
	{
		return defined('static::LABEL_CATEGORY') ? static::LABEL_CATEGORY : null;
	}

	public function getLabelType(): ?string
	{
		return static::LABEL_TYPE;
	}
}
