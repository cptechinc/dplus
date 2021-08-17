<?php namespace Controllers\Mpo\PurchaseOrder;
// Dplus Model
use PurchaseOrder;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\PurchaseOrderEdit as EpoModel;
// Dplus Validators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;
use Controllers\Mpo\PurchaseOrder\Base;

class Received extends Base {
	const DPLUSPERMISSION = 'mpo';
	const JSONCODE = 'po-received';

	private static $jsonm;

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['ponbr|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->ponbr) === false) {
			return self::po($data);
		}
		return self::initScreen($data);
	}

	public static function po($data) {
		$fields = ['ponbr|ponbr'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validator()->po($data->ponbr) === false) {
			return self::invalidPo($data);
		}
		return self::received($data);
	}

	private static function received($data) {
		self::requestJson($data);
		$verified = self::verifyData($data);
		if ($verified === true) {
			self::pw('page')->headline = "Viewing Receipts for PO # $data->ponbr";
		}
		return self::display($data);
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function requestJson($data) {
		self::requestData($data);
	}

/* =============================================================
	Data Processing
============================================================= */
	private static function verifyData($data) {
		self::sanitizeParametersShort($data, ['scan|text']);

		$jsonm = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');

		if ($jsonm->exists(self::JSONCODE) === false) {
			$session->setFor(self::JSONCODE, $data->ponbr, ($session->getFor(self::JSONCODE, $data->ponbr) + 1));
			if ($session->getFor(self::JSONCODE, $data->ponbr) > 3) {
				return false;
			}
			$session->redirect(self::scanUrl($data->ponbr));
		}

		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['error'] === true) {
				return true;
			}

			if ($json['rcptconfirm'] != $data->ponbr) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::receievedUrl($data->ponbr), $http301 = false);
			}
			$session->setFor(self::JSONCODE, $data->ponbr, 0);
			return true;
		}

		if ($session->getFor(self::JSONCODE, $data->ponbr) > 3) {
			return false;
		}
		$session->setFor(self::JSONCODE, $data->ponbr, ($session->getFor(self::JSONCODE, $data->ponbr) + 1));
		$session->redirect(self::poReceivedUrl($data->ponbr), $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	private static function initScreen($data) {
		return self::lookupForm($data);
	}

	private static function display($data) {
		$jsonm = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Activity File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}

		$html = '';
		$html .= $config->twig->render('purchase-orders/purchase-order/received/display.twig', ['json' => $json]);
		return $html;
	}


/* =============================================================
	Requests
============================================================= */
	private static function requestData($data) {
		self::sendRequest(['RCPTCONFIRM', "PONBR=$data->ponbr"]);
	}

	private static function sendRequest(array $data, $sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = self::pw('modules')->get('DplusOnlineDatabase')->db_name;
		$data = array_merge(["DBNAME=$db"], $data);
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['warehouse'], $sessionID);
	}

/* =============================================================
	Validator, Module Getters
============================================================= */
	public static function validateUserPermission(User $user = null) {
		if (empty($user)) {
			$user = self::pw('user');
		}
		return $user->has_function(self::DPLUSPERMISSION);
	}

	public static function getJsonModule() {
		if (empty(self::$jsonm)) {
			self::$jsonm = self::pw('modules')->get('JsonDataFilesSession');
		}
		return self::$jsonm;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMpo');

		$m->addHook('Page(pw_template=purchase-order-received)::poUrl', function($event) {
			$event->return = self::poUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=purchase-order-received)::receivedUrl', function($event) {
			$event->return = self::poReceivedUrl($event->arguments(0));
		});
	}

}
