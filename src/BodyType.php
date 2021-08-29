<?php

namespace Fatty;

abstract class BodyType
{
	const LABEL_CATEGORY = null;
	const LABEL_TYPE = null;

	public function __toString()
	{
		return implode(": ", array_filter([
			$this->getLabelCategory(),
			$this->getLabelType(),
		]));
	}

	public function getCode()
	{
		return lcfirst(array_slice(explode('\\', get_called_class()), -1, 1)[0]);
	}

	public function getLabelCategory()
	{
		return defined('static::LABEL_CATEGORY') ? static::LABEL_CATEGORY : null;
	}

	public function getLabelType()
	{
		return static::LABEL_TYPE;
	}
}
