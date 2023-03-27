<?php namespace Controllers\Mii\Loti;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use InvLotMaster;
// Dpluso Model
use Invsearch;
// ProcessWire Classes, Modules
use ProcessWire\SearchInventory;
use ProcessWire\User;
// Dplus
use Dplus\Session\UserMenuPermissions;
// Mvc Controllers
use Controllers\Mii\Loti\Base;

class Loti extends Base {
	
/* =============================================================
	Index, Logic Functions
============================================================= */
	public static function index($data) {
		$fields = ['scan|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		if (empty($data->scan) === false) { // HANDLE SEARCHING UPC, LOTNBR, ITEMID
			return self::scan($data);
		}
		return self::list($data);
	}

	private static function scan($data) {
		self::sanitizeParametersShort($data, ['scan|text']);
		$inventory = self::pw('modules')->get('SearchInventory');
		$inventory->requestSearch($data->scan);
		$count = $inventory->count_lotserials_distinct();

		if ($count == 1) {
			$result = $inventory->query()->findOne();
			self::pw('session')->redirect(self::lotActivityUrl($result->lotserial), $http301 = false);
		}

		if ($count == 0) {
			self::scanRedirectIfLotFound($data);
		}
		return self::scanResults($data, $inventory);
	}

	private static function scanRedirectIfLotFound($data) {
		$filter = self::getFilter();

		if ($filter->exists($data->scan)) {
			self::pw('session')->redirect(self::lotActivityUrl($data->scan), $http301 = false);
		}

		if ($filter->existsLotRef($data->scan)) {
			$filter->filterByLotRef($data->scan);
			$lot = $filter->findOne();
			self::pw('session')->redirect(self::lotActivityUrl($lot->lotnbr), $http301 = false);
		}
	}

	private static function scanResults($data, SearchInventory $inventory) {
		$lotnbrs = $inventory->query()->select(Invsearch::get_aliasproperty('lotserial'))->find()->toArray();
		$filter = self::getFilter();
		$filter->query->filterByLotnbr($lotnbrs);
		$lots = $filter->query->paginate(self::pw('input')->pageNum, sizeof($lotnbrs));
		$html  = self::breadcrumbs($data);
		$html .= self::formAndlistDisplay($data, $lots);
		return $html;
	}

	private static function list($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$page = self::pw('page');
		$filter = self::getFilter();
		$filter->inStock();

		if ($data->q) {
			$filter->search(strtoupper($data->q));
		}

		$lots = $filter->query->paginate(self::pw('input')->pageNum, 10);

		$html  = self::breadcrumbs($data);
		$html .= self::formAndlistDisplay($data, $lots);
		return $html;
	}

/* =============================================================
	2. Validations
============================================================= */
	/**
	 * Validate User's Permission to this Function
	 * @param  User|null $user
	 * @return bool
	 */
	public static function validateUserPermission(User $user = null) {
		if (static::validateUserMenuPermission($user) === false) {
			return false;
		}
		return parent::validateUserPermission($user);
	}

	/**
	 * Return if User has Access to Parent Menu
	 * @param  User|null $user
	 * @return bool
	 */
	public static function validateUserMenuPermission(User $user = null) {
		$perimittedList = UserMenuPermissions::instance()->list();
		return $perimittedList->has('mii');
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
	public static function lotActivityUrl($lotnbr, $startdate = '') {
		$url = new Purl(self::pw('pages')->get("pw_template=loti")->url);
		$url->path->add('activity');
		$url->query->set('lotnbr', $lotnbr);

		if ($startdate) {
			$url->query->set('startdate', $startdate);
		}
		return $url->getUrl();
	}

/* =============================================================
	Display Functions
============================================================= */
	/**
	 * Return Lot List
	 * @param  object           $data
	 * @param  PropelModelPager $lots InvLotMaster[]
	 * @return string           HTML
	 */
	private static function listDisplay($data, PropelModelPager $lots) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('mii/loti/list.twig', ['lots' => $lots]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $lots]);
		return $html;
	}

	/**
	 * Return Scan form and Lot List Combined
	 * @param  object           $data
	 * @param  PropelModelPager $lots InvLotMaster[]
	 * @return string           HTML
	 */
	private static function formAndlistDisplay($data, PropelModelPager $lots) {
		$html = '';
		$html .= self::scanForm($data);
		$html .= self::listDisplay($data, $lots);
		return $html;
	}

	private static function scanForm($data) {
		return self::pw('config')->twig->render('mii/loti/forms/scan.twig');
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
