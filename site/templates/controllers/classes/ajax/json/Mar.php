<?php namespace Controllers\Ajax\Json;
// Dplus Models
use CustomerQuery, CustomerShiptoQuery;
// ProcessWire Mlasses, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Mar       as MarValidator;
use Dplus\CodeValidators\Mar\Cxm   as CxmValidator;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base as CodeTable;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Mar extends Base {

	public static function validateSalesPersonId($data) {
		$valid = false;
		$fields = ['id|text', 'jqv|bool', 'new|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$valid = self::_validateSalesPersonId($data);

		if ($data->new) {
			$canuse = $valid === false;

			if ($canuse === false && $data->jqv) {
				return "Sales Person $data->id already exists";
			}
			return $canuse;
		}

		if ($data->jqv && $valid === false) {
			return "Sales Person $data->id not found";
		}
		return $valid;
	}

	public static function _validateSalesPersonId($data) {
		$exists = false;
		$fields = ['id|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new MarValidator();

		if ($validate->salespersonid($data->id) === false) {
			return false;
		}
		return true;
	}

	public static function validateSalesGroupId($data) {
		$valid = false;
		$fields = ['id|text', 'jqv|bool', 'new|bool'];
		$data = self::sanitizeParametersShort($data, $fields);

		$valid = self::_validateSalesGroupId($data);

		if ($data->new) {
			$canuse = $valid === false;
			if ($canuse === false && $data->jqv) {
				return "Sales Person $data->id already exists";
			}
			return $canuse;
		}

		if ($valid === false && $data->jqv) {
			return "Sales Group $data->id not found";
		}
		return $valid;
	}

	public static function _validateSalesGroupId($data) {
		$exists = false;
		$fields = ['id|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new MarValidator();

		if ($validate->salesgroupid($data->id) === false) {
			return false;
		}
		return true;
	}

	public static function validateCustid($data) {
		$fields = ['custID|text', 'new|bool', 'jqv|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new MarValidator();
		$exists = $validate->custid($data->custID);

		if ($data->new) {
			$valid = $exists === false;

			if ($valid === false && $data->jqv) {
				return "$data->custID already exists";
			}
			return $valid;
		}

		if ($exists === false && $data->jqv) {
			return "$data->custID not found";
		}
		return $exists;
	}

	public static function getCustomer($data) {
		self::sanitizeParametersShort($data, ['custID|text']);
		$validate = new MarValidator();

		if ($validate->custid($data->custID) === false) {
			return false;
		}
		$customer = CustomerQuery::create()->findOneByCustid($data->custID);
		return [
			'id'   => $customer->id,
			'name' => $customer->name
		];
	}

	public static function getCustomerShipto($data) {
		self::sanitizeParametersShort($data, ['custID|text']);
		$validate = new MarValidator();

		if ($validate->custShiptoid($data->custID, $data->shiptoID) === false) {
			return false;
		}
		$shipto = CustomerShiptoQuery::create()->filterByCustid($data->custID)->filterByShiptoid($data->shiptoID)->findOne();

		return [
			'custid' => $shipto->custid,
			'id'     => $shipto->id,
			'name'   => $shipto->name,
			'address' => [
				'address1' => $shipto->address,
				'address2' => $shipto->address2,
				'city'     => $shipto->city,
				'state'    => $shipto->state,
				'zip'      => $shipto->zip,
			]
		];
	}

/* =============================================================
	CodeTable functions
============================================================= */
	public static function validateCcmCode($data) {
		$manager = Codes\Mar\Ccm::getInstance();
		return self::validateCodeTableSimpleCode($manager, $data);
	}

	public static function getCcmCode($data) {
		$manager = Codes\Mar\Ccm::getInstance();
		return self::getCodeTableSimpleCode($manager, $data);
	}

	public static function validateCrtmCode($data) {
		$manager = Codes\Mar\Crtm::getInstance();
		return self::validateCodeTableSimpleCode($manager, $data);
	}

	public static function getCrtmCode($data) {
		$manager = Codes\Mar\Crtm::getInstance();
		return self::getCodeTableSimpleCode($manager, $data);
	}

	public static function validateSpgpmCode($data) {
		$manager = Codes\Mar\Spgpm::getInstance();
		return self::validateCodeTableSimpleCode($manager, $data);
	}

	public static function getSpgpmCode($data) {
		$manager = Codes\Mar\Spgpm::getInstance();
		return self::getCodeTableSimpleCode($manager, $data);
	}

	public static function validateSpmCode($data) {
		$manager = Codes\Mar\Spm::getInstance();
		return self::validateCodeTableSimpleCode($manager, $data);
	}

	public static function getSpmCode($data) {
		$manager = Codes\Mar\Spm::getInstance();
		return self::getCodeTableSimpleCode($manager, $data);
	}

	public static function validateSucCode($data) {
		$manager = Codes\Mar\Suc::getInstance();
		return self::validateCodeTableSimpleCode($manager, $data);
	}

	public static function getSucCode($data) {
		$manager = Codes\Mar\Suc::getInstance();
		return self::getCodeTableSimpleCode($manager, $data);
	}

	public static function validateWormCode($data) {
		$manager = Codes\Mar\Worm::getInstance();
		return self::validateCodeTableSimpleCode($manager, $data);
	}

	public static function getWormCode($data) {
		$manager = Codes\Mar\Worm::getInstance();
		return self::getCodeTableSimpleCode($manager, $data);
	}
}
