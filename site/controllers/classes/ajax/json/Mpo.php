<?php namespace Controllers\Ajax\Json;

use ProcessWire\Module, ProcessWire\ProcessWire;

use Mvc\Controllers\AbstractController;

use PurchaseOrderQuery, PurchaseOrder;
use PurchaseOrderDetailQuery, PurchaseOrderDetail;

use Dplus\CodeValidators\Mpo       as MpoValidator;

class Mpo extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validatePonbr($data) {
		$fields = ['ponbr|ponbr'];
		$data = self::sanitizeParametersShort($data, $fields);
		return $data->ponbr;
		$validate = new MpoValidator();

		if ($validate->po($data->ponbr) === false) {
			return "Purchase Order $data->ponbr not found";
		}
		return true;
	}

	public static function getPoItem($data) {
		$fields = ['ponbr|text', 'linenbr|int'];
		$data = self::sanitizeParametersShort($data, $fields);
		$data->ponbr = PurchaseOrder::get_paddedponumber($data->ponbr);
		$q = PurchaseOrderDetailQuery::create()->filterByPonbr($data->ponbr)->filterByLinenbr($data->linenbr);

		if (boolval($q->count()) === false) {
			return false;
		}
		$configs = self::pw('modules')->get('PurchaseOrderEditConfigs');
		$configs->init_configs();
		$line = $q->findOne();

		$response = [
			'linenbr'      => $line->linenbr,
			'itemid'       => $line->itemid,
			'description'  => $line->description,
			'vendoritemid' => $line->vendoritemid,
			'whseid'       => $line->whse,
			'specialorder' => $line->specialorder,
			'uom'          => $line->uom,
			'qty' => [
				'ordered'  => number_format($line->qty_ordered, $configs->decimal_places_qty()),
				'received' => number_format($line->qty_receipt(), $configs->decimal_places_qty()),
				'invoiced' => number_format($line->qty_invoiced(), $configs->decimal_places_qty())
			],
			'cost'         => number_format($line->cost, $configs->decimal_places_cost()),
			'cost_total'   => number_format($line->cost_total, $configs->decimal_places_cost()),
			'itm' => [
				'weight'   => number_format($line->itm ? $line->itm->weight : 0.0, $configs->decimal_places_qty())
			]
		];
		return $response;
	}
}
