<?php namespace Dplus\Wm\Inventory\Whse\StockStatus;
// Dplus Model
use WhseLotserial as Model;
// ProcessWire
use ProcessWire\WireData;
// Dplus WM Inventory
use Dplus\Wm\Inventory\Whse\Lots\Lookup\ExcludePackBin as InvLots;
use Dplus\Wm\Inventory\Whse\StockStatus;

/**
 * StockStatus
 * Class for getting Stock Status
 */
class Provalley extends StockStatus {
/* =============================================================
	Data Functions
============================================================= */
	/**
	 * Return Data for Item and Bin
	 * @param  array  $item
	 * @return array
	 */
	protected function getBinItemData(array $item) {
		$data = parent::getBinItemData($item);
		$data['totals']['avgage'] = $this->getBinItemidAvgDays($item['binid'], $item['itemid']);
		return $data;
	}

	/**
	 * Return the Avg Date Difference for Expriation Date
	 * @param  string $binID   Bin ID
	 * @param  string $itemID  Item ID
	 * @return int
	 */
	protected function getBinItemidAvgDays($binID, $itemID) {
		$q = $this->inventory->queryWhse();
		$q->filterByBinid($binID)->filterByItemid($itemID);
		$q->withColumn("ROUND(AVG(DATEDIFF(curdate(), STR_TO_DATE(inltexpiredate, '%Y%m%d'))))", 'days');
		$q->select('days');
		return $q->findOne();
	}

	/**
	 * Return Lots for Item and Bin
	 * @param  string $binID   Bin ID
	 * @param  string $itemID  Item ID
	 * @return array
	 */
	protected function getBinItemidLots($binID, $itemID) {
		$lots = parent::getBinItemidLots($binID, $itemID);
		$data = [];
		foreach ($lots as $lot) {
			$r = $lot;
			$r['lotref'] = $this->getLotRef($lot['lotserial']);
			$data[] = $r;
		}
		return $data;
	}
}
