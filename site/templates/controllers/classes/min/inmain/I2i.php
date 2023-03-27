<?php namespace Controllers\Min\Inmain;
// Purl URI Library
use Purl\Url as Purl;
// Propel ORM Ljbrary
use Propel\Runtime\Util\PropelModelPager;
// Dplus Model
use InvItem2ItemQuery, InvItem2Item;
// ProcessWire Classes, Modules
use ProcessWire\WireData, ProcessWire\Page;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as Locker;
// Dplus Configs
use Dplus\Configs;
// Dplus Filters
use Dplus\Filters;
use Dplus\Filters\Min\I2i as I2iFilter;
// Dplus CRUD
use Dplus\Min\Inmain\I2i\I2i as CRUDManager;

class I2i extends AbstractController {
	const DPLUSPERMISSION = 'i2i';
	private static $i2i;

	public static function index($data) {
		if (self::validateUserPermission() === false) {
			return self::renderUserNotPermittedAlert();
		}

		// Sanitize Params, parse route from params
		$fields = ['parentID|text', 'childID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}
		self::initHooks();
		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->parentID) === false) {
			return self::xref($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateUserPermission() === false) {
			return self::pw('session')->redirect(self::url(), $http301 = false);
		}
		$fields = ['parentID|text', 'childID|text', 'action|text'];
		self::sanitizeParameters($data, $fields);
		$i2i = self::getI2i();
		$i2i->processInput(self::pw('input'));
		$url = self::xrefListUrl();

		switch ($data->action) {
			case 'update-i2i':
				if ($i2i->exists($data->parentID, $data->childID)) {
					$xref = $i2i->xref($data->parentID, $data->childID);
					$url = self::xrefListFocusUrl($i2i->getRecordlockerKey($xref));
				}
				break;
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	public static function list($data) {
		self::sanitizeParametersShort($data, ['q|text', 'orderby|text']);
		self::pw('session')->removeFor('upcx', 'sortfilter');
		self::pw('page')->headline = "Item 2 Item";

		$i2i = self::getI2i();
		$i2i->recordlocker->deleteLock();

		$filter = new I2iFilter();

		if ($data->q) {
			self::pw('page')->headline = "I2I: Searching for '$data->q'";
			$filter->search(strtoupper($data->q));
		}
		$filter->sortby(self::pw('page'));

		if (empty($data->q) === false || empty($data->orderby) === false) {
			$sortFilter = Filters\SortFilter::fromArray(['q' => $data->q, 'orderby' => $data->orderby]);
			$sortFilter->saveToSession('i2i');
		}
		$filter->query->orderBy(InvItem2Item::aliasproperty('parentitemid'), 'ASC');

		$xrefs = $filter->query->paginate(self::pw('input')->pageNum, 10);

		self::pw('page')->js .= self::pw('config')->twig->render('min/inmain/i2i/list/.js.twig');
		$html = self::displayList($data, $xrefs);
		$i2i->deleteResponse();
		return $html;
	}

	public static function xref($data) {
		$fields = ['parentID|text', 'childID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		$i2i = self::getI2i();

		$xref = $i2i->getOrCreate($data->parentID, $data->childID);

		if ($i2i->exists($data->parentID, $data->childID)) {
			self::pw('page')->headline = "Item to Item: $data->parentID-$data->childID";
		}

		if ($i2i->exists($data->parentID, $data->childID) === false) {
			self::pw('page')->headline = "Item to Item: Creating New X-Ref";
		}

		if ($xref->isNew() === false) {
			if ($i2i->recordlocker->isLocked($i2i->getRecordlockerKey($xref)) === false) {
				$i2i->recordlocker->lock($i2i->getRecordlockerKey($xref));
			}
		}
		self::pw('page')->js .= self::pw('config')->twig->render('min/inmain/i2i/xref/form/.js.twig');
		$html = self::displayXref($data, $xref);;
		$i2i->deleteResponse();
		return $html;
	}

/* =============================================================
	Display Functions
============================================================= */
	private static function displayList($data, PropelModelPager $xrefs) {
		$i2i = self::geti2i();
		$config = self::pw('config');

		$html  = '';
		$html .= self::displayBreadcrumbs($data);
		$html .= self::displayResponse($data);
		$html .= $config->twig->render('min/inmain/i2i/list/display.twig', ['i2i' => $i2i, 'xrefs' => $xrefs]);
		$html .= '<div class="mb-3"></div>';
		$html .= $config->twig->render('util/paginator/propel.twig', ['pager' => $xrefs]);
		return $html;
	}

	private static function displayLock(InvItem2Item $xref) {
		$config = self::pw('config');
		$i2i = self::getI2i();
		$html = '';

		$key = $i2i->getRecordlockerKey($xref);

		if ($i2i->recordlocker->isLocked($key) === false && $i2i->recordlocker->userHasLocked($key) === false) {
			$i2i->recordlocker->lock($key);
		}

		if ($i2i->recordlocker->isLocked($key) && $i2i->recordlocker->userHasLocked($key) === false) {
			$msg = "Item To Item $xref->parentitemid - $xref->childitemid is being locked by " . $i2i->recordlocker->getLockingUser($key);
			$html .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Reason Code $xref->parentitemid - $xref->childitemid is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
		}
		$html .= '<div class="mb-3"></div>';
		return $html;
	}

	private static function displayXref($data, InvItem2Item $xref) {
		$html  = self::displayBreadcrumbs($data);
		$html .= self::displayResponse($xref);
		$html .= self::displayLock($xref);
		$html .= self::pw('config')->twig->render('min/inmain/i2i/xref/display.twig', ['xref' => $xref, 'i2i' => self::geti2i()]);
		return $html;
	}

	private static function displayResponse($data) {
		$response = self::getI2i()->getResponse();

		if (empty($response) || $response->hasSuccess()) {
			return '';
		}
		return self::pw('config')->twig->render('code-tables/response.twig', ['response' => $response]);
	}

	private static function displayBreadcrumbs($data) {
		return self::pw('config')->twig->render('min/inmain/i2i/bread-crumbs.twig');
	}

/* =============================================================
	URL Functions
============================================================= */
	public static function url() {
		return Menu::i2iUrl();
	}

	public static function i2iUrl() {
		return self::url();
	}

	public static function xrefListUrl($focus = '') {
		if (empty($focus)) {
			return self::url();
		}
		return self::xrefListFocusUrl($focus);
	}

	public static function xrefListFocusUrl($key, $childID = '') {
		$i2i = self::getI2i();

		if (empty($childID) === false) {
			$key = $i2i->getRecordlockerKeyFromKeys($key, $childID);
		}

		if ($i2i->existsFromRecordlockerKey($key) === false) {
			return self::i2iUrl();
		}
		$xref = $i2i->xrefFromRecordlockerKey($key);
		$sortFilter = Filters\SortFilter::getFromSession('i2i');
		$filter = new I2iFilter();

		if ($sortFilter) {
			$filter->applySortFilter($sortFilter);
		}
		$filter->query->orderBy(InvItem2Item::aliasproperty('parentitemid'), 'ASC');
		$offset = $filter->position($xref);
		$pagenbr = self::getPagenbrFromOffset($offset);

		$url = new Purl(self::i2iUrl());
		$url->query->set('focus', $key);
		$url = self::pw('modules')->get('Dpurl')->paginate($url, self::pw('pages')->get(self::i2iUrl())->name, $pagenbr);
		if ($sortFilter) {
			if ($sortFilter->q) {
				$url->query->set('q', $sortFilter->q);
			}
			if ($sortFilter->orderby) {
				$url->query->set('orderby', $sortFilter->orderby);
			}
		}
		return $url->getUrl();
	}

	public static function xrefUrl($parentID, $childID) {
		$url = new Purl(self::i2iUrl());
		$url->query->set('parentID', $parentID);
		$url->query->set('childID', $childID);
		return $url->getUrl();
	}

	public static function xrefDeleteUrl($parentID, $childID) {
		$url = new Purl(self::xrefUrl($parentID, $childID));
		$url->query->set('action', 'delete-i2i');
		return $url->getUrl();
	}

	public static function xrefNewUrl() {
		$url = new Purl(self::i2iUrl());
		$url->query->set('parentID', 'new');
		return $url->getUrl();
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return I2i CRUD Manager
	 * @return CRUDManager
	 */
	public static function getI2i() {
		if (empty(self::$i2i)) {
			self::$i2i = CRUDManager::getInstance();
		}
		return self::$i2i;
	}

	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMin');

		$m->addHook('Page(pw_template=inmain)::menuUrl', function($event) {
			$event->return = Menu::menuUrl();
		});

		$m->addHook('Page(pw_template=inmain)::menuTitle', function($event) {
			$event->return = Menu::TITLE;
		});

		$m->addHook('Page(pw_template=inmain)::xrefUrl', function($event) {
			$event->return = self::xrefUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=inmain)::xrefDeleteUrl', function($event) {
			$event->return = self::xrefDeleteUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=inmain)::xrefNewUrl', function($event) {
			$event->return = self::xrefNewUrl();
		});

		$m->addHook('Page(pw_template=inmain)::xrefListUrl', function($event) {
			$event->return = self::xrefListUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=inmain)::xrefListFocusUrl', function($event) {
			$event->return = self::xrefListFocusUrl($event->arguments(0), $event->arguments(1));
		});
	}
}
