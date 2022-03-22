<?php namespace Controllers\Ajax\Json;
// Propel
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
use DplusUserQuery, DplusUser;
use SalesOrderDetailQuery, SalesOrderDetail;
use SalesHistoryDetailQuery, SalesHistoryDetail;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Mso     as MsoValidator;
use Dplus\CodeValidators\Mso\Cxm as CxmValidator;
// Dplus Codes
use Dplus\Codes;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Mso extends Controller {
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

		$freight = codes\Mso\Mfcm::getInstance()->code($data->code);
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

	public static function getSalesOrderDetail($data) {
		$fields = ['ordn|text', 'linenbr|int'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		if ($validate->invoice($data->ordn)) {
			return self::getSalesHistoryDetail($data);
		}
		$q = SalesOrderDetailQuery::create()->filterByOrdernumber($data->ordn)->filterByLinenbr($data->linenbr);

		if (boolval($q->count()) === false) {
			return false;
		}

		$item = $q->findOne();
		$response = self::getSalesDetailResponse($item);
		return $response;
	}

	public static function getSalesHistoryDetail($data) {
		$fields = ['ordn|text', 'linenbr|int'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		if ($validate->order($data->ordn)) {
			return self::getSalesOrderDetail($data);
		}
		$q = SalesHistoryDetailQuery::create()->filterByOrdernumber($data->ordn)->filterByLinenbr($data->linenbr);

		if (boolval($q->count()) === false) {
			return false;
		}

		$item = $q->findOne();
		$response = self::getSalesDetailResponse($item);
		return $response;
	}

	/**
	 * Return SalesHistoryDetail|SalesOrderDetail Data
	 * @param  ActiveRecordInterface|SalesHistoryDetail|SalesOrderDetail $item
	 * @return array
	 */
	private static function getSalesDetailResponse(ActiveRecordInterface $item) {
		$response = [
			'ordn'    => $item->ordernumber,
			'linenbr' => $item->linenbr,
			'nonstock' => [
				'vendorid' => $item->nsvendorid,
				'vendoritemid' => $item->nsvendoritemid,
				'itemgroupid'  => $item->nsitemgroupid,
				'ponbr'        => $item->ponbr,
				'poref'        => $item->poref,
			]
		];
		return $response;
	}

	public static function validateCxm($data) {
		$fields = ['custID|text', 'custitemID|text', 'new|bool', 'jqv|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new CxmValidator();
		$exists = $validate->exists($data->custID, $data->custitemID);

		if ($data->new) {
			$valid = $exists === false;

			if ($valid === false && $data->jqv) {
				return "X-ref $data->custID-$data->custitemID exists";
			}
			return $valid;
		}
		if ($exists === false && $data->jqv) {
			return "X-ref $data->custID-$data->custitemID not found";
		}
		return $exists;
	}

	public static function getPricing($data) {
		$fields = ['itemID|text', 'custID|text'];
		self::sanitizeParametersShort($data, $fields);
		$pricingM = self::pw('modules')->get('ItemPricing');
		$pricingM->request_search($data->itemID, $data->custID);

		if ($pricingM->has_pricing($data->itemID) === false) {
			return false;
		}
		$pricing = $pricingM->get_pricing($data->itemID);
		$pricebreaks = [
			['qty' => $pricing->priceqty1, 'price' => $pricing->priceprice1]
		];

		for ($i = 2; $i <= 6; $i++) {
			$colQty   = 'priceqty' . $i;
			$colPrice = 'priceprice' . $i;

			if ($pricing->$colQty > 0) {
				$pricebreaks[] = [
					'qty'   => $pricing->$colQty,
					'price' => $pricing->$colPrice
				];
			}
		}

		$response = [
			'itemid' => $data->itemID,
			'price'  => $pricing->price,
			'pricebreaks' => $pricebreaks
		];
		return $response;

	public static function validateLsmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mso\Lsm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Lost Sales Reason $data->code already exists";
		}

		if ($exists === false) {
			return "Lost Sales Reason $data->code not found";
		}
		return true;
	}

	public static function getLsmCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mso\Lsm::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	public static function validateMfcmCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mso\Mfcm::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "Motor Freight Code $data->code already exists";
		}

		if ($exists === false) {
			return "Motor Freight Code $data->code not found";
		}
		return true;
	}

	public static function getMfcmCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mso\Mfcm::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	public static function validateRgarcCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mso\Rgarc::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "RGA/Return Reason Code $data->code already exists";
		}

		if ($exists === false) {
			return "RGA/Return Reason Code $data->code not found";
		}
		return true;
	}

	public static function getRgarcCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mso\Rgarc::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	public static function validateRgascCode($data) {
		$fields = ['code|text', 'jqv|bool', 'new|bool'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mso\Rgasc::getInstance();
		$exists = $manager->exists($data->code);

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "RGA/Return Ship Via Code $data->code already exists";
		}

		if ($exists === false) {
			return "RGA/Return Ship Via Code $data->code not found";
		}
		return true;
	}

	public static function getRgascCode($data) {
		$fields = ['code|text'];
		self::sanitizeParametersShort($data, $fields);

		$manager = Codes\Mso\Rgasc::getInstance();

		if ($manager->exists($data->code) === false) {
			return false;
		}
		return $manager->codeJson($manager->code($data->code));
	}

	private static function validator() {
		return new MsoValidator();
	}
}
