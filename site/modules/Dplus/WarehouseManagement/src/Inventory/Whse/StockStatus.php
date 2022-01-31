<?php namespace Dplus\Wm\Inventory\Whse;
// Dplus Model
use InvWhseLot as Model;
use ItemMasterItemQuery, ItemMasterItem;
use InvLotMasterQuery, InvLotMaster;
// ProcessWire
use ProcessWire\WireData;
// Dplus Configs
use Dplus\Configs;
use Dplus\Wm\Inventory\Whse\Lots\Lookup\ExcludePackBin as InvLots;

/**
 * StockStatus
 * Class for getting Stock Status
 */
class StockStatus extends WireData {
	protected $whseID;

	public function __construct() {
		$this->inventory = new InvLots();
	}

/* =============================================================
	Setter Functions
============================================================= */
	public function setWhseID($whseID) {
		$this->whseID = $whseID;
		$this->inventory->setWhseID($whseID);
	}

/* =============================================================
	Data Functions
============================================================= */
	/**
	 * Return Entire Stock Status
	 * @return array
	 */
	public function getData() {
		$items = $this->getItemsGroupedByBin();
		$data = [];

		foreach ($items as $item) {
			$data[$item['binid'] . '-' . $item['itemid']] = $this->getBinItemData($item);
		}
		return $data;
	}

	/**
	 * Return Data for Item and Bin
	 * @param  array  $item
	 * @return array
	 */
	protected function getBinItemData(array $item) {
		$lots = $this->getBinItemidLots($item['binid'], $item['itemid']);

		$data = [
			'binid'    => $item['binid'],
			'itemid'   => $item['itemid'],
			'itemDesc' => $this->getItemDescription($item['itemid']),
			'totals' => [
				'qty'      => $item['qty'],
				'lotcount' => sizeof($lots),
			],
			'lots' => $lots
		];
		return $data;
	}

	/**
	 * Return Item Description
	 * @param  string $itemID Item ID
	 * @return string
	 */
	protected function getItemDescription($itemID) {
		$q = ItemMasterItemQuery::create();
		$q->select(ItemMasterItem::aliasproperty('description'));
		return $q->findOneByItemid($itemID);
	}

	/**
	 * Return Lot Reference #
	 * @param  string $lotnbr Lot Number
	 * @return string
	 */
	protected function getLotRef($lotnbr) {
		$q = InvLotMasterQuery::create();
		$q->select(InvLotMaster::aliasproperty('lotref'));
		return $q->findOneByLotnbr($lotnbr);
	}

	/**
	 * Return Lots for Item and Bin
	 * @param  string $binID   Bin ID
	 * @param  string $itemID  Item ID
	 * @return array
	 */
	protected function getBinItemidLots($binID, $itemID) {
		$configSo = Configs\So::config();
		$decimalPlacesQty = $configSo->decimal_places_qty;
		$colQty = Model::aliasproperty('qty');

		$q = $this->inventory->queryWhse();
		$q->filterByBinid($binID)->filterByItemid($itemID);
		$q->withColumn(Model::aliasproperty('binid'), 'binid');
		$q->withColumn(Model::aliasproperty('itemid'), 'itemid');
		$q->withColumn(Model::aliasproperty('lotserial'), 'lotserial');
		$q->withColumn(Model::aliasproperty('lotserial'), 'lotserial');
		$q->withColumn("ROUND($colQty, $decimalPlacesQty)", 'qty');
		$q->withColumn(Model::aliasproperty('expiredate'), 'expiredate');
		$q->withColumn("DATEDIFF(curdate(), STR_TO_DATE(inltexpiredate, '%Y%m%d'))", 'days');
		$q->select(['binid', 'itemid', 'qty']);
		return $q->find()->toArray();
	}

	/**
	 * Return Summarized Item Data
	 * @return array
	 */
	public function getItemsGroupedByBin() {
		$configSo = Configs\So::config();
		$decimalPlacesQty = $configSo->decimal_places_qty;
		$colQty = Model::aliasproperty('qty');

		$q = $this->inventory->queryWhseBins();
		$q->withColumn("ROUND(SUM($colQty), $decimalPlacesQty)", 'qty');
		$q->withColumn(Model::aliasproperty('binid'), 'binid');
		$q->withColumn(Model::aliasproperty('itemid'), 'itemid');
		$q->select(['binid', 'itemid', 'qty']);
		$q->groupBy(['itemid', 'binid']);
		$q->orderBy('binid');
		return $q->find();
	}
}
