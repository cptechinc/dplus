<?php namespace Controllers\Ajax\Json;
// Propel
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
// use DplusUserQuery, DplusUser;
use SalesOrderDetailQuery, SalesOrderDetail;
use SalesHistoryDetailQuery, SalesHistoryDetail;
// ProcessWire Classes, Modules
use ProcessWire\ProcessWire;
use ProcessWire\WireData;
// Dplus Validators
use Dplus\CodeValidators\Mso	 as MsoValidator;
use Dplus\CodeValidators\Mso\Cxm as CxmValidator;
// Dplus Codes
use Dplus\Codes;
use Dplus\Xrefs;

class Mso extends AbstractJsonController {
	public static function test() {
		return 'test';
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
			'ordn'	  => $item->ordernumber,
			'linenbr' => $item->linenbr,
			'nonstock' => [
				'vendorid' => $item->nsvendorid,
				'vendoritemid' => $item->nsvendoritemid,
				'itemgroupid'  => $item->nsitemgroupid,
				'ponbr' 	   => $item->ponbr,
				'poref' 	   => $item->poref,
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

	public static function validateCxmXref(WireData $data) {
		$fields = ['custID|text', 'custitemID|text', 'new|bool', 'jqv|bool'];
		self::sanitizeParametersShort($data, $fields);

		$cxm = Xrefs\Cxm::instance();
		$exists = $cxm->exists($data->custID, $data->custitemID);
		$description = $cxm::DESCRIPTION_RECORD . "$data->custID|$data->custitemID";

		if (boolval($data->jqv) === false) {
			return boolval($data->new) ? $exists === false : $exists;
		}

		if (boolval($data->new) === true) {
			return $exists === false ? true : "$description already exists";
		}

		if ($exists === false) {
			return "$description not found";
		}
		return true;
	}

	public static function getPricing($data) {
		$fields = ['itemID|text', 'custID|text'];
		self::sanitizeParametersShort($data, $fields);
		$pricingM = self::pw('modules')->get('ItemPricing');
		$pricingM->request_search($data->itemID, $data->custID);

		$response = [
			'itemid' => $data->itemID,
			'price'  => 0.00,
			'pricebreaks' => []
		];

		if ($pricingM->has_pricing($data->itemID) === false) {
			return $response;
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
					'qty'	=> $pricing->$colQty,
					'price' => $pricing->$colPrice
				];
			}
		}
		$response['pricebreaks'] = $pricebreaks;
		return $response;
	}

	public static function validateLsmCode($data) {
		$table = Codes\Mso\Lsm::instance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getLsmCode($data) {
		$table = Codes\Mso\Lsm::instance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateMfcmCode($data) {
		$table = Codes\Mso\Mfcm::instance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getMfcmCode($data) {
		$table = Codes\Mso\Mfcm::instance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateRgarcCode($data) {
		$table = Codes\Mso\Rgarc::instance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getRgarcCode($data) {
		$table = Codes\Mso\Rgarc::instance();
		return self::getCodeTableCode($data, $table);
	}

	public static function validateRgascCode($data) {
		$table = Codes\Mso\Rgasc::instance();
		return self::validateCodeTableCode($data, $table);
	}

	public static function getRgascCode($data) {
		$table = Codes\Mso\Rgasc::instance();
		return self::getCodeTableCode($data, $table);
	}

	private static function validator() {
		return new MsoValidator();
	}
}
