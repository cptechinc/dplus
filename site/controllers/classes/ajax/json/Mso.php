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
// Mvc Controllers
use Mvc\Controllers\AbstractController;

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

	private static function validator() {
		return new MsoValidator();
	}
}
