<?php namespace Controllers\Ajax\Json\Wm;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Filters
use Dplus\Filters\Min\ItemMaster as ItemMasterFilter;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
// Dplus Wm
use Dplus\Wm\Receiving\Receiving as ReceivingCRUD;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Receiving extends AbstractController {

	public static function requireLotserial($data) {
		$fields = ['itemID|text', 'jqv|bool', 'lotserial|text'];
		self::sanitizeParametersShort($data, $fields);
		$validate = self::validatorMin();

		if ($validate->itemid($data->itemID) === false) {
			return $data->jqv ? "$data->itemID not found" : false;
		}
		if (empty($data->lotserial) === false) {
			return true;
		}
		$filter = new ItemMasterFilter();
		$filter->query->filterByItemid($data->itemID);
		$filter->query->filterByItemtype([ItemMasterItem::ITEMTYPE_SERIALIZED, ItemMasterItem::ITEMTYPE_LOTTED]);
		if ($filter->query->count()) {
			return $data->jqv ? "Lotserial is Required" : true;
		}
		return $data->jqv ? true : false;
	}

	static public function allowItemOnOrder($data) {
		self::sanitizeParametersShort($data, ['itemID|text', 'ponbr|ponbr', 'jqv|bool']);
		$r = new ReceivingCRUD();
		$r->setPonbr($data->ponbr);
		$r->init();

		if ($r->items->allowItemid($data->itemID)) {
			return true;
		}
		return $data->jqv ? "Item Not Allowed on PO" : false;
	}

	static public function doesQtyAddNeedWarning($data) {
		$fields = ['itemID|text', 'qty|float', 'ponbr|ponbr'];
		self::sanitizeParametersShort($data, $fields);
		$validate = self::validatorMin();

		if ($validate->itemid($data->itemID) === false) {
			return true;
		}

		$r = new ReceivingCRUD();
		$r->setPonbr($data->ponbr);
		$r->init();
		if ($r->enforceQty->warn() === false) {
			return false;
		}
		$qtyOrdered  = $r->items->getQtyOrderedItemid($data->itemID);
		$qtyReceived = $r->items->getQtyReceivedItemid($data->itemID) + $data->qty;
		return $qtyReceived > $qtyOrdered;
	}

	private static function validatorMin() {
		return new MinValidator();
	}
}
