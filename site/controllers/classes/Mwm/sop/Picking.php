<?php namespace Controllers\Wm\Sop\Picking;

use stdClass;
// Purl Library
use Purl\Url as Purl;
// Propel Classes
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Model
use WarehouseQuery, Warehouse;
// Dpluso Model
use WhseitemphysicalcountQuery, Whseitemphysicalcount;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module, ProcessWire\WireData;
use Processwire\SearchInventory, Processwire\WarehouseManagement,ProcessWire\HtmlWriter;
// Dplus Configs
use Dplus\Configs as Dconfigs;
// Dplus Validators
use Dplus\CodeValidators\Mso as MsoValidator;
// Dplus CRUD
use Dplus\Wm\Sop\Picking\Picking as PickingCRUD;
use Dplus\Wm as Wm;
// Mvc Controllers
use Controllers\Wm\Base;

class Picking extends Base {
	const DPLUSPERMISSION = 'porpk';

	/** @var PickingCRUD */
	static private $picking;
	/** @var MsoValidator */
	static private $validateMso;

/* =============================================================
	Indexes
============================================================= */
	static public function index($data) {
		$fields = ['scan|text', 'action|text', 'ordn|ordn'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->ordn) === false) {
			return self::picking($data);
		}
		$picking  = self::getPicking();
		$wSession = self::getWhsesession();

		if ($wSession->is_pickingunguided() === false) {
			$picking->requestStartPicking();
		}
		$html = self::pw('config')->twig->render('warehouse/picking/sales-order-form.twig');
		return $html;
	}

	static public function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['action|text', 'ordn|ordn', 'scan|text']);

		$validate = self::getValidatorMso();
		if (empty($data->ordn) === false && $validate->order($data->ordn) === false) {
			self::redirect(self::pickingUrl($data->ordn), $http301 = false);
		}

		$m = self::getPicking($data->ordn);
		$m->processInput(self::pw('input'));

		// REDIRECT
		switch ($data->action) {
			default:
				self::redirect(self::pickingUrl($data->ordn), $http301 = false);
				break;
		}
	}

/* =============================================================
	Data Processing
============================================================= */

/* =============================================================
	URLs
============================================================= */
	static public function pickingUrl($ordn = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=whse-picking')->url);
		if ($ordn) {
			$url->query->set('ordn', $ordn);
		}
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */

/* =============================================================
	Validator, Module Getters
============================================================= */
	static public function validateUserPermission(user $user = nul) {
		if (empty($user)) {
			$user = self::pw('user');
		}
		return $user->has_function(self::DPLUSPERMISSION);
	}

	static public function getPicking($ordn = '') {
		self::pw('modules')->get('WarehouseManagement');

		if (empty(self::$picking)) {
			self::$picking = new PickingCRUD();
			self::$picking->setSessionid(self::getSessionid());
		}
		if ($ordn) {
			self::$picking->setOrdn($ordn);
		}
		self::$picking->init();
		return self::$picking;
	}

	static public function getValidatorMso() {
		if (empty(self::$validateMso)) {
			self::$validateMso = new MsoValidator();
		}
		return self::$validateMso;
	}

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('WarehouseManagement');


	}
}
