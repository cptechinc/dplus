<?php namespace Controllers\Mso\SalesOrder;

use stdClass;
// Purl URI Library
use Purl\Url as Purl;
// Propel Query
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
use SalesOrderQuery, SalesOrder as SoModel;
use SalesHistoryQuery, SalesHistory;
use ConfigSalesOrderQuery, ConfigSalesOrder as ConfigSo;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module;
// Alias Document Finders
// Dplus Databases
use Dplus\Databases\Connectors\Dpluso as DbDpluso;
use Dplus\DocManagement\Finders as DocFinders;
// Dplus Code Validators
use Dplus\CodeValidators\Mso as MsoValidator;
// Dplus Mso
use Dplus\Mso\So\Tools;
// Mvc Controllers
use Controllers\Mii\Ii\Ii;
use Controllers\Mci\Ci\Ci;

class SalesOrder extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['ordn|text', 'action|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->ordn) === false) {
			return self::so($data);
		}
		return self::lookupScreen($data);
	}

	public static function so($data) {
		self::sanitizeParametersShort($data, ['ordn|ordn', 'print|bool']);
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}

		if ($validate->orderAccess($data->ordn, self::pw('user')) === false) {
			return self::soAccessDenied($data);
		}
		if ($data->print) {
			self::pw('session')->redirect(self::orderPrintUrl($data->ordn), $http301 = false);
		}
		self::pw('page')->headline = "Sales Order #$data->ordn";
		self::pw('modules')->get('SalesOrderEdit')->init();

		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/orders/sales-order.js'));

		if ($validate->invoice($data->ordn)) {
			return Invoice::invoice($data);
		}

		return self::salesorder($data);
	}

	public static function handleCRUD($data) {
		self::sanitizeParametersShort($data, ['ordn|ordn', 'action|text']);
		switch ($data->action) {
			case 'print-invoice':
				self::requestPrintInvoice($data);
				break;
		}
		self::pw('session')->redirect(self::orderUrl($data->ordn));
	}

/* =============================================================
	Displays
============================================================= */
	public static function salesorder($data) {
		self::sanitizeParametersShort($data, ['ordn|ordn']);

		if (self::validator()->order($data->ordn) === false) {
			return self::invalidSo($data);
		}
		$config = self::pw('config');
		$order = SalesOrderQuery::create()->findOneByOrdernumber($data->ordn);
		$twig = [
			'header' => $config->twig->render("sales-orders/sales-order/header-display.twig", ['config' => self::configSo(), 'order' => $order]),
			'items'  => self::itemsDisplay($order, $data)
		];
		$twig = self::_orderDetails($order, $data, $twig);
		$html = $config->twig->render("sales-orders/sales-order/page.twig", ['html' => $twig, 'order' => $order]);
		return $html;
	}

	/**
	 * Render Twig Elements fo Sales Order
	 * @param  ActiveRecordInterface|SalesOrder $order  Order
	 * @param  stdClass              $data
	 * @param  array                 $twig   Twig array to append to
	 * @return array
	 */
	protected static function _orderDetails(ActiveRecordInterface $order, $data, array $twig) {
		self::sanitizeParametersShort($data, ['ordn|ordn']);
		$config  = self::pw('config');
		$page    = self::pw('page');

		$twig['tracking']    = $config->twig->render('sales-orders/sales-order/sales-order-tracking.twig', ['order' => $order, 'urlmaker' => self::pw('modules')->get('DplusURLs')]);
		$twig['documents']   = self::documentsDisplay($data);
		$twig['qnotes']      = static::qnotesDisplay($data);
		$twig['useractions'] = self::userActionsDisplay($data);
		$twig['modals']      = $config->twig->render('sales-orders/sales-order/specialorder-modal.twig', ['ordn' => $data->ordn]);
		$page->js .= $config->twig->render('sales-orders/sales-order/specialorder-modal.js.twig', ['ordn' => $data->ordn]);
		return $twig;
	}

	protected static function itemsDisplay(ActiveRecordInterface $order, $data) {
		$config     = self::pw('config');
		$twigloader = $config->twig->getLoader();

		if ($twigloader->exists("sales-orders/sales-order/$config->company/items.twig")) {
			return $config->twig->render("sales-orders/sales-order/$config->company/items.twig", ['config' => self::configSo(), 'order' => $order, 'soTools' => Tools::getInstance()]);
		}
		return $config->twig->render("sales-orders/sales-order/items.twig", ['config' => self::configSo(), 'order' => $order, 'soTools' => Tools::getInstance()]);
	}

	protected static function documentsDisplay($data) {
		$docm      = self::docm();
		$documents = $docm->getDocuments($data->ordn);
		return self::pw('config')->twig->render('sales-orders/sales-order/documents.twig', ['documents' => $documents, 'docm' => $docm, 'ordn' => $data->ordn]);
	}

	protected static function userActionsDisplay($data) {
		$m       = self::pw('modules')->get('FilterUserActions');
		$query   = $m->get_actionsquery(self::pw('input'));
		$actions = $query->filterBySalesorderlink($data->ordn)->find();
		return self::pw('config')->twig->render('sales-orders/sales-order/user-actions.twig', ['module_useractions' => $m, 'actions' => $actions, 'ordn' => $data->ordn]);
	}

	protected static function qnotesDisplay($data) {
		$qnotes = self::pw('modules')->get('QnotesSalesOrder');
		return self::pw('config')->twig->render('sales-orders/sales-order/qnotes.twig', ['qnotes_so' => $qnotes, 'ordn' => $data->ordn]);
	}

/* =============================================================
	Supplemental
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMso');

		$m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::orderUrl', function($event) {
			$event->return = self::orderUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=sales-order-view)::orderItemUrl', function($event) {
			$event->return = Item::itemUrl($event->arguments(0), $event->arguments(1));
		});


		$m->addHook('Page(pw_template=sales-order-view)::orderListUrl', function($event) {
			$event->return = self::orderListUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=sales-order-view)::orderHistoryListUrl', function($event) {
			$event->return = self::orderHistoryListUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::orderPrintUrl', function($event) {
			$event->return = self::orderPrintUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::orderEditUrl', function($event) {
			$event->return = self::orderEditUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::orderEditUnlockUrl', function($event) {
			$event->return = self::orderEditUnlockUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::orderNotesUrl', function($event) {
			$event->return = self::orderNotesUrl($event->arguments(0), $event->arguments(1));
		});

		$m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::orderDocumentsUrl', function($event) {
			$event->return = self::orderDocumentsUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::documentUrl', function($event) {
			$event->return = self::documentUrl($event->arguments(0), $event->arguments(1), $event->arguments(2));
		});

		$m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::iiUrl', function($event) {
			$event->return = Ii::iiUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=sales-order-view)::printInvoiceUrl', function($event) {
			$event->return = self::orderPrintInvoiceUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::ciUrl', function($event) {
			$event->return = Ci::ciUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=sales-order-view|sales-order-edit)::ciShiptoUrl', function($event) {
			$event->return = Ci::ciShiptoUrl($event->arguments(0), $event->arguments(1));
		});
	}

	protected static function requestPrintInvoice($data) {
		$vars = ['PRINTARINVOICE', "ORDN=$data->ordn"];
		self::sendRequest($vars);
	}

	protected static function sendRequest(array $data, $sessionID = '') {
		$sessionID = $sessionID ? $sessionID : session_id();
		$db = DbDpluso::instance()->dbconfig->dbName;
		$data = array_merge(["DBNAME=$db"], $data);
		$requestor = self::pw('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $sessionID);
		$requestor->cgi_request(self::pw('config')->cgis['default'], $sessionID);
	}
}
