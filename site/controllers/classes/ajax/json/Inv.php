<?php namespace Controllers\Ajax\Json;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\In as InValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Inv extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateTariffCode($data) {
		$fields = ['code|text', 'tarriffcode|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$code = $data->code ? $data->code : $data->tariffcode;

		if ($validate->tariffcode($code) === false) {
			return "Tariff Code $code not found";
		}
		return true;
	}

	public static function getTariffCode($data) {
		$fields = ['code|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		if ($validate->tariffcode($data->code) === false) {
			return false;
		}
		$tariff = TariffCodeQuery::create()->findOneByCode($data->code);
		$response = array(
			'code'        => $data->code,
			'number'      => $tariff->number,
			'rate'        => $tariff->duty_rate,
			'description' => $tariff->description
		);
	}

	public static function validateCountryCode($data) {
		$fields = ['code|text', 'countrycode|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$code = $data->code ? $data->code : $data->tariffcode;

		if ($validate->countrycode($code) === false) {
			return "Country Code $code not found";
		}
		return true;
	}

	public static function getCountryCode($data) {
		$fields = ['code|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->countrycode($data->code) === false) {
			return false;
		}
		$c = CountryCodeQuery::create()->findOneByCode($data->code);
		$response = array(
			'code'        => $data->code,
			'description' => $c->description
		);
	}

	public static function validateCountryCode($data) {
		$fields = ['code|text', 'tarriffcode|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$code = $data->code ? $data->code : $data->tariffcode;

		if ($validate->countrycode($code) === false) {
			return "Country Code $code not found";
		}
		return true;
	}

	public static function validateMsdsCode($data) {
		$fields = ['code|text', 'msdscode|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$code = $data->code ? $data->code : $data->msdscode;

		if ($validate->msdscode($code) === false) {
			return "MSDS Code $code not found";
		}
		return true;
	}

	public static function validateMsdsCode($data) {
		$fields = ['code|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->msdscode($data->code) === false) {
			return false;
		}
		$msds = $self::pw('modules')->get('CodeTablesMsdsm')->get_code($code);
		$response = array(
			'code'        => $data->code,
			'description' => $msds->description
		);
	}

	public static function validateItemid($data) {
		$fields = ['itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->itemid_exists($data->itemID) === false) {
			return "$data->itemID not found";
		}
		return true;
	}

	public static function getItm($data) {
		$fields = ['itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		$validate = self::validator();

		if ($validate->itemid_exists($data->itemID) === false) {
			return false;
		}
		$wire = self::pw();
		$sanitizer = $wire->wire('sanitizer');
		$fields = $sanitizer->array($data->fields, 'string', ['delimiter' => ',']);
		$loader = $wire->wire('modules')->get('LoadItem');
		return $loader->get_item_array($itemID, $fields);
	}

	public static function validateItemid($data) {
		$fields = ['whseID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->whseid($data->whseID) === false || $data->whseID != '**') {
			return "Warehouse ID $data->whseID not found";
		}
		return true;
	}

	public static function validateItmpExists($data) {
		$fields = ['loginID|text', 'userID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$itmp = self::pw('modules')->get('Itmp');
		$validate = self::validator();

		if ($itmp->exists($loginID) === false) {
			return "ITMP for $loginID not found";
		}
		return true;
	}


	private static function validator() {
		return new InValidator();
	}
}
