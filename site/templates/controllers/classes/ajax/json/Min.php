<?php namespace Controllers\Ajax\Json;
// Dplus Model
use TariffCodeQuery, TariffCode;
use CountryCodeQuery, CountryCode;
use WarehouseBinQuery, WarehouseBin;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus CRUD
use Dplus\Min\Inmain\Itm\Substitutes as ItmSub;
use Dplus\Min\Inmain\Itm\Options as ItmOptions;
// Dplus Codes
use Dplus\Codes;
// Dplus Validators
use Dplus\CodeValidators as  Validators;
use Dplus\CodeValidators\Min as MinValidator;
use Dplus\CodeValidators\Min\Upcx as UpcxValidator;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Min extends Controller {
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
		$fields = ['whseID|text', 'id|text', 'jqv|bool'];
		if (empty($data->whseID) === false) {
			$data->id = $data->whseID;
		}
		self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($data->id == '**') {
			return true;
		}

		if ($validate->whseid($data->id) === false) {
			return $data->jqv ? "Warehouse ID $data->id not found" : false;
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
		$fields = ['loginID|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);
		$itmp = self::pw('modules')->get('Itmp');

		$exists = $itmp->exists($data->loginID);

		if ($data->jqv === false) {
			if ($data->new) {
				return $exists === false;
			}
			return $exists;
		}

		if ($exists === false) {
			if ($data->new) {
				return "$data->loginID already exists";
			}
			return "$data->loginID not found";
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

	public static function validateIarnExists($data) {
		$fields = ['id|text', 'new|bool', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$exists = $validate->iarn($data->id);

		if (boolval($data->jqv) === false) {
			if ($data->new) {
				return $exists === false;
			}
			return $exists;
		}

		// JQuery Validate
		if ($data->new) { // If new, check that upc doesn't already exist.
			return $exists ? "Inv Adjustment Code Already Exists" : true;
		}

		return $exists ? true : "Inv Adjustment Code not found";
	}

	public static function validateItmWhse($data) {
		$fields = ['itemID|text', 'whseID|text', 'new|bool', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		$exists = $validate->itmWhse($data->itemID, $data->whseID);

		if (boolval($data->jqv) === false) {
			if ($data->new) {
				return $exists === false;
			}
			return $exists;
		}

		// JQuery Validate
		if ($data->new) { // If new, check that upc doesn't already exist.
			return $exists ? "ITM Warehouse Already Exists" : true;
		}

		return $exists ? true : "ITM Warehouse not found";
	}

	public static function validateItmSub($data) {
		$fields = ['itemID|text', 'subitemID|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);
		$itmSub = new ItmSub();
		$itmSub->init();

		$exists = $itmSub->exists($data->itemID, $data->subitemID);

		if (boolval($data->jqv) === false) {
			if (boolval($data->new) === false) { // CHECK against existing Items
				return $exists;
			}
			// CHECK if Sub could exist
			return $exists === false;
		}

		// JQV
		if (boolval($data->new) === false) { // CHECK against existing Items
			return $exists ? true : "$data->itemID Substitute $data->subitemID not found";
		}

		$exists === false ? true : "$data->itemID Substitute $data->subitemID already exists";
	}


	public static function validateItmShortitemid($data) {
		$fields = ['itemID|text', 'shortitemID|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);
		$validate = new Validator\Mso\Cxm();
		$exists = $validate->shortitemExists($data->shortitemID);

		if (boolval($data->jqv) === false) {
			if (boolval($data->new) === false) { // CHECK against existing Items
				return $exists;
			}
			// CHECK if Sub could exist
			return $exists === false;
		}

		// JQV
		if (boolval($data->new) === false) { // CHECK against existing Items
			return $exists ? true : "Short Item $data->shortitemID Item not found";
		}

		$exists === false ? true : "Short Item $data->shortitemID already exists";
	}

	public static function validateItmShortitemidAvailable($data) {
		$fields = ['itemID|text', 'shortitemID|text', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);
		$validate = new Validators\Mso\Cxm();
		$exists = $validate->shortitemExists($data->shortitemID);
		if ($exists === false) {
			return true;
		}
		$cxm = self::pw('modules')->get('XrefCxm');
		$xref = $cxm->xref_shortitem_by_custitemid($data->shortitemID);
		$available = $xref->itemid == $data->itemID;
		if (boolval($data->jqv) === false) {
			return $available;
		}
		return $available === false ? "Short Item $data->shortitemID already exists" : 'true';
	}

	public static function getUom($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);
		$umm = self::pw('modules')->get('CodeTablesUmm');
		if ($umm->code_exists($data->code) === false) {
			return false;
		}
		$uom = $umm->get_code($data->code);
		return [
			'code'        => $uom->code,
			'description' => $uom->description,
			'conversion'  => $uom->conversion
		];
	}

	public static function validateAddm($data) {
		$fields = ['itemID|text', 'addonID|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		$exists = $validate->addm($data->itemID, $data->addonID);

		if (boolval($data->jqv) === false) {
			if (boolval($data->new)) {
				if ($data->itemID === $data->addonID) {
					return false;
				}
				return $exists === false;
			}
			return $exists;
		}

		// JQuery Validate
		if (boolval($data->new)) { // If new, check that Add-On doesn't already exist or Can't Exist
			if ($data->itemID === $data->addonID) {
				return $data->jqv ? "Add-On Item ID cannot = the Item ID" : false;
			}
			return $exists ? "Add-On Item Already Exists" : true;
		}

		return $exists ? true : "Add-On Item not found";
	}

	public static function getInvOptCodeNotes($data) {
		$fields = ['itemID|text', 'type|text'];
		self::sanitizeParametersShort($data, $fields);
		$qnotes = ItmOptions\Qnotes::getInstance();
		if ($qnotes->notesExist($data->itemID, $data->type) === false) {
			return false;
		}
		return $qnotes->notesJson($data->itemID, $data->type);
	}

	public static function validateCsccmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Csccm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Customer Stocking Cell Code $data->code already exists";
		}

		if ($exists === false) {
			return "Customer Stocking Cell Code $data->code not found";
		}
		return true;
	}

	public static function getCsccmCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Csccm::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	public static function validateIasmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Iasm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Inventory Assortment Code $data->code already exists";
		}

		if ($exists === false) {
			return "Inventory Assortment Code $data->code not found";
		}
		return true;
	}

	public static function getIasmCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Iasm::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	public static function validateIgcmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Igcm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Inventory Commission Code $data->code already exists";
		}

		if ($exists === false) {
			return "Inventory Commission Code $data->code not found";
		}
		return true;
	}

	public static function getIgcmCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Igcm::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	public static function validateIgmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Igm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Inventory Group Code $data->code already exists";
		}

		if ($exists === false) {
			return "Inventory Group Code $data->code not found";
		}
		return true;
	}

	public static function getIgmCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Igm::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	public static function validateIgpmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Igpm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Inventory Price Code $data->code already exists";
		}

		if ($exists === false) {
			return "Inventory Price Code $data->code not found";
		}
		return true;
	}

	public static function getIgpmCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Igpm::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	public static function validateIplmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Iplm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Inventory Product Line Code $data->code already exists";
		}

		if ($exists === false) {
			return "Inventory Product Line Code $data->code not found";
		}
		return true;
	}

	public static function getIplmCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Iplm::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	public static function validateMsdsmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Msdsm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Material Safety Data Sheet Code $data->code already exists";
		}

		if ($exists === false) {
			return "Material Safety Data Sheet Code $data->code not found";
		}
		return true;
	}

	public static function getMsdsmCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Msdsm::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	public static function validateSpitCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Spit::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Special Item Code $data->code already exists";
		}

		if ($exists === false) {
			return "Special Item Code $data->code not found";
		}
		return true;
	}

	public static function getSpitCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Spit::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	public static function validateStcmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Stcm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Stock Code $data->code already exists";
		}

		if ($exists === false) {
			return "Stock Code $data->code not found";
		}
		return true;
	}

	public static function getStcmCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Min\Stcm::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

/* =============================================================
	Supplemental
============================================================= */
	private static function validator() {
		return new MinValidator();
	}
}
