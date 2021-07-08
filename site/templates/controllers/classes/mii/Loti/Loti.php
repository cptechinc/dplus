<?php namespace Controllers\Mii\Loti;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use InvLotQuery, InvLot;
// ProcessWire Classes, Modules
use ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\Min\LotMaster as LotFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Loti extends AbstractController {
	private static $upcx;

	public static function index($data) {
		$fields = ['scan|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->scan) === false) { // HANDLE SEARCHING UPC, LOTNBR, ITEMID
			return self::scan($data);
		}
		return self::list($data);
	}

	private static function scan($data) {
		self::sanitizeParametersShort($data, ['scan|text']);
		$inventory = self::pw('modules')->get('SearchInventory');
		$inventory->requestSearch($data->scan);

		if ($inventory->count_itemids_distinct() == 1) {
			$result = $inventory->query()->findOne();
			self::pw('session')->redirect(self::lotActivityUrl($result->lotserial), $http301 = false);
		}
	}


	private static function list($data) {
		$data = self::sanitizeParametersShort($data, ['q|text']);
		$page = self::pw('page');
		$filter = new LotFilter();
		$filter->inStock();

		if ($data->q) {
			$filter->search(strtoupper($data->q));
		}


		$lots = $filter->query->paginate(self::pw('input')->pageNum, 10);

		$html = '';
		$html .= self::scanForm($data);
		$html .= self::listDisplay($data, $lots);
		return $html;
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

	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMii');

		$m->addHook('Page(pw_template=loti)::lotActivityUrl', function($event) {
			$event->return = self::lotActivityUrl($event->arguments(0));
		});
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function listDisplay($data, PropelModelPager $lots) {
		$config = self::pw('config');

		$html = '';
		$html .= $config->twig->render('mii/loti/list.twig', ['lots' => $lots]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $lots]);
		return $html;
	}

	private static function scanForm($data) {
		return self::pw('config')->twig->render('mii/loti/forms/scan.twig');
	}
}
