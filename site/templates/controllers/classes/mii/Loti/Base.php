<?php namespace Controllers\Mii\Loti;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
	// use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use InvLotMasterQuery, InvLotMaster;
// ProcessWire
use ProcessWire\Page;
use ProcessWire\User;
// Dplus
use Dplus\Filters\Min\LotMaster as LotFilter;
use Dplus\Session\UserMenuPermissions;
// Controllers
use Controllers\AbstractController;

class Base extends AbstractController {
	const DPLUSPERMISSION = ''; // TODO
/* =============================================================
	Classes, Module Getters
============================================================= */
	/**
	 * Return InvLotMaster
	 * @param  string $lotnbr Lot Number
	 * @return string
	 */
	protected static function getLot($lotnbr) {
		return InvLotMasterQuery::create()->findOneByLotnbr($lotnbr);
	}

	/**
	 * Return Filter
	 * @return LotFilter
	 */
	protected static function getFilter() {
		return new LotFilter();
	}

/* =============================================================
	URL Functions
============================================================= */
	/**
	 * Return URL to view / edit UPC
	 * @param  string $upc    UPC Code
	 * @param  string $itemID ** Optional
	 * @return string
	 */
	public static function lotActivityUrl($lotnbr) {
		$url = new Purl(self::pw('pages')->get("pw_template=loti")->url);
		$url->path->add('activity');
		$url->query->set('lotnbr', $lotnbr);
		return $url->getUrl();
	}

/* =============================================================
	Displays
============================================================= */
	protected static function breadcrumbs($data) {
		return self::pw('config')->twig->render('mii/loti/bread-crumbs.twig');
	}

/* =============================================================
	Validator, Module Getters
============================================================= */
	public static function validateUserPermission(User $user = null) {
		if (self::validateMenuPermission($user) === false) {
			return false;
		}
		return parent::validateUserPermission($user);
	}

	public static function validateMenuPermission(User $user = null) {
		$page   = self::pw('page');

		foreach ($page->parents('template=dplus-menu|warehouse-menu') as $parent) {
			$code = $parent->dplus_function ? $parent->dplus_function : $parent->dplus_permission;

			if (empty($code) === false && UserMenuPermissions::instance()->canAccess($code) === false) {
				return false;
			}
		}
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMii');

		$m->addHook('Page(pw_template=loti)::lotActivityUrl', function($event) {
			$event->return = self::lotActivityUrl($event->arguments(0));
		});
	}
}
