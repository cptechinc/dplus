<?php namespace Controllers\Mso\SalesOrder;

use stdClass;
// Propel Query
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
use SalesOrderQuery, SalesOrder as SoModel;
use SalesHistoryQuery, SalesHistory;
use ConfigSalesOrderQuery, ConfigSalesOrder as ConfigSo;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;
// Dplus Classes
use Dplus\CodeValidators\Mso as MsoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class SalesOrder extends Base {
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
			$twig['items'] = $config->twig->render("sales-orders/sales-history/$config->company/items.twig", ['config' => self::configSo(), 'order' => $order]);
		} else {
			$twig['items'] = $config->twig->render("sales-orders/sales-history/items.twig", ['config' => self::configSo(), 'order' => $order]);
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
			'header' => $config->twig->render("sales-orders/sales-order/header-display.twig", ['config' => self::configSo(), 'order' => $order])
		];
		$twigloader = $config->twig->getLoader();

		if ($twigloader->exists("sales-orders/sales-order/$config->company/items.twig")) {
			$twig['items'] = $config->twig->render("sales-orders/sales-order/$config->company/items.twig", ['config' => self::configSo(), 'order' => $order]);
		} else {
			$twig['items'] = $config->twig->render("sales-orders/sales-order/items.twig", ['config' => self::configSo(), 'order' => $order]);
		}

		$qnotes = self::pw('modules')->get('QnotesSalesOrder');
		$twig = self::_orderDetails($order, $data, $qnotes, $twig);
		$html = $config->twig->render("sales-orders/sales-order/page.twig", ['html' => $twig, 'order' => $order]);
		return $html;
	}

	/**
	 * Render Twig Elements fo Sales Order
	 * @param  ActiveRecordInterface|SalesOrder|SalesHistory $order  [description]
	 * @param  stdClass              $data
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
		$page    = self::pw('page');
		$docm    = self::docm();
		$documents = $docm->getDocuments($data->ordn);

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
}
