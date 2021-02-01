<?php namespace Controllers\Ajax\Json;

use ProcessWire\Module, ProcessWire\ProcessWire;

use Mvc\Controllers\AbstractController;

use Dplus\CodeValidators\So as MsoValidator;

use DplusUserQuery, DplusUser;

class Mso extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateFreightCode($data) {
		$fields = ['code|text', 'freightcode|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$code = $data->code ? $data->code : $data->freightcode;

		if ($validate->freightcode($code) === false) {
			return "Freight Code $code not found";
		}
		return true;
	}

	public static function getFreightCode($data) {
		$fields = ['code|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->freightcode($data->code) === false) {
			return false;
		}

		$freight = self::pw('modules')->get('CodeTablesMfcm')->get_code($data->code);
		return array(
			'code'        => $data->code,
			'description' => $freight->description
		);
	}

	public static function validatePriceDiscount($data) {
		$fields = ['itemID|text', 'price|float'];
		$data = self::sanitizeParametersShort($data, $fields);
		$discounter = self::pw('modules')->get('PriceDiscounter');
		$discounter->setItemid($data->itemID);
		$discounter->setPrice($data->price);
		return $discounter->allowPrice();
	}

	public static function getLowestPrice($data) {
		$fields = ['itemID|text', 'price|float'];
		$data = self::sanitizeParametersShort($data, $fields);
		$discounter = self::pw('modules')->get('PriceDiscounter');
		$discounter->setItemid($data->itemID);
		$discounter->setPrice($data->price);
		return $discounter->minprice();
	}

	private static function validator() {
		return new MsoValidator();
	}
}
