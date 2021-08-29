<?php

namespace Fatty;

class Amount
{
	protected $amount;

	public function __construct($amount)
	{
		if (!static::validateNumber($amount)) {
			throw (new \App\Classes\Profile\Exceptions\InvalidAmountException("Invalid amount."));
		}

		$this->amount = static::sanitizeNumber($amount);
	}

	public function __toString()
	{
		return \Katu\Utils\Formatter::getLocalReadableNumber(\Katu\Utils\Formatter::getPreferredLocale(), $this->getAmount());
	}

	public static function validateNumber($amount)
	{
		$amount = trim($amount);
		if (!preg_match('/^\-?[0-9]+([\,\.][0-9]+)?$/', $amount)) {
			throw (new \App\Classes\Profile\Exceptions\InvalidAmountException("Invalid amount."));
		}

		return true;
	}

	public static function sanitizeNumber($amount)
	{
		return (new \Katu\Types\TString(trim($amount)))->getAsFloat();
	}

	public function getAmount()
	{
		return $this->amount;
	}
}
