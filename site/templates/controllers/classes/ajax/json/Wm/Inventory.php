<?php namespace Controllers\Ajax\Json\Wm;
// Dplus
use Dplus\Wm\Inventory\BinInquiry;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Inventory extends Controller {
	public static function getItemLotserialBinQty($data) {
		$fields = ['itemID|string', 'binID|string', 'lotserial|text'];
		self::sanitizeParametersShort($data, $fields);

		$response = ['itemid' => $data->itemID, 'binid' => $data->binID, 'qty' => 0];

		$TABLE = BinInquiry::instance();

		if (empty($data->lotserial)) {
			$response['qty'] = floatval($TABLE->totalBinItemQty($data->binID, $data->itemID));
			return $response;
		}
		$response['lotserial'] = $data->lotserial;
		$response['qty'] = floatval($TABLE->totalBinItemLotserialQty($data->binID, $data->itemID, $data->lotserial));
		return $response;
	}
}