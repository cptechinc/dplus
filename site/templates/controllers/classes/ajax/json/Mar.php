<?php namespace Controllers\Ajax\Json;
// Dplus Models
use CustomerQuery, CustomerShiptoQuery;
// ProcessWire Mlasses, Modules
use ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Mar       as MarValidator;
use Dplus\Codes;
use Dplus\Mar\Armain;
// Mvc Controllers
use Controllers\Ajax\Json\AbstractJsonController;

class Mar extends AbstractJsonController {
	public static function test() {
		return 'test';
	}

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
		$fields = ['custID|string', 'new|bool', 'jqv|bool'];
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
		self::sanitizeParametersShort($data, ['custID|string']);
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
		self::sanitizeParametersShort($data, ['custID|string']);
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

	public static function validateCcmCode($data) {
		$table = Codes\Mar\Ccm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getCcmCode($data) {
		$table = Codes\Mar\Ccm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateCpmCode($data) {
		$table = Codes\Mar\Cpm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getCpmCode($data) {
		$table = Codes\Mar\Cpm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateCocomCode($data) {
		$table = Codes\Mar\Cocom::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getCocomCode($data) {
		$table = Codes\Mar\Cocom::getInstance();
		return self::getCodeTableCode($data, $table);
	}
	
	public static function validateCrcdCode($data) {
		$table = Codes\Mar\Crcd::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getCrcdCode($data) {
		$table = Codes\Mar\Crcd::getInstance();
		return self::getCodeTableCode($data, $table);
	}
	
	public static function validateCrtmCode($data) {
		$table = Codes\Mar\Crtm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getCrtmCode($data) {
		$table = Codes\Mar\Crtm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateCsvCode($data) {
		$table = Codes\Mar\Csv::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getCsvCode($data) {
		$table = Codes\Mar\Csv::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateCtmCode($data) {
		$table = Codes\Mar\Ctm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getCtmCode($data) {
		$table = Codes\Mar\Ctm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateCucCode($data) {
		$table = Codes\Mar\Cuc::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getCucCode($data) {
		$table = Codes\Mar\Cuc::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateMtmCode($data) {
		$table = Codes\Mar\Mtm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getMtmCode($data) {
		$table = Codes\Mar\Mtm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validatePty3Account($data) {
		$fields = ['custid|string', 'accountnbr|string', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$table = Armain\Pty3::instance();
		$desc = $table::DESCRIPTION_RECORD;
		$exists = $table->exists($data->custid, $data->accountnbr);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "$desc $data->accountnbr already exists";
		}

		if ($exists === false) {
			return "$desc $data->accountnbr not found";
		}
		return true;
	}

	public static function validatePty3CustidExists($data) {
		$fields = ['custid|string', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);

		$table = Armain\Pty3::instance();
		$desc = $table::DESCRIPTION_RECORD;
		$exists = $table->custidExists($data->custid);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "3rd Party Freight Customer $data->custid already exists";
		}

		if ($exists === false) {
			return "3rd Party Freight Customer $data->custid not found";
		}
		return true;
	}

	public static function getPty3Account($data) {
		$fields = ['custid|string', 'accountnbr|string'];
		self::sanitizeParametersShort($data, $fields);
		$table = Armain\Pty3::instance();

		if ($table->exists($data->custid, $data->accountnbr) === false) {
			return false;
		}
		return $table->recordJson($table->customerAccount($data->custid, $data->accountnbr));
	}

	public static function validateSicCode($data) {
		$table = Codes\Mar\Sic::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getSicCode($data) {
		$table = Codes\Mar\Sic::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateSpgpmCode($data) {
		$table = Codes\Mar\Spgpm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getSpgpmCode($data) {
		$table = Codes\Mar\Spgpm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateSpmCode($data) {
		$table = Codes\Mar\Spm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getSpmCode($data) {
		$table = Codes\Mar\Spm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateSucCode($data) {
		$table = Codes\Mar\Suc::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getSucCode($data) {
		$table = Codes\Mar\Suc::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateTmCode($data) {
		$table = Codes\Mar\Tm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getTmCode($data) {
		$table = Codes\Mar\Tm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateTrmCode($data) {
		$table = Codes\Mar\Trm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getTrmCode($data) {
		$table = Codes\Mar\Trm::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateTrmgCode($data) {
		$table = Codes\Mar\Trmg::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getTrmgCode($data) {
		$table = Codes\Mar\Trmg::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateWormCode($data) {
		$table = Codes\Mar\Worm::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getWormCode($data) {
		$table = Codes\Mar\Worm::getInstance();
		return self::getCodeTableCode($data, $table);
	}
}
