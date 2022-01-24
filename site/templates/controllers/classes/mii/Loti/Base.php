<?php namespace Controllers\Mii\Loti;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use InvLotMasterQuery, InvLotMaster;
// Dpluso Model
use InvsearchQuery, Invsearch;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\SearchInventory, ProcessWire\DpagesMii;
// Dplus Filters
use Dplus\Filters\Min\LotMaster as LotFilter;
// Mvc Controllers
use Mvc\Controllers\Controller;

class Base extends Controller {
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
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMii');

		$m->addHook('Page(pw_template=loti)::lotActivityUrl', function($event) {
			$event->return = self::lotActivityUrl($event->arguments(0));
		});
	}
}
