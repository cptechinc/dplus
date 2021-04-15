<?php namespace Controllers\Min\Itm;
// Purl URI Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Itm as ItmModel;
// Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class ItmFunction extends AbstractController {
	const PERMISSION_ITMP = '';

	private static $minvalidator;

/* =============================================================
	Validations
============================================================= */
	protected static function validateItemid($data) {
		$data = self::sanitizeParametersShort($data, ['itemID|text']);
		$wire = self::pw();
		$validate = self::getMinValidator();

		if ($validate->itemid($data->itemID) === false) {
			$wire->wire('session')->redirect($wire->wire('page')->itmURL($data->itemID), $http301 = false);
		}
		return true;
	}

	protected static function validateUserPermission() {
		$wire = self::pw();
		$user = $wire->wire('user');
		$itmp = $wire->wire('modules')->get('Itmp');
		$page = $wire->wire('page');
		if ($user->has_function('itm') === false) {
			return false;
		}
		if (static::PERMISSION_ITMP != '') {
			return $itmp->isUserAllowed($user, static::PERMISSION_ITMP);
		}
		return $itmp->is_user_allowed_template($user, $page->pw_template);
	}

	protected static function validateItemidAndPermission($data) {
		self::validateItemid($data);

		if (static::validateUserPermission() === false) {
			$page   = self::pw('page');
			$config = self::pw('config');
			if (isset($data->action) === false) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "You don't have access to this function", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Permission: ITM $page->name"]);
			}
			return false;
		}
		return true;
	}

/* =============================================================
	Display
============================================================= */
	protected static function breadCrumbs() {
		return self::pw('config')->twig->render('items/itm/bread-crumbs.twig');
	}

/* =============================================================
	URLs
============================================================= */
	public static function itmUrl($itemID = '') {
		$url = new Purl(self::pw('pages')->get('pw_template=itm')->url);
		if ($itemID) {
			$url->query->set('itemID', $itemID);
		}
		return $url->getUrl();
	}

	public static function itmUrlFunction($itemID, $function) {
		$url = new Purl(self::itmUrl($itemID));
		$url->path->add($function);
		return $url->getUrl();
	}

	public static function itmUrlCosting($itemID) {
		return self::itmUrlFunction($itemID, 'costing');
	}

	public static function itmUrlPricing($itemID) {
		return self::itmUrlFunction($itemID, 'pricing');
	}

	public static function itmUrlWhse($itemID, $whseID = '') {
		$url = new Purl(self::itmUrlFunction($itemID, 'warehouses'));
		$url->query->set('whseID', $whseID);
		return $url->getUrl();
	}

	public static function itmUrlMisc($itemID) {
		return self::itmUrlFunction($itemID, 'misc');
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return Itm
	 * @return ItmModel
	 */
	protected static function getItm() {
		return self::pw('modules')->get('Itm');
	}

	/**
	 * Return Min Validator
	 * @return MinValidator
	 */
	protected static function getMinValidator() {
		if (empty(self::$minvalidator)) {
			self::$minvalidator = new MinValidator();
		}
		return self::$minvalidator;
	}

	public static function initHooks() {
		$m = self::pw('modules')->get('Itm');

		$m->addHook('Page(pw_template=itm)::itemUrl', function($event) {
			$event->return = self::itmUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=itm)::itmUrl', function($event) {
			$event->return = self::itmUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=itm)::itmUrlFunction', function($event) {
			$event->return = self::itmUrlFunction($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=itm)::itmUrlCosting', function($event) {
			$event->return = self::itmUrlCosting($event->arguments(0));
		});

		$m->addHook('Page(pw_template=itm)::itmUrlPricing', function($event) {
			$event->return = self::itmUrlPricing($event->arguments(0));
		});

		$m->addHook('Page(pw_template=itm)::itmUrlWhse', function($event) {
			$event->return = self::itmUrlWhse($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=itm)::itmUrlMisc', function($event) {
			$event->return = self::itmUrlPricing($event->arguments(0));
		});
	}
}
