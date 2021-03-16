<?php namespace Controllers\Ajax\Json;
// Dplus Model
use ItemXrefVendorQuery, ItemXrefVendor;
use PurchaseOrderDetailQuery, PurchaseOrderDetail;
use PurchaseOrderQuery, PurchaseOrder;
use PhoneBookQuery, PhoneBook;
use VendorQuery, Vendor;
// ProcessWire Mlasses, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Validators
use Dplus\CodeValidators\Map       as MapValidator;
use Dplus\CodeValidators\Map\Vxm   as VxmValidator;
use Dplus\CodeValidators\Map\Mxrfe as MxrfeValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Map extends AbstractController {
	public static function test() {
		return 'test';
	}

	public static function validateVendorid($data) {
		$fields = ['vendorID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new MapValidator();

		if ($validate->vendorid($data->vendorID) === false) {
			return "Vendor $data->vendorID not found";
		}
		return true;
	}

	public static function validateVxm($data) {
		$exists = false;
		$fields = ['vendorID|text', 'vendoritemID|text', 'itemID|text', 'jqv|bool', 'new|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new VxmValidator();
		$exists = $validate->exists($data->vendorID, $data->vendoritemID, $data->itemID);

		// If trying to validate new item
		if ($data->new) {
			$valid = $exists === false;
			if ($data->jqv && $valid === false) {
				return "$data->vendoritemID from $data->vendorID for $data->itemID already exists";
			}
			return $valid;
		}

		if ($data->jqv && $exists === false) {
			return "$data->vendoritemID from $data->vendorID for $data->itemID was not found in the Vendor X-ref";
		}
		return $exists;
	}

	public static function validateVxmCanBePrimary($data) {
		$fields = ['vendorID|text', 'vendoritemID|text', 'itemID|text', 'jqv|bool'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new VxmValidator();
		$vxm = self::pw('modules')->get('XrefVxm');
		if ($vxm->poordercode_primary_exists($data->itemID) === false) {
			return true;
		}
		$primary = $vxm->get_primary_poordercode_itemid($data->itemID);

		if ($primary->vendorid == $data->vendorID && $primary->vendoritemid == $data->vendoritemID) {
			return true;
		}
		// FALSE Return
		if ($data->jqv) {
			return "$primary->ouritemID already has a Primary Vendor X-ref";
		}
		return false;
	}

	public static function validateVxmExistsForItemid($data) {
		$fields = ['vendorID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new VxmValidator();
		return $validate->vendor_has_xref_itemid($data->itemID, $data->vendorID);
	}

	public static function validateVendoritemMatchesItemid($data) {
		$fields = ['vendoritemID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new VxmValidator();
		return $validate->vendoritemid_matches_itemid($data->vendoritemID, $data->itemID);
	}

	public static function getVxm($data) {
		$fields = ['vendorID|text', 'vendoritemID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new VxmValidator();

		if ($validate->exists($data->vendorID, $data->vendoritemID, $data->itemID) === false) {
			return false;
		}

		$xref = self::pw('modules')->get('XrefVxm')->xref($data->vendorID, $data->vendoritemID, $data->itemID);
		return array(
			'vendorid'     => $xref->vendorid,
			'itemid'       => $xref->itemid,
			'vendoritemid' => $xref->vendoritemid
		);
	}

	public static function getVxmPrimary($data) {
		$fields = ['itemID|text'];
		$vxm = self::pw('modules')->get('XrefVxm');
		if ($vxm->poordercode_primary_exists($data->itemID) === false) {
			return false;
		}
		$primary = $vxm->get_primary_poordercode_itemid($data->itemID);
		$data->vendorID = $primary->vendorid;
		$data->vendoritemID = $primary->vendoritemid;
		return self::getVxm($data);
	}

	public static function getVxmByItemid($data) {
		$fields = ['vendorID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new VxmValidator();

		if ($validate->vendor_has_xref_itemid($data->vendorID, $data->itemID) === false) {
			return false;
		}

		$q = ItemXrefVendorQuery::create();
		$q->filterByItemid($data->itemID)->filterByVendorid($data->vendorID);
		if ($validate->vendor_has_primary($data->vendorID, $data->itemID)) {
			$q->filterByPo_ordercode(ItemXrefVendor::POORDERCODE_PRIMARY);
		}
		$xref = $q->findOne();
		$response = array(
			'vendorid'     => $xref->vendorid,
			'itemid'       => $xref->itemid,
			'vendoritemid' => $xref->vendoritemid
		);
		return $response;
	}

	public function validateVxmUpdateItmCost($data) {
		$fields = ['vendorID|text', 'vendoritemID|text', 'itemID|text', 'ordercode|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$response = ['allow' => false, 'confirm' => false];
		$validate = new VxmValidator();
		if ($validate->exists($data->vendorID, $data->vendoritemID, $data->itemID) === false) {
			return $response;
		}
		$vxm = self::pw('modules')->get('XrefVxm');
		$vxm->init_configs();
		$xref = $vxm->xref($data->vendorID, $data->vendoritemID, $data->itemID);
		if (array_key_exists($ordercode, ItemXrefVendor::OPTIONS_POORDERCODE)) {
			$xref->setPo_ordercode($ordercode);
		}
		$response['allow'] = $vxm->allow_itm_cost_update_xref($xref);
		if ($response['allow']) {
			$response['confirm'] = $vxm->configs->ap->confirm_update_itm_cost();
		}
		return $response;
	}

	public static function validateMxrfe($data) {
		$fields = ['mnfrID|text', 'mnfritemID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new MxrfeValidator();

		if ($validate->exists($data->mnfrID, $data->mnfritemID, $data->itemID) === false) {
			return "MXRFE X-ref not found";
		}
		return true;
	}

	public static function validateMxrfeNew($data) {
		$fields = ['mnfrID|text', 'mnfritemID|text', 'itemID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$validate = new MxrfeValidator();

		if ($validate->exists($data->mnfrID, $data->mnfritemID, $data->itemID) === false) {
			return true;
		}
		return "MXRFE X-ref exists";
	}

	public static function getVendor($data) {
		$fields = ['vendorID|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$q = new VendorQuery();
		$q->filterByVendorid($data->vendorID);
		if ($q->count() === 0) {
			return false;
		}
		$v = $q->findOne();
		$response = [
			'id'   => $v->id,
			'name' => $v->name,
			'address' => [
				'address1' => $v->address,
				'address2' => $v->address2,
				'city'     => $v->city,
				'state'    => $v->state,
				'zip'      => $v->zip,
			]
		];
		return $response;
	}

	public static function getVendorContact($data) {
		$fields = ['vendorID|text', 'shipfromID|text', 'contact|text'];
		$data = self::sanitizeParametersShort($data, $fields);
		$q = new PhoneBookQuery();
		$q->filterByVendorid($data->vendorID);
		$q->filterByType([PhoneBook::TYPE_VENDOR, PhoneBook::TYPE_VENDORCONTACT]);
		$q->filterByShipfromid($data->shipfromID);
		$q->filterByContact($data->contact);
		if ($q->count() === 0) {
			return false;
		}
		$c = $q->findOne();
		$sanitizer = self::pw('sanitizer');
		$response = [
			'vendorid'   => $c->vendorid,
			'shipfromid' => $c->shipfromid,
			'contact'    => $c->contact,
			'phone'      => $sanitizer->phoneus($c->phone),
			'extension'  => $c->extension,
			'fax'        => $sanitizer->phoneus($c->fax)
		];
		return $response;
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
				'received' => number_format($line->qty_receipt() / $line->itm->weight, $configs->decimal_places_qty()),
				'invoiced' => number_format($line->qty_invoiced(), $configs->decimal_places_qty())
			],
			'cost'         => number_format($line->cost, $configs->decimal_places_cost()),
			'cost_total'   => number_format($line->cost_total, $configs->decimal_places_cost()),
			'itm' => [
				'weight'   => number_format($line->itm->weight, $configs->decimal_places_qty())
			]
		];
		return $response;
	}
}
