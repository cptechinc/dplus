<?php namespace Controllers\Mii\Ii;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Propel ORM Library
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use ItemMasterItemQuery, ItemMasterItem;
use ItemPricingQuery, ItemPricing;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\WireData, ProcessWire\User;
use ProcessWire\CiLoadCustomerShipto;
// Dplus Filters
use Dplus\Filters\Min\ItemMaster  as ItemMasterFilter;
// Dplus Configs
use Dplus\Configs;

class Ii extends Base {
	const SECTIONS = [
		'item' => [
			'code'      => 'ii-item',
			'formatter' => 'ii:item',
			'twig'      => 'items/ii/item/item.twig',
		],
		'stock' => [
			'code'      => 'ii-stock',
			'formatter' => 'ii:stock',
			'twig'      => 'items/ii/item/stock.twig',
		]
	];

	const SUBFUNCTIONS = [
		'stock'            => ['title' => 'Stock', 'permission' => 'stock'],
		'requirements'     => ['title' => 'Requirements', 'permission' => 'requirements'],
		'costing'          => ['title' => 'Costing', 'permission' => 'cost'],
		'pricing'          => ['title' => 'Pricing', 'permission' => 'pricing'],
		'usage'            => ['title' => 'Usage', 'permission' => 'general'],
		'activity'         => ['title' => 'Activity', 'permission' => 'activity'],
		'kit'              => ['title' => 'Kit', 'permission' => 'kit'],
		'bom'              => ['title' => 'BoM', 'permission' => 'kit'],
		'where-used'       => ['title' => 'Where Used', 'permission' => 'where'],
		'lotserial'        => ['title' => 'Lot / Serial', 'permission' => 'lotserial'],
		'general'          => ['title' => 'General', 'permission' => 'general'],
		'substitutes'      => ['title' => 'Substitutes', 'permission' => 'substitutes'],
		'documents'        => ['title' => 'Documents', 'permission' => 'documents'],
		'sales-orders'     => ['title' => 'Sales Orders', 'permission' => 'salesorders'],
		'sales-history'    => ['title' => 'Sales History', 'permission' => 'saleshistory'],
		'quotes'           => ['title' => 'Quotes', 'permission' => 'quotes'],
		'purchase-orders'  => ['title' => 'Purchase Orders', 'permission' => 'purchaseorders'],
		'purchase-history' => ['title' => 'Purchase History', 'permission' => 'purchasehistory'],
	];

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['itemID|text', 'q|text', 'refresh|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateUserPermission($data) === false) {
			return self::alertInvalidItemPermissions($data);
		}

		if (empty($data->itemID) === false) {
			if ($data->refresh) {
				self::requestIiItem($data->itemID);
				self::pw('session')->redirect(self::itemUrl($data->itemID), $http301 = false);
			}
			return self::item($data);
		}
		return self::list($data);
	}

	private static function item($data) {
		self::getData($data);
		self::pw('page')->headline = "II: $data->itemID";
		return self::displayIi($data);
	}

	private static function list($data) {
		$page = self::pw('page');
		$filter = new ItemMasterFilter();
		$filter->sortby($page);

		if ($data->q) {
			$data->q = strtoupper($data->q);

			if ($filter->exists($data->q)) {
				self::pw('session')->redirect(self::itemUrl($data->itemID), $http301 = false);
			}
			$filter->search($data->q);
			$page->headline = "II: Searching for '$data->q'";
		}
		$pricingM = self::pw('modules')->get('ItemPricing');
		$items = $filter->query->paginate(self::pw('input')->pageNum, 10);
		$pricingM->request_multiple(array_keys($items->toArray(ItemMasterItem::get_aliasproperty('itemid'))));
		$page->searchURL = $page->url;
		return self::displayList($data, $items);
	}

/* =============================================================
	Data Requests
============================================================= */
	# Inherited from IiFunction

/* =============================================================
	URLs
============================================================= */
	public static function itemUrl($itemID, $refreshdata = false) {
		$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
		$url->query->set('itemID', $itemID);

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		foreach (self::SECTIONS as $key => $jsonInfo) {
			self::getDataSection($data, $jsonInfo);
		}
		return true;
	}

	private static function getDataSection($data, $jsonInfo) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile($jsonInfo['code']);
		$session = self::pw('session');

		if ($jsonm->exists($jsonInfo['code'])) {
			if (self::jsonItemidMatches($json['itemid'], $data->itemID) === false) {
				$jsonm->delete($jsonInfo['code']);
				$session->redirect(self::itemUrl($data->itemID, $refresh = true), $http301 = false);
			}
			$session->setFor('ii', 'item', 0);
			return true;
		}

		if ($session->getFor('ii', 'item') > 3) {
			return false;
		}
		$session->setFor('ii', 'item', ($session->getFor('ii', 'item') + 1));
		$session->redirect(self::itemUrl($data->itemID, $refresh = true), $http301 = false);
	}

