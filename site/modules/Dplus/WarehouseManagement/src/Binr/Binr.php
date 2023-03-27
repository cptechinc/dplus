<?php namespace Dplus\Wm;

class Binr extends Base {
	public function requestBinReassignment($vars, $debug = false) {
		$vars->frombin = strtoupper($vars->frombin);
		$vars->tobin   = strtoupper($vars->tobin);

		$data = ['BINR', "ITEMID=$vars->itemID"];

		if ($vars->lotnbr) {
			$data[] = "LOTNBR=$vars->lotnbr";
		}
		if ($vars->serialnbr) {
			$data[] = "SERIALNBR=$vars->serialnbr";
		}
		$data[] = "QTY=$vars->qty";
		$data[] = "FROMBIN=$vars->frombin";
		$data[] = "TOBIN=$vars->tobin";
		return $this->sendDplusRequest($data, $debug);
	}
}
