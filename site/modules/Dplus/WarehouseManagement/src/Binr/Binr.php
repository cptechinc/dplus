<?php namespace Dplus\Wm;

//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;

class Binr extends WireData {
	public function __construct() {
		$this->sessionID = session_id();
	}
	public function requestBinReassignment($vars, $debug = false) {
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
	public function sendDplusRequest(array $data, $debug = false) {
		$db = $this->wire('modules')->get('DplusOnlineDatabase')->db_name;
		$data = array_merge(["DBNAME=$db"], $data);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($this->wire('config')->cgis['warehouse'], $this->sessionID);
	}
}
