<?php namespace Controllers\Min\Itm;
// Purl URI Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Itm as ItmModel;
// Dplus Filters
use Dplus\Filters;
// Validators
use Dplus\CodeValidators\Min as MinValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;


class ItmFunction extends AbstractController {
	const PERMISSION_ITMP = '';

	private static $minvalidator;
	private static $itm;
	private static $itmp;

/* =============================================================
	Validations
============================================================= */
	protected static function validateItemid($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$validate = self::getMinValidator();

		if ($validate->itemid($data->itemID) === false) {
			self::pw('session')->redirect(self::itmUrl($data->itemID), $http301 = false);
		}
		return true;
	}

	protected static function validateUserPermission() {
		$user = self::pw('user');
		$itmp = self::pw('modules')->get('Itmp');
		$page = self::pw('page');

		if ($user->has_function('itm') === false) {
			return false;
		}
		if (static::PERMISSION_ITMP != '') {
			return $itmp->isUserAllowed($user, static::PERMISSION_ITMP);
		}
		return true;
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

	public static function itmListUrl($focus = '') {
		if (empty($focus)) {
			return self::itmUrl();
		}
		return self::itmListFocus($focus);
	}

	public static function itmListFocus($itemID) {
		$itm = self::getItm();

		if ($itm->exists($itemID) === false) {
			return self::itmUrl();
		}
		$page = self::pw('pages')->get("pw_template=itm");
		$filter = new Filters\Min\ItemMaster();
		$offset = $filter->positionQuick($itemID);
		$pagenbr = ceil($offset / self::pw('session')->display);
		$url = self::pw('modules')->get('Dpurl')->paginate(new Purl($page->url), $page->name, $pagenbr);
		$url->query->set('focus', $itemID);
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

	public static function itmUrlXrefs($itemID) {
		return self::itmUrlFunction($itemID, 'xrefs');
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function lockItem($itemID) {
		$itm  = self::getItm();
		$html = '';
		if ($itm->recordlocker->isLocked($itemID) && $itm->recordlocker->userHasLocked($itemID) === false) {
			$config = self::pw('config');
			$msg = "ITM Item $itemID is being locked by " . $itm->recordlocker->getLockingUser($itemID);
			$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "ITM Item $itemID is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			$html .= $html->div('class=mb-3');
		} elseif ($itm->recordlocker->isLocked($itemID) === false) {
			$itm->recordlocker->lock($itemID);
		}
		return $html;
	}
	/**
	 * Return Itm
	 * @return ItmModel
	 */
	protected static function getItm() {
		if (empty(self::$itm)) {
			self::$itm = self::pw('modules')->get('Itm');
		}
		return self::$itm;
	}

	/**
	 * Return Itm
	 * @return Itmp
	 */
	public static function getItmp() {
		if (empty(self::$itmp)) {
			self::$itmp = self::pw('modules')->get('Itmp');
		}
		return self::$itmp;
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

		$m->addHook('Page(pw_template=itm)::itmListUrl', function($event) {
			$event->return = self::itmListUrl($event->arguments(0));
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

		$m->addHook('Page(pw_template=itm)::itmUrlXrefs', function($event) {
			$event->return = self::itmUrlXrefs($event->arguments(0));
		});
	}
}
