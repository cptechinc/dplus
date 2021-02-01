<?php namespace Controllers\Ajax\Json;

use ProcessWire\Module, ProcessWire\ProcessWire;

use Mvc\Controllers\AbstractController;

use ItemXrefVendorQuery, ItemXrefVendor;
use PurchaseOrderDetailQuery, PurchaseOrderDetail;
use PurchaseOrderQuery, PurchaseOrder;

class Map extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateVendorid($data) {
		$fields = ['vendorID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->vendorid($data->vendorID) === false) {
			return "Vendor $data->vendorID not found";
		}
		return true;
	}

	public static function validateVxm($data) {
		$fields = ['vendorID|text', 'vendoritemID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		if ($validate->vxm->exists($data->vendorID, $data->vendoritemID, $data->itemID) === false) {
			return "VXM X-ref not found";
		}
		return true;
	}

	public static function validateVxmExistsForItemid($data) {
		$fields = ['vendorID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();
		return $validate->vxm->vendor_has_xref_itemid($data->itemID, $data->vendorID);
	}

	public static function getVxm($data) {
		$fields = ['vendorID|text', 'vendoritemID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->vxm->exists($data->vendorID, $data->vendoritemID, $data->itemID) === false) {
			return false;
		}

		$xref = $modules->get('XrefVxm')->xref($data->vendorID, $data->vendoritemID, $data->itemID);
		return array(
			'vendorid'     => $xref->vendorid,
			'itemid'       => $xref->itemid,
			'vendoritemid' => $xref->vendoritemid
		);
	}

	public static function getVxmByItemid($data) {
		$fields = ['vendorID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->vxm->vendor_has_xref_itemid($data->vendorID, $data->itemID) === false) {
			return false
		}

		$q = ItemXrefVendorQuery::create()->filterByItemid($data->itemID)->filterByVendorid($data->vendorID);
		if ($validate->vxm->vendor_has_primary($data->vendorID, $data->itemID)) {
			$q->filterByPo_ordercode(ItemXrefVendor::POORDERCODE_PRIMARY);
		}
		$xref = $q->findOne();
		$response = array(
			'vendorid'     => $xref->vendorid,
			'itemid'       => $xref->itemid,
			'vendoritemid' => $xref->vendoritemid
		);
	}

	public static function validateMxrfe($data) {
		$fields = ['mnfrID|text', 'mnfritemID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->mxrfe->exists($data->mnfrID, $data->mnfritemID, $data->itemID) === false) {
			return "MXRFE X-ref not found";
		}
		return true;
	}

	public static function validateMxrfeNew($data) {
		$fields = ['mnfrID|text', 'mnfritemID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = self::validator();

		if ($validate->mxrfe->exists($data->mnfrID, $data->mnfritemID, $data->itemID) === false) {
			return true;
		}
		return "MXRFE X-ref exists";
	}

	public function getPoItem($data) {
		$fields = ['ponbr|text', 'linenbr|int'];
		$data = self::sanitizeParametersShort($data, $fields);
		$data->ponbr = PurchaseOrder::get_paddedponumber($data->ponbr);
		$q = PurchaseOrderDetailQuery::create()->filterByPonbr($ponbr)->filterByLinenbr($linenbr);

		if (boolval($q->count()) === false) {
			return false;
		}
		$configs = self::pw('modules')->get('PurchaseOrderEditConfigs');
		$configs->init_configs();

		$response = [
			'linenbr'      => $linenbr,
			'itemid'       => $line->itemid,
			'description'  => $line->description,
			'vendoritemid' => $line->vendoritemid,
			'whseid'       => $line->whse,
			'specialorder' => $line->specialorder,
			'uom'          => $line->uom,
			'qty' => [
				'ordered'  => number_format($line->qty_ordered, $configs->decimal_places_qty()),
				'received' => number_format($line->qty_receipt() / $line->itm->weight, $configs->decimal_places_qty()),
				'invoiced' => number_format($line->qty_invoiced(), $configs->decimal_places_qty())
			],
			'cost'         => number_format($line->cost, $configs->decimal_places_cost()),
			'cost_total'   => number_format($line->cost_total, $configs->decimal_places_cost()),
			'itm' => [
				'weight'   => number_format($line->itm->weight, $configs->decimal_places_qty())
			]
		];
	}

	private static function validator() {
		return self::pw('modules')->geT('ValidateMap');
	}
}
