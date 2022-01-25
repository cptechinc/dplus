<?php namespace Controllers\Mvi\Vi\Admin;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use OptionsViQuery, OptionsVi;
// Dplus User Options
use Dplus\UserOptions\Mvi\Vio as VioManager;
// MVC Controllers
use Mvc\Controllers\Controller;

class Vio extends Controller {
	const PERMISSION     = 'vio';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['userID|text'];
		self::sanitizeParametersShort($data, $fields);
		return self::options($data);
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text', 'userID|text']);
		$url = self::url();

		if ($data->action) {
			$vio = self::getVio();
			$vio->processInput(self::pw('input'));
			switch ($data->action) {
				case 'update':
					$url = self::url($data->userID);
					break;
			}
		}
		self::pw('session')->redirect($url, $http301);
	}

	private static function options($data) {
		$vio  = self::getVio();
		$user = $vio->userOrNew($data->userID);

		if ($user->isNew() === false) {
			self::pw('page')->headline = "VIO: $data->userID";
		}
		$vio->lockrecord($user);
		self::pw('page')->js .= self::pw('config')->twig->render('mvi/vio/js.twig');
		$html = self::displayOptions($data, $user);
		$vio->deleteResponse();
		return $html;
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayOptions($data, OptionsVi $user) {
		$vio = self::getVio();

		$html  = '';
		$html .= self::displayResponse($data);
		$html .= self::displayLockedAlert($data);
		$html .= self::pw('config')->twig->render('mvi/vio/display.twig', ['vio' => $vio, 'user' => $user]);
		return $html;
	}

	private static function displayInvalidPermission($data) {
		return self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Access Denied', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You don't have access to this function"]);
	}

	private static function displayLockedAlert($data) {
		$vio = self::getVio();

		if ($vio->recordlocker->getLockingUser($data->userID) != self::pw('user')->loginid) {
			$msg = "IIO $data->userID is being locked by " . $vio->recordlocker->getLockingUser($data->userID);
			$alert = self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "IIO $data->userID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			return '<div class="mb-3">'. $alert .'</div>';
		}
		return '';
	}

	private static function displayResponse($data) {
		$vio = self::getVio();
		$response = $vio->getResponse();
		if (empty($response)) {
			return '';
		}
		$alert = self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
		return '<div class="mb-3">'. $alert .'</div>';
	}

/* =============================================================
	URLs
============================================================= */
	public static function url($userID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=vio')->url);
		if ($userID) {
			$url->query->set('userID', $userID);
		}
		return $url->getUrl();
	}

	public static function deleteUrl($userID) {
		$url = new Purl(self::url($userID));
		$url->query->set('action', 'delete');
		return $url->getUrl();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMvi');

		$m->addHook('Page(pw_template=vio)::vioDeleteUrl', function($event) {
			$event->return = self::deleteUrl($event->arguments(0));
		});
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function getVio() {
		return VioManager::getInstance();
	}

	public static function validateVendoridPermission($data) {
		self::sanitizeParametersShort($data, ['vendorID|text']);
		return self::validateUserPermission($data);
	}

	protected static function validateUserPermission($data) {
		$user = self::pw('user');
		return $user->has_function(static::PERMISSION);
	}
}
