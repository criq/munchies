<?php

namespace Fatty\Exceptions;

class FattyException extends \Exception
{
	protected $abbr;
	protected $names = [];

	public static function createFromAbbr(string $abbr)
	{
		return (new static)->setAbbr($abbr);
	}

	public function setAbbr(?string $abbr) : FattyException
	{
		$this->abbr = $abbr;

		return $this;
	}

	public function getAbbr() : ?string
	{
		return $this->abbr;
	}

	public function addName(string $name) : FattyException
	{
		return $this->names[] = $name;

		return $this;
	}

	public function getNames() : array
	{
		return $this->names;
	}
}

// case 'missingActivityAmount':
// 	$errors['missingActivityAmount'] = [
// 		'fields' => ['activity'],
// 		'messages' => ["Chybějící úroveň aktivity."],
// 	];
// 	break;
// case 'missingSportDurations':
// 	$errors['missingSportDurations'] = [
// 		'fields' => ['activity'],
// 		'messages' => ["Chybějící informace o sportu."],
// 	];
// 	break;
// case 'invalidLowFrequencySportDuration':
// 	$errors['invalidLowFrequencySportDuration'] = [
// 		'fields' => ['sportDurations[lowFrequency]'],
// 		'messages' => ["Neplatná délka cvičení nízkofrekvenčních sportů."],
// 	];
// 	break;
// case 'invalidAerobicSportDuration':
// 	$errors['invalidAerobicSportDuration'] = [
// 		'fields' => ['sportDurations[aerobic]'],
// 		'messages' => ["Neplatná délka cvičení aerobních sportů."],
// 	];
// 	break;
// case 'invalidAnaerobicSportDuration':
// 	$errors['invalidAnaerobicSportDuration'] = [
// 		'fields' => ['sportDurations[anaerobic]'],
// 		'messages' => ["Neplatná délka cvičení anaerobních sportů."],
// 	];
// 	break;
// case 'missingGoalTrend':
// 	$errors['missingGoalTrend'] = [
// 		'fields' => ['goalTrend'],
// 		'messages' => ["Chybějící hmotnostní cíl."],
// 	];
// 	break;
// case 'invalidGoalTrend':
// 	$errors['invalidGoalTrend'] = [
// 		'fields' => ['goalTrend'],
// 		'messages' => ["Neplatný hmotnostní cíl."],
// 	];
// 	break;
// case 'missingGoalWeight':
// 	$errors['missingGoalWeight'] = [
// 		'fields' => ['goalWeight'],
// 		'messages' => ["Chybějící cílová hmotnost."],
// 	];
// 	break;
// case 'invalidGoalWeight':
// 	$errors['invalidGoalWeight'] = [
// 		'fields' => ['goalWeight'],
// 		'messages' => ["Neplatná cílová hmotnost."],
// 	];
// 	break;
// case 'goalWeightHigherThanCurrentWeight':
// 	$errors['goalWeightHigherThanCurrentWeight'] = [
// 		'fields' => ['goalWeight'],
// 		'messages' => ["Cílová hmotnost je vyšší než současná."],
// 	];
// 	break;
// case 'goalWeightLowerThanCurrentWeight':
// 	$errors['goalWeightLowerThanCurrentWeight'] = [
// 		'fields' => ['goalWeight'],
// 		'messages' => ["Cílová hmotnost je nižší než současná."],
// 	];
// 	break;
// case 'goalWeightUnchanged':
// 	$errors['goalWeightUnchanged'] = [
// 		'fields' => ['goalWeight'],
// 		'messages' => ["Cílová hmotnost je stejná jako současná."],
// 	];
// 	break;
// case 'missingDiet':
// 	$errors['missingDiet'] = [
// 		'fields' => ['diet'],
// 		'messages' => ["Chybějící výživový trend."],
// 	];
// 	break;
// case 'invalidDiet':
// 	$errors['invalidDiet'] = [
// 		'fields' => ['diet'],
// 		'messages' => ["Neplatný výživový trend."],
// 	];
// 	break;
// case 'invalidDietCarbs':
// 	$errors['invalidDietCarbs'] = [
// 		'fields' => ['dietCarbs'],
// 		'messages' => ["Neplatné množství sacharidů v dietě."],
// 	];
// 	break;
// case 'missingBodyFatPercentageInput':
// 	$errors['missingBodyFatPercentageInput'] = [
// 		'fields' => ['proportions[height]', 'proportions[neck]', 'proportions[waist]', 'measurements[bodyFatPercentage]'],
// 		'messages' => ["Chybí míry k výpočtu procenta tělesného tuku, nebo jeho přímé zadání."],
// 	];
// 	break;
// case 'unableEssentialFatPercentage':
// 	$errors['unableEssentialFatPercentage'] = [
// 		'fields' => [],
// 		'messages' => ["Chybí údaje k výpočtu procenta esenciálního tělesného tuku."],
// 	];
// 	break;
// case 'unableOptimalFatPercentage':
// 	$errors['unableOptimalFatPercentage'] = [
// 		'fields' => [],
// 		'messages' => ["Chybí údaje k výpočtu procenta optimálního tělesného tuku."],
// 	];
// 	break;
// default:
// 	// var_dump($e);
// 	// die;
// 	break;
