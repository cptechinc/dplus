<?php namespace Controllers\Ajax\Json\Wm;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
// ProcessWire Classes, Modules
use ProcessWire\Module, ProcessWire\ProcessWire;
// Dplus Filters
use Dplus\Filters\Min\ItemMaster as ItemMasterFilter;
// Dplus Validators
use Dplus\CodeValidators\Min as MinValidator;
use Dplus\CodeValidators\Mpo as MpoValidator;
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
		if ($r->strategies->enforceQty->warn() === false) {
			return false;
		}
		$qtyOrdered  = $r->items->sumQtyOrderedItemid($data->itemID);
		$qtyReceived = $r->items->getQtyReceivedItemid($data->itemID) + $data->qty;
		return $qtyReceived > $qtyOrdered;
	}

	static public function getLineLotserial($data) {
		$fields = ['ponbr|ponbr', 'linenbr|int', 'lotserial|text', 'binID|text'];
		self::sanitizeParametersShort($data, $fields);
		$validate = self::validatorMpo();

		if ($validate->po($data->ponbr) === false) {
			return true;
		}

		$r = new ReceivingCRUD();
		$r->setPonbr($data->ponbr);
		$r->init();
		if ($r->items->lineLotserialExists($data->linenbr, $data->lotserial, $data->binID) === false) {
			return false;
		}

		$lot = $r->items->getLineLotserial($data->linenbr, $data->lotserial, $data->binID);
		$item = $lot->item;
		$data = [
			'linenbr'   => $lot->linenbr,
			'itemid'    => $lot->itemid,
			'lotserial' => $lot->lotserial,
			'binid'     => $lot->binid,
			'lotref'    => $lot->lotref,
			'date'      => $lot->lotdate,
			'item' => [
				'itemid'      => $lot->itemid,
				'description' => $item->description,
				'description2' => $item->description2,
			],
			'uom' => [
				'code'        => $item->unitofmpurchase->code,
				'description' => $item->unitofmpurchase->description
			],
			'qty' => [
				'received'    => $r->items->getQtyReceivedLineLotserial($lot->linenbr, $lot->lotserial, $lot->binid),
			],
		];
		return $data;
	}

	private static function validatorMin() {
		return new MinValidator();
	}

	private static function validatorMpo() {
		return new MpoValidator();
	}
}