/* =============================================================
	Displays
============================================================= */
	private static function displayIi($data) {
		$html = '';
		$html .= self::breadCrumbs();
		$html .= self::displayItem($data);
		return $html;
	}

	private static function displayItem($data) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$config = self::pw('config');
		$jsonM  = self::getJsonModule();

		$html = new WireData();
		foreach (self::SECTIONS as $key => $jsonInfo) {
			$html->$key = self::displaySectionFormatted($data, $jsonInfo);
		}
		$page = self::pw('page');
		$page->lastmodified = $jsonM->lastModified('ii-stock');
		$page->refreshurl   = self::itemUrl($data->itemID, true);
		$item = ItemMasterItemQuery::create()->findOneByItemid($data->itemID);
		$itempricing = ItemPricingQuery::create()->findOneByItemid($data->itemID);
		return $config->twig->render('items/ii/item/display.twig', ['item' => $item, 'itempricing' => $itempricing, 'html' => $html]);
	}

	private static function displaySectionFormatted($data, $jsonInfo) {
		self::sanitizeParametersShort($data, ['itemID|text']);
		$config = self::pw('config');
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile($jsonInfo['code']);

		if ($jsonm->exists($jsonInfo['code']) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}

		$formatters = self::pw('modules')->get('ScreenFormatters');
		$formatter = $formatters->formatter($jsonInfo['formatter']);
		$formatter->init_formatter();
		return $config->twig->render($jsonInfo['twig'], ['itemID' => $data->itemID, 'json' => $json, 'module_formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint()]);
	}

	private static function displayList($data, PropelModelPager $items) {
		$config   = self::pw('config');
		$pricingM = self::pw('modules')->get('ItemPricing');
		$html = self::breadCrumbs();
		$html .= $config->twig->render('items/item-search.twig', ['items' => $items, 'pricing' => $pricingM]);
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager'=> $items]);
		return $html;
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return II Subfunctions that are configured by II config
	 * @return array
	 */
	public static function getSubfunctions() {
		$functions = self::SUBFUNCTIONS;
		$configIi = Configs\Ii::config();

		if (array_key_exists('kit', $functions)) {
			if ($configIi->allowBreakdownKit() === false) {
				unset($functions['kit']);
			}
		}

		if (array_key_exists('bom', $functions)) {
			if ($configIi->allowBreakdownBom() === false) {
				unset($functions['bom']);
			}
		}

		if (array_key_exists('quotes', $functions)) {
			if ($configIi->allowQuotes() === false) {
				unset($functions['quotes']);
			}
		}

		if (array_key_exists('lost-sales', $functions)) {
			if ($configIi->allowLostSales() === false) {
				unset($functions['lost-sales']);
			}
		}
		return $functions;
	}

	/**
	 * Return II Subfunctions that the User has Access to
	 * @param  User $user
	 * @return array
	 */
	private static function getUserAllowedSubfunctions(User $user = null) {
		$user = $user ? $user : self::pw('user');
		$iio  = self::getIio();
		$allowed = [];

		foreach (self::getSubfunctions() as $path => $data) {
			if ($iio->allowUser($user, $data['permission'])) {
				$allowed[$path] = $data;
			}
		}
		return $allowed;
	}

/* =============================================================
	Hooks
============================================================= */
	public static function init() {
		Documents::init();

		$m = self::pw('modules')->get('DpagesMii');
		$m->addHook('Page(pw_template=ii-item)::subfunctions', function($event) {
			$event->return = self::getUserAllowedSubfunctions(self::pw('user'));
		});

		$m->addHook('Page(pw_template=ii-item)::subfunctionURL', function($event) {
			$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
			$url->path->add($event->arguments(1));
			$url->query->set('itemID', $event->arguments(0));
			$event->return = $url->getUrl();
		});

		$m->addHook('Page(pw_template=ii-item)::subfunctionUrl', function($event) {
			$url = new Purl(self::pw('pages')->get('pw_template=ii-item')->url);
			$url->path->add($event->arguments(1));
			$url->query->set('itemID', $event->arguments(0));
			$event->return = $url->getUrl();
		});

		$m->addHook('Page(pw_template=ii-item)::subfunctionTitle', function($event) {
			$title = $event->arguments(0);
			if (array_key_exists($event->arguments(0), self::SUBFUNCTIONS)) {
				$func = self::SUBFUNCTIONS[$event->arguments(0)];
				$title = array_key_exists('title', $func) ? $func['title'] : $event->arguments(0);
			}
			$event->return = $title;
		});

		$m->addHook('Page(pw_template=ii-item)::itemUrl', function($event) {
			$event->return = self::iiUrl($event->arguments(0));
		});
	}
}
