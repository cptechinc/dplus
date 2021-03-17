<?php namespace Controllers\Mso;

use stdClass;
// Propel Query
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
use SalesOrderQuery, SalesOrder as SalesOrderModel;
use SalesHistoryQuery, SalesHistory;
use SalesOrderDetailQuery, SalesOrderDetail;
use CustomerQuery, Customer;
use ConfigSalesOrderQuery, ConfigSalesOrder as ConfigSo;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module;
// Dplus Classes
use Dplus\CodeValidators\Mso as MsoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class SalesOrder extends AbstractController {
	static $validate;
	static $docm;
	static $configSo;

	public static function index($data) {
		$fields = ['ordn|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->ordn) === false) {
			return self::so($data);
		}
	}

	public static function handleCRUD($data) {
		$data = self::sanitizeParametersShort($data, ['action|text', 'ordn|ordn']);
		self::pw('session')->redirect(self::pw('input')->url(), $http301 = false);
	}

	public static function so($data) {
		$data = self::sanitizeParametersShort($data, ['ordn|ordn']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}

		if ($validate->orderAccess($data->ordn, self::pw('user')) === false) {
			return self::soAccessDenied($data);
		}
		if ($page->print) {
			self::pw('session')->redirect(self::pw('pages')->get('pw_template=sales-order-print')->url."?ordn=$data->ordn");
		}
		$page->headline = "Sales Order #$data->ordn";

		self::pw('modules')->get('DpagesMso')->init_salesorder_hooks();
		self::pw('modules')->get('SalesOrderEdit')->init();

		if ($validate->invoice($data->ordn)) {
			return self::saleshistory($data);
		}

		if ($validate->order($data->ordn)) {
			return self::salesorder($data);
		}
	}

	public static function saleshistory($data) {
		$data = self::sanitizeParametersShort($data, ['ordn|ordn']);
		$validate = self::validator();

		if ($validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}
		$page   = self::pw('page');
		$config = self::pw('config');
		$order = SalesHistoryQuery::create()->findOneByOrdernumber($data->ordn);
		$page->listpage = self::pw('pages')->get('pw_template=sales-history-orders');
		$docm = self::docm();
		$twig = [
			'header' => $config->twig->render("sales-orders/sales-history/header-display.twig", ['config' => self::configSo(), 'order' => $order, 'docm' => $docm])
		];
		$twigloader = $config->twig->getLoader();

		if ($twigloader->exists("sales-orders/sales-history/$config->company/items.twig")) {
			$twig['items'] = $config->twig->render("sales-orders/sales-history/$config->company/items.twig", ['config' => self::configSo(), 'order' => $order, 'docm' => $docm]);
		} else {
			$twig['items'] = $config->twig->render("sales-orders/sales-history/items.twig", ['config' => self::configSo(), 'order' => $order, 'docm' => $docm]);
		}

		$qnotes = self::pw('modules')->get('QnotesSalesHistory');
		$twig = self::_orderDetails($order, $data, $qnotes, $twig);
		$html = $config->twig->render("sales-orders/sales-history/page.twig", ['html' => $twig, 'order' => $order]);
		return $html;
	}

	public static function salesorder($data) {
		$data = self::sanitizeParametersShort($data, ['ordn|ordn']);
		$validate = self::validator();

		if ($validate->order($data->ordn) === false) {
			return self::invalidSo($data);
		}
		$config = self::pw('config');
		$order = SalesOrderQuery::create()->findOneByOrdernumber($data->ordn);
		self::pw('page')->listpage = self::pw('pages')->get('pw_template=sales-orders');
		$twig = [
			'header' => $config->twig->render("sales-orders/sales-order/header-display.twig", ['config' => self::configSo(), 'order' => $order, 'docm' => self::docm()])
		];
		$twigloader = $config->twig->getLoader();

		if ($twigloader->exists("sales-orders/sales-order/$config->company/items.twig")) {
			$twig['items'] = $config->twig->render("sales-orders/sales-order/$config->company/items.twig", ['config' => self::configSo(), 'order' => $order, 'docm' => self::docm()]);
		} else {
			$twig['items'] = $config->twig->render("sales-orders/sales-order/items.twig", ['config' => self::configSo(), 'order' => $order, 'docm' => self::docm()]);
		}

		$qnotes = self::pw('modules')->get('QnotesSalesOrder');
		$twig = self::_orderDetails($order, $data, $qnotes, $twig);
		$html = $config->twig->render("sales-orders/sales-order/page.twig", ['html' => $twig, 'order' => $order]);
		return $html;
	}

	/**
	 * Render Twig Elements fo Sales Order
	 * @param  ActiveRecordInterface|SalesOrder|SalesHistory $order  [description]
	 * @param  stdClass              $data   [description]
	 * @param  Module                $qnotes
	 * @param  array                 $twig   Twig array to append to
	 * @return array
	 */
	private static function _orderDetails(ActiveRecordInterface $order, $data, Module $qnotes, array $twig) {
		$data = self::sanitizeParametersShort($data, ['ordn|ordn']);
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}
		$modules = self::pw('modules');
		$config  = self::pw('config');
		$page  = self::pw('page');
		$docm    = self::docm();
		$documents = $docm->get_documents($data->ordn);

		$module_useractions = $modules->get('FilterUserActions');
		$query_useractions = $module_useractions->get_actionsquery(self::pw('input'));
		$actions = $query_useractions->filterBySalesorderlink($data->ordn)->find();

		$twig['tracking']    = $config->twig->render('sales-orders/sales-order/sales-order-tracking.twig', ['order' => $order, 'urlmaker' => $modules->get('DplusURLs')]);
		$twig['documents']   = $config->twig->render('sales-orders/sales-order/documents.twig', ['documents' => $documents, 'docm' => $docm, 'ordn' => $data->ordn]);
		$twig['qnotes']      = $config->twig->render('sales-orders/sales-order/qnotes.twig', ['qnotes_so' => $qnotes, 'ordn' => $data->ordn]);
		$twig['useractions'] = $config->twig->render('sales-orders/sales-order/user-actions.twig', ['module_useractions' => $module_useractions, 'actions' => $actions, 'ordn' => $data->ordn]);
		$twig['modals']      = $config->twig->render('sales-orders/sales-order/specialorder-modal.twig', ['ordn' => $data->ordn]);
		$page->js   .= $config->twig->render('sales-orders/sales-order/specialorder-modal.js.twig', ['ordn' => $data->ordn]);
		return $twig;
	}

	private static function invalidSo($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Sales Order #$data->ordn not found";
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Sales Order Not Found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Order # $data->ordn can not be found"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::lookupForm();
		return $html;
	}

	private static function soAccessDenied($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Access Denied";
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Sales Order Access Denied', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You don't have access to Order # $data->ordn"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::lookupForm();
		return $html;
	}

	private static function lookupForm() {
		$config = self::pw('config');
		$html = $config->twig->render('sales-orders/sales-order/lookup-form.twig');
		return $html;
	}

	private static function validator() {
		if (empty(self::$validate)) {
			self::$validate = new MsoValidator();
		}
		return self::$validate;
	}

	private static function docm() {
		if (empty(self::$docm)) {
			self::$docm = self::pw('modules')->get('DocumentManagementSo');
		}
		return self::$docm;
	}

	private static function configSo() {
		if (empty(self::$configSo)) {
			self::$configSo = self::pw('modules')->get('ConfigureSo')->config();
		}
		return self::$configSo;
	}
}
