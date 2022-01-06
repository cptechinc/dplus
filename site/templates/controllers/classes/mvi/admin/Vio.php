<?php namespace Controllers\Mvi\Vi\Admin;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use OptionsViQuery, OptionsVi;
// Dplus User Options
use Dplus\UserOptions\Mvi\Vio as VioManager;
// MVC Controllers
use Mvc\Controllers\AbstractController;

class Vio extends AbstractController {
	const PERMISSION     = 'vio';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['userID|text'];
		self::sanitizeParametersShort($data, $fields);
		return self::options($data);
	}

	private static function options($data) {
		$vio  = self::getVio();
		$user = $vio->userOrNew($data->userID);

		if ($user->isNew() === false) {
			self::pw('page')->headline = "VIO: $data->userID";
		}
		$vio->lockrecord($user);
		self::pw('page')->js .= self::pw('config')->twig->render('mvi/vio/js.twig');
		return self::displayOptions($data, $user);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayOptions($data, OptionsVi $user) {
		$vio = self::getVio();

		$html  = '';
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

/* =============================================================
	Supplemental
============================================================= */
	public static function getVio() {
		return VioManager::getInstance();
	}

	public static function validateVendoridPermission($data) {
		self::sanitizeParametersShort($data, ['vendorID|text']);
		$user = self::pw('user');

		if (self::validateVendorid($data->vendorID) === false) {
			return false;
		}

		if (self::validateUserPermission($data) === false) {
			return false;
		}
		return true;
	}

	protected static function validateUserPermission($data) {
		$user = self::pw('user');
		// $vio  = self::getVio();

		if ($user->has_function(static::PERMISSION) === false) {
			return false;
		}

		// if ($vio->allowUser($user, static::PERMISSION_VIO) === false) {
		// 	return false;
		// }
		return true;
	}
}
