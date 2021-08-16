<?php namespace Controllers\Ajax\Json;
// Dplus Model
use TariffCodeQuery, TariffCode;
use CountryCodeQuery, CountryCode;
use WarehouseBinQuery, WarehouseBin;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
use Dplus\CodeValidators\Min\Upcx as UpcxValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Min extends AbstractController {
	public static function test($data) {
		return 'test';
	}

	public static function validateStockCode($data) {
		self::sanitizeParametersShort($data, ['code|text', 'jqv|bool']);
		$validate = self::validator();

		if ($validate->stockcode($data->code) === false) {
			return $data->jqv ? "Tariff Code $code not found" : false;
		}
		return true;
	}

	public static function validateSpecialItemCode($data) {
		self::sanitizeParametersShort($data, ['code|text', 'jqv|bool']);
		$validate = self::validator();

		if ($validate->specialitem($data->code) === false) {
			return $data->jqv ? "Special Item Code $code not found" : false;
		}
		return true;
	}

	public static function validateTariffCode($data) {
		$fields = ['code|text', 'tariffcode|text'];
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
		return array(
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
		return array(
			'code'        => $data->code,
			'description' => $c->description
		);
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

	public static function getMsdsCode($data) {
		$fields = ['code|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->msdscode($data->code) === false) {
			return false;
		}
		$msds = self::pw('modules')->get('CodeTablesMsdsm')->get_code($data->code);
		return array(
			'code'        => $data->code,
			'description' => $msds->description
		);
	}

	public static function validateItemid($data) {
		$fields = ['itemID|text', 'jqv|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->itemid($data->itemID)) {
			return true;
		}
		return $data->jqv ? "$data->itemID not found" : false;
	}

	public static function getItm($data) {
		$fields = ['itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->itemid($data->itemID) === false) {
			return false;
		}
		$sanitizer = self::pw('sanitizer');
		$fields = isset($data->fields) ? $sanitizer->array($data->fields, 'text', ['delimiter' => ',']) : [];
		$loader = self::pw('modules')->get('LoadItem');
		return $loader->get_item_array($data->itemID, $fields);
	}

	public static function getItemAvailable($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$validate = self::validator();

		if ($validate->itemid($data->itemID) === false) {
			return false;
		}
		$m = self::pw('modules')->get('ItemPricing');
		$m->request_search($data->itemID);
		if ($m->has_pricing($data->itemID) === false) {
			return false;
		}
		$pricing = $m->get_pricing($data->itemID);
		$response = [
			'itemid' => $data->itemID,
			'qty'    => $pricing->qty
		];
		return $response;
	}

	public static function validateInvGroupCode($data) {
		$fields = ['code|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->itemgroup($data->code) === false) {
			return "Inv Group Code $data->code not found";
		}
		return true;
	}

	public static function validateWarehouseid($data) {
		$fields = ['whseID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->whseid($data->whseID) === false && $data->whseID != '**') {
			return "Warehouse ID $data->whseID not found";
		}
		return true;
	}

	public static function validateWarehouseBinid($data) {
		$fields = ['whseID|text', 'binID|text', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->whseid($data->whseID) === false) {
			return $data->jqv ? "Warehouse ID '$data->whseID' not found" : false;
		}

		if ($validate->whsebin($data->whseID, $data->binID) === false) {
			return $data->jqv ? "Warehouse '$data->whseID' Bin '$data->binID' not found" : false;
		}
		return true;
	}

	public static function validateItmpExists($data) {
		$fields = ['loginID|text', 'userID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$itmp = self::pw('modules')->get('Itmp');

		if ($itmp->exists($loginID) === false) {
			return "ITMP for $loginID not found";
		}
		return true;
	}

	public static function validateUpc($data) {
		$fields = ['upc|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);
		$validate = new UpcxValidator();

		$exists = $validate->exists($data->upc);

		if (boolval($data->jqv) === false) {
			return $exists;
		}

		// JQuery Validate
		if ($data->new) { // If new, check that upc doesn't already exist.
			return $exists ? "UPC $data->upc Already Exists" : true;
		}

		return $exists ? true : "UPC $data->upc not found";
	}

	public static function validateUpcXref($data) {
		$fields = ['upc|text', 'itemID|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);
		$validate = new UpcxValidator();

		$exists = $validate->exists($data->upc, $data->itemID);

		if (boolval($data->jqv) === false) {
			return $exists;
		}

		// JQuery Validate
		if ($data->new) { // If new, check that upc doesn't already exist.
			return $exists ? "UPC X-Ref Already Exists" : true;
		}

		return $exists ? true : "UPC X-Ref not found";
	}

	public static function validateUpcPrimary($data) {
		$fields = ['upc|text', 'itemID|text', 'jqv|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new UpcxValidator();

		if ($validate->exists($data->upc) === false) {
			return false;
		}

		if ($validate->primaryExistsForItemid($data->itemID) === false) {
			return true;
		}

		if ($validate->primaryExistsForItemid($data->itemID)) {
			$upcx = self::pw('modules')->get('XrefUpc');
			$xref = $upcx->xref_primary_by_itemid($data->itemID);
			$matches = $xref->upc == $data->upc;

			if ($matches === false && $data->jqv === true) {
				return "$xref->upc is the Primary for $data->itemID";
			}

			return $matches;
		}
	}

	public static function getPrimaryUpc($data) {
		$fields = ['itemID|text'];
		self::sanitizeParametersShort($data, $fields);
		$upcx = self::pw('modules')->get('XrefUpc');

		if ($upcx->xref_primary_by_itemid_exists($data->itemID) === false) {
			return false;
		}
		$xref = $upcx->xref_primary_by_itemid($data->itemID);
		return [
			'upc'    => $xref->upc,
			'itemid' => $xref->itemid
		];
	}

	public static function validateI2iExists($data) {
		$fields = ['parentID|text', 'childID|text', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		$exists = $validate->i2i($data->parentID, $data->childID);

		if (boolval($data->jqv) === false) {
			return $exists;
		}

		// JQuery Validate
		if ($data->new) { // If new, check that upc doesn't already exist.
			return $exists ? "Item to Item Already Exists" : true;
		}

		return $exists ? true : "Item to Item X-Ref not found";
	}


	private static function validator() {
		return new MinValidator();
	}
}
