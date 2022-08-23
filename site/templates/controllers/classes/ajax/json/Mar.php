<?php namespace Controllers\Ajax\Json;
// Dplus Models
use CustomerQuery, CustomerShiptoQuery;
// ProcessWire Mlasses, Modules
use ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Mar       as MarValidator;
use Dplus\Codes;
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

	public static function validateCcmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mar\Ccm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Commission Code $data->code already exists";
		}

		if ($exists === false) {
			return "Commission Code $data->code not found";
		}
		return true;
	}

	public static function getCcmCode($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$src = Codes\Mar\Ccm::getInstance();
		if ($src->exists($data->code) === false) {
			return false;
		}
		return $src->codeJson($src->code($data->code));
	}

	public static function validateCpmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mar\Ccm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Customer Price Code $data->code already exists";
		}

		if ($exists === false) {
			return "Customer Price Code $data->code not found";
		}
		return true;
	}

	public static function getCpmCode($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$src = Codes\Mar\Cpm::getInstance();
		if ($src->exists($data->code) === false) {
			return false;
		}
		return $src->codeJson($src->code($data->code));
	}

	public static function validateCocomCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mar\Cocom::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Country / Currency Code $data->code already exists";
		}

		if ($exists === false) {
			return "Country / Currency Code $data->code not found";
		}
		return true;
	}

	public static function getCocomCode($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$src = Codes\Mar\Cocom::getInstance();
		if ($src->exists($data->code) === false) {
			return false;
		}
		return $src->codeJson($src->code($data->code));
	}
	
	public static function validateCrcdCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mar\Crcd::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Credit Card Code $data->code already exists";
		}

		if ($exists === false) {
			return "Credit Card Code $data->code not found";
		}
		return true;
	}

	public static function getCrcdCode($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$src = Codes\Mar\Crcd::getInstance();
		if ($src->exists($data->code) === false) {
			return false;
		}
		return $src->codeJson($src->code($data->code));
	}
	
	public static function validateCrtmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mar\Crtm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Route Code $data->code already exists";
		}

		if ($exists === false) {
			return "Route Code $data->code not found";
		}
		return true;
	}

	public static function getCrtmCode($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$src = Codes\Mar\Crtm::getInstance();
		if ($src->exists($data->code) === false) {
			return false;
		}
		return $src->codeJson($src->code($data->code));
	}

	public static function validateCucCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mar\Cuc::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Customer User Code $data->code already exists";
		}

		if ($exists === false) {
			return "Customer User Code  $data->code not found";
		}
		return true;
	}

	public static function getCucCode($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$src = Codes\Mar\Cuc::getInstance();
		if ($src->exists($data->code) === false) {
			return false;
		}
		return $src->codeJson($src->code($data->code));
	}

	public static function validateSicCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mar\Sic::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Standard Industrial Class $data->code already exists";
		}

		if ($exists === false) {
			return "Standard Industrial Class $data->code not found";
		}
		return true;
	}

	public static function getSicCode($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$src = Codes\Mar\Sic::getInstance();
		if ($src->exists($data->code) === false) {
			return false;
		}
		return $src->codeJson($src->code($data->code));
	}

	public static function validateSpgpmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mar\Spgpm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Salesperson Group Code $data->code already exists";
		}

		if ($exists === false) {
			return "Salesperson Group Code $data->code not found";
		}
		return true;
	}

	public static function getSpgpmCode($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$src = Codes\Mar\Spgpm::getInstance();
		if ($src->exists($data->code) === false) {
			return false;
		}
		return $src->codeJson($src->code($data->code));
	}

	public static function validateSucCode($data) {
		$table = Codes\Mar\Suc::getInstance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getSucCode($data) {
		$table = Codes\Mar\Suc::getInstance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateWormCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mar\Worm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Write-Off Code $data->code already exists";
		}

		if ($exists === false) {
			return "Write-Off Group Code $data->code not found";
		}
		return true;
	}

	public static function getWormCode($data) {
		self::sanitizeParametersShort($data, ['code|text']);

		$src = Codes\Mar\Worm::getInstance();
		if ($src->exists($data->code) === false) {
			return false;
		}
		return $src->codeJson($src->code($data->code));
	}
}
