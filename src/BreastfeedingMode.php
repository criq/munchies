<?php

namespace Fatty;

class BreastfeedingMode
{
	public function getCode()
	{
		return lcfirst(array_slice(explode('\\', get_called_class()), -1, 1)[0]);
	}
}
