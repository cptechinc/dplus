<?php namespace Controllers\Min\Itm;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use WarehouseInventoryQuery, WarehouseInventory;
// ProcessWire classes, modules
use ProcessWire\Page, ProcessWire\ItmWarehouse as WarehouseCRUD;
// Mvc Controllers
use Controllers\Min\Itm\ItmFunction;

class Warehouse extends ItmFunction {
	const PERMISSION_ITMP = 'whse';

	public static function index($data) {
		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		self::pw('page')->show_breadcrumbs = false;

		if (empty($data->whseID) === false) {
			return self::warehouse($data);
		}
		return self::list($data);
	}

	public static function handleCRUD($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		$fields = ['itemID|text', 'whseID|text', 'action|text'];
		$data   = self::sanitizeParameters($data, $fields);
		$input  = self::pw('input');
		$itmW  = self::getItmWarehouse();
		$itmW->init_configs();

		if ($data->action) {
			$itmW->process_input($input);
			$url = self::itmUrlWhse($data->itemID, $data->whseID);

			switch ($data->action) {
				case 'delete-whse':
				case 'update-whse':
					$url = self::itmUrlWhse($data->itemID);
					break;
			}
		}
		self::pw('session')->redirect($url, $http301 = false);
	}

	public static function warehouse($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return self::displayAlertUserPermission($data);
		}

		$fields = ['itemID|text', 'whseID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		self::initHooks();
		$config  = self::pw('config');
		$page    = self::pw('page');
		$page->headline = "ITM: $data->itemID Warehouse $data->whseID";
		$validate = self::getMinValidator();

		if ($validate->whseid($data->whseID) === false && $data->whseID != 'new') {
			return self::invalidWhse($data);
		}

		if (self::getItmWarehouse()->exists($data->itemID, $data->whseID) === false) {
			$page->headline = "ITM: $data->itemID Warehouse Add";
		}
		return self::whseDisplay($data);
	}

	private static function whseDisplay($data) {
		$itm  = self::getItm();
		$itmW = self::getItmWarehouse();
		$itmW->init_configs();
		$item = $itm->get_item($data->itemID);
		$whse = $itmW->getOrCreate($data->itemID, $data->whseID);
		$config = self::pw('config');

		$html   = '';
		$html   .= $config->twig->render('items/itm/bread-crumbs.twig');
		$html   .= $config->twig->render('items/itm/itm-links.twig');
		$html   .= self::lockItem($data->itemID);

		if (self::pw('session')->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => self::pw('session')->getFor('response', 'itm')]);
		}

		if ($whse->isNew() === false) {
			$html .= self::lockItemWarehouse($whse);
		}

		$html .= $config->twig->render('items/itm/warehouse/display.twig', ['item' => $item, 'warehouse' => $whse, 'm_whse' => $itmW, 'recordlocker' => $itmW->recordlocker]);
		if ($whse->isNew() === false) {
			$html .= self::displayQnotes($data, $whse);
		}
		$html .= $config->twig->render('items/itm/warehouse/bins-modal.twig', ['itemID' => $data->itemID, 'm_whse' => $itmW]);
		return $html;
	}

	private static function displayQnotes($data, $item) {
		$qnotes = self::getQnotes();
		$html = '';
		self::pw('page')->js .= self::pw('config')->twig->render('items/itm/warehouse/notes/order/js.twig', ['item' => $item, 'qnotes' => $qnotes]);
		$html .= self::pw('config')->twig->render('items/itm/warehouse/notes/notes.twig', ['item' => $item, 'qnotes' => $qnotes]);
		self::pw('session')->remove('response_qnote');
		return $html;
	}

	private static function invalidWhse($data) {
		$validate = self::getMinValidator();
		$html = '';
		if ($validate->whseid($data->whseID) === false) {
			$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Warehouse Not Found", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Warehouse '$data->whseID' not found "]);
			$htmlwriter = self::pw('modules')->get('HtmlWriter');
			$url = self::itmUrlWhse($data->itemID);
			$html .= $htmlwriter->a("class=btn btn-primary mt-3|href=$url", $htmlwriter->icon('fa fa-undo')." Warehouses");
		}
		return $html;
	}

	private static function lockItemWarehouse(WarehouseInventory $whse) {
		$itmW = self::getItmWarehouse();
		$html = '';
		$itmW->lockrecord($whse);

		if ($itmW->recordlocker->isLocked($whse->itemid) && $itmW->recordlocker->userHasLocked($itmW->lockerkey($whse)) === false) {
			$msg = "Warehouse $whse->whseid for $whse->itemid is being locked by " . $itmW->recordlocker->getLockingUser($itmW->lockerkey($whse));
			$html .= self::pw('config')->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Warehouse $whse->whseid is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
			$html .= '<div class="mb-3"></div>';
		}
		return $html;
	}

	public static function list($data) {
		if (self::validateItemidAndPermission($data) === false) {
			return $page->body;
		}

		$fields = ['itemID|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);
		if ($data->action) {
			return self::handleCRUD($data);
		}
		self::initHooks();
		$itmw    = self::getItmWarehouse();
		$itmw->recordlocker->deleteLock();
		$page    = self::pw('page');
		$page->headline = "ITM: $data->itemID Warehouses";

		return self::listDisplay($data);
	}

	private static function listDisplay($data) {
		$config  = self::pw('config');
		$item    = self::getItm()->item($data->itemID);
		$itmw    = self::getItmWarehouse();
		$qnotes  = self::getQnotes();

		$html = '';
		$html .= $config->twig->render('items/itm/bread-crumbs.twig');
		$html .= $config->twig->render('items/itm/itm-links.twig');
		if (self::pw('session')->getFor('response', 'itm')) {
			$html .= $config->twig->render('items/itm/response-alert.twig', ['response' => self::pw('session')->getFor('response', 'itm')]);
		}
		$html .= $config->twig->render('items/itm/warehouse/list-display.twig', ['itmw' => $itmw, 'itemID' => $data->itemID, 'item' => $item, 'warehouses' => $itmw->get_itemwarehouses($data->itemID), 'qnotes' => $qnotes]);
		return $html;
	}

	public static function getItmWarehouse() {
		return self::pw('modules')->get('ItmWarehouse');
	}

	public static function getQnotes() {
		return self::pw('modules')->get('QnotesItemWhseOrder');
	}

	public static function itmUrlWhseDelete($itemID, $whseID) {
		$url = new Purl(self::itmUrlWhse($itemID, $whseID));
		$url->query->set('action', 'delete-whse');
		return $url->getUrl();
	}

	public static function itmUrlWhseFocus($itemID, $whseID) {
		$url = new Purl(self::itmUrlWhse($itemID));
		$url->query->set('focus', $whseID);
		return $url->getUrl();
	}

	public static function initHooks() {
		$m = self::pw('modules')->get('Itm');

		$m->addHook('Page(pw_template=itm)::itmUrlWhseDelete', function($event) {
			$event->return = self::itmUrlWhseDelete($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=itm)::itmUrlWhseExit', function($event) {
			$event->return = self::itmUrlWhseFocus($event->arguments(0), $event->arguments(1));
		});
	}

}
