<?php namespace Controllers\Ajax\Json;
// Dplus Model
use TariffCodeQuery, TariffCode;
use CountryCodeQuery, CountryCode;
use WarehouseBinQuery, WarehouseBin;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus CRUD
use Dplus\Min as MinMaintenance;
use Dplus\Min\Inmain\Itm\Substitutes as ItmSub;
use Dplus\Min\Inmain\Itm\Options as ItmOptions;
// Dplus Codes
use Dplus\Codes;
// Dplus Validators
use Dplus\CodeValidators as  Validators;
use Dplus\CodeValidators\Min as MinValidator;
use Dplus\CodeValidators\Min\Upcx as UpcxValidator;
// Mvc Controllers
use Controllers\Ajax\Json\AbstractJsonController;

class Min extends AbstractJsonController {
	public static function validateCountryCode($data) {
		$data->code = $data->code ? $data->code : $data->countrycode;
		return Mar::validateCocomCode($data);
	}

	public static function getCountryCode($data) {
		$data->code = $data->code ? $data->code : $data->countrycode;
		return Mar::getCocomCode($data);
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

	public static function validateWarehouseid($data) {
		$fields = ['whseID|string', 'id|string', 'jqv|bool'];
		if (empty($data->whseID) === false) {
			$data->id = $data->whseID;
		}
		self::sanitizeParametersShort($data, $fields);

		if ($data->id == '**') {
			return true;
		}
		return self::validateIwhmCode($data);
	}

	public static function validateWarehouseBinid($data) {
		$fields = ['whseID|string', 'binID|string', 'jqv|bool'];
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

	public static function validateItmWhse($data) {
		$fields = ['itemID|text', 'whseID|string', 'new|bool', 'jqv|bool'];
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

	public static function getItmpUser($data) {
		$fields = ['userID|text', 'code|line'];
		self::sanitizeParametersShort($data, $fields);

		$manager = MinMaintenance\Itmp::instance();
		return $manager->userJson($manager->userItmp($data->userID));
	}

/* =============================================================
	Code Table Validates / Gets
============================================================= */
	public static function validateCsccmCode($data) {
		$table = Codes\Min\Csccm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getCsccmCode($data) {
		$table = Codes\Min\Csccm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateIasmCode($data) {
		$table = Codes\Min\Iasm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getIasmCode($data) {
		$table = Codes\Min\Iasm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateIarnCode($data) {
		$table = Codes\Min\Iarn::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getIarnCode($data) {
		$table = Codes\Min\Iarn::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateIgcmCode($data) {
		$table = Codes\Min\Igcm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getIgcmCode($data) {
		$table = Codes\Min\Igcm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateIgmCode($data) {
		$table = Codes\Min\Igm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getIgmCode($data) {
		$table = Codes\Min\Igm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateIgpmCode($data) {
		$table = Codes\Min\Igpm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getIgpmCode($data) {
		$table = Codes\Min\Igpm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateIplmCode($data) {
		$table = Codes\Min\Iplm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getIplmCode($data) {
		$table = Codes\Min\Iplm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateIwhmCode($data) {
		$fields = ['id|string'];
		self::sanitizeParametersShort($data, $fields);
		if (empty($data->id) === false) {
			$data->code = $data->id;
		}

		$table = Codes\Min\Iwhm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getIwhmCode($data) {
		$fields = ['id|string'];
		self::sanitizeParametersShort($data, $fields);
		if (empty($data->id) === false) {
			$data->code = $data->id;
		}

		$table = Codes\Min\Iwhm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateMsdsmCode($data) {
		$data->code = $data->code ? $data->code : $data->msdscode;
		$table = Codes\Min\Msdsm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getMsdsmCode($data) {
		$data->code = $data->code ? $data->code : $data->msdscode;
		$table = Codes\Min\Msdsm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateSpitCode($data) {
		$table = Codes\Min\Spit::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getSpitCode($data) {
		$table = Codes\Min\Spit::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateStcmCode($data) {
		$table = Codes\Min\Stcm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getStcmCode($data) {
		$table = Codes\Min\Stcm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateTarmCode($data) {
		$data->code = $data->code ? $data->code : $data->tariffcode;
		$table = Codes\Min\Tarm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getTarmCode($data) {
		$data->code = $data->code ? $data->code : $data->tariffcode;
		$table = Codes\Min\Tarm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateUmmCode($data) {
		$table = Codes\Min\Umm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getUmmCode($data) {
		$table = Codes\Min\Umm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

/* =============================================================
	Supplemental
============================================================= */
	private static function validator() {
		return new MinValidator();
	}
}
