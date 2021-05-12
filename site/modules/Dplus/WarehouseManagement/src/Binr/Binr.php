<?php namespace Dplus\Wm;

class Binr extends Base {
	public function requestBinReassignment($vars, $debug = false) {
		$vars->frombin = strtoupper($vars->frombin);
		$vars->tobin   = strtoupper($vars->tobin);

		$data = ['BINR', "ITEMID=$vars->itemID"];
		if ($data->lotnbr) {
			$data[] = "LOTNBR=$vars->lotnbr";
		}
		if ($data->serialnbr) {
			$data[] = "SERIALNBR=$vars->serialnbr";
		}
		$data[] = "QTY=$vars->qty";
		$data[] = "FROMBIN=$vars->frombin";
		$data[] = "TOBIN=$vars->tobin";
		return $this->sendDplusRequest($data, $debug);
	}
}
