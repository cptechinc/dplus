<?php namespace Controllers\Mso;

use Mvc\Controllers\AbstractController;

use ProcessWire\Page, ProcessWire\SalesOrderEdit as EsoModel;

use Dplus\CodeValidators\Mso as MsoValidator;

use PricingQuery, Pricing;

use CustomerQuery, Customer;
use SalesOrder;
use SalesOrderDetailQuery, SalesOrderDetail;

use OrdrhedQuery, Ordrhed as SalesOrderEditable;

class Eso extends AbstractController {
	public static function index($data) {
		$fields = ['ordn|text', 'action|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->action) === false) {
			return self::handleCRUD($data);
		}

		if (empty($data->ordn) === false) {
			return self::so($data);
		}
		return self::lookupForm();
	}

	public static function handleCRUD($data) {
		$data = self::sanitizeParametersShort($data, ['action|text', 'ordn|ordn']);

		if (empty($data->action) === true) {
			self::pw('session')->redirect(self::pw('page')->url."?ordn=$data->ordn", $http301 = false);
		}

		if ($data->action) {
			$page = self::pw('page');
			$eso  = self::pw('modules')->get('SalesOrderEdit');
			$eso->process_input(self::pw('input'));
			$url = $data->action == 'exit' ? $page->so_viewURL($data->ordn) : $page->so_editURL($data->ordn);
			self::pw('session')->redirect($url, $http301 = false);
		}
	}

	public static function so($data) {
		$data = self::sanitizeParametersShort($data, ['ordn|ordn', 'load|int']);
		$data->ordn = self::pw('sanitizer')->ordn($data->ordn);
		$page = self::pw('page');
		$config = self::pw('config');
		$validate = new MsoValidator();

		if ($validate->order($data->ordn) === false) {
			if ($validate->invoice($data->ordn)) {
				return self::invalidHistory($data);
			}
			return self::invalidSo($data);
		}

		$eso = self::pw('modules')->get('SalesOrderEdit');
		$eso->set_ordn($data->ordn);

		if ($eso->exists_editable($data->ordn) === false) {
			if ($data->load > 0) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "SO # $data->ordn can not be loaded for editing"]);
				return $page->body;
			}
			$eso->request_so_edit($data->ordn);
			$page->fullURL->query->set('load', 1);
			self::pw('session')->redirect($page->fullURL->getUrl(), $http301 = false);
		}
		return self::soEditForm($data, $eso, $page, $config);
	}

	private static function soEditForm($data, EsoModel $eso) {
		self::pw('modules')->get('DpagesMso')->init_salesorder_hooks();
		$page = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Editing Sales Order #$data->ordn";
		$order = $eso->get_editable_header($data->ordn);
		self::soEditHeader($eso, $order);
		self::soEditItems($eso, $order);
		self::itemLookupForm($order);
		self::qnotes($order->ordernumber);
		self::setupCstkLastSold($order);
		self::js($eso, $order->ordernumber);
		$page->body .= $config->twig->render('sales-orders/sales-order/edit/edit-item/modal.twig', ['ordn' => $order->ordernumber]);
		return $page->body;
	}

	private static function soEditHeader(EsoModel $eso, SalesOrderEditable $order) {
		$page  = self::pw('page');
		$config = self::pw('config');
		$customer = CustomerQuery::create()->findOneByCustid($order->custid);
		$page->listpage = self::pw('pages')->get('pw_template=sales-orders');
		$page->formurl  = self::pw('pages')->get('template=dplus-menu, name=mso')->child('template=redir')->url;
		$page->body .= $config->twig->render('sales-orders/sales-order/edit/links-header.twig', ['order' => $order]);
		$page->body .= $config->twig->render('sales-orders/sales-order/edit/sales-order-header.twig', ['customer' => $customer, 'order' => $eso->get_order_static($order->ordernumber)]);

		if (self::pw('user')->is_editingorder($order->ordernumber)) {
			$page->body .= $config->twig->render('sales-orders/sales-order/edit/edit-form.twig', ['eso' => $eso, 'order' => $order, 'states' => $eso->get_states(), 'shipvias' => $eso->get_shipvias(), 'warehouses' => $eso->get_warehouses(), 'termscodes' => $eso->get_termscodes(), 'shiptos' => $customer->get_shiptos()]);
		}
	}

	private static function soEditItems(EsoModel $eso, SalesOrderEditable $order) {
		$page   = self::pw('page');
		$config = self::pw('config');
		if ($config->twigloader->exists("sales-orders/sales-order/edit/$config->company/order-items.twig")) {
			$page->body .= $config->twig->render("sales-orders/sales-order/edit/$config->company/order-items.twig", ['order' => $order, 'eso' => $eso]);
		} else {
			$page->body .= $config->twig->render('sales-orders/sales-order/edit/order-items.twig', ['order' => $order, 'eso' => $eso]);
		}
	}

	private static function itemLookupForm(SalesOrderEditable $order) {
		if (self::pw('user')->is_editingorder($order->ordernumber)) {
			$config = self::pw('config');
			$page   = self::pw('page');
			$page->body .= '<div class="mt-3"></div>';

			if ($config->twigloader->exists("sales-orders/sales-order/edit/lookup/$config->company/form.twig")) {
				$page->body .= $config->twig->render("sales-orders/sales-order/edit/lookup/$config->company/form.twig", ['order' => $order]);
				$page->js   .= $config->twig->render("sales-orders/sales-order/edit/lookup/$config->company/js.twig", ['order' => $order, 'eso' => $eso]);
			} else {
				$page->body .= $config->twig->render('sales-orders/sales-order/edit/lookup/form.twig', ['order' => $order]);
				$page->js   .= $config->twig->render('sales-orders/sales-order/edit/lookup/js.twig', ['order' => $order]);
			}

			$input = self::pw('input');
			if ($input->get->q) {
				$q = $input->get->text('q');
				$eso = self::pw('modules')->get('SalesOrderEdit');
				$eso->set_ordn($order->ordernumber);
				$eso->request_itemsearch($q);
				$results = PricingQuery::create()->findBySessionid(session_id());
				$page->body .= $config->twig->render('sales-orders/sales-order/edit/lookup/results.twig', ['q' => $q, 'results' => $results, 'soconfig' => $eso->config('so') ]);
			}
		}
	}

	private static function qnotes($ordn) {
		$config = self::pw('config');
		$page   = self::pw('page');
		$qnotes = self::pw('modules')->get('QnotesSalesOrder');
		$page->body .= '<div class="mb-4"></div>';
		$page->body .= $config->twig->render('sales-orders/sales-order/qnotes.twig', ['qnotes_so' => $qnotes, 'ordn' => $ordn]);
	}

	private static function js(EsoModel $eso, $ordn) {
		$config = self::pw('config');
		$page   = self::pw('page');
		if (self::pw('user')->is_editingorder($ordn)) {
			$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => array('shiptos' => $eso->get_shiptos_json_array())]);
			$page->js   .= $config->twig->render('sales-orders/sales-order/edit/js/js.twig', ['eso' => $eso]);
			$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		}
	}

	private static function setupCstkLastSold($order) {
		$modules = self::pw('modules');
		$config = self::pw('config');
		$page   = self::pw('page');

		if ($modules->get('ConfigsCi')->option_lastsold  == 'cstk') {
			$lastsold = $modules->get('LastSoldItemsCustomerCstk');
			$lastsold->custID = $order->custid;
			$lastsold->shiptoID = $order->shiptoid;
			$lastsold->function = 'eso';

			if ($lastsold->has_pricing()) {
				$lastsold->request_pricing();
			}
			$page->body .= $config->twig->render('sales-orders/sales-order/edit/last-sales/modal.twig', ['ordn' => $order->ordernumber, 'lastsold' => $lastsold, 'loader' => $config->twigloader, 'company' => $config->company]);
		}
	}

	private static function invalidSo($data) {
		$page = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Sales Order #$data->ordn not found";
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Sales Order Not Found, check if it\'s in History', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "SO # $data->ordn can not be found"]);
		$page->body .= '<div class="mb-3"></div>';
		return self::lookupForm();
	}

	private static function invalidHistory($data) {
		$page = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Sales Order #$data->ordn is not editable";
		$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Order #$data->ordn is in Sales History", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "SO # $data->ordn is invoiced, and in history"]);
		$page->body .= '<div class="mb-3"></div>';
		return self::lookupForm();
	}

	private static function lookupForm() {
		$page = self::pw('page');
		$config = self::pw('config');
		$page->body .= $config->twig->render('sales-orders/sales-order/lookup-form.twig');
		return $page->body;
	}

	public static function editItem($data) {
		$eso = self::pw('modules')->get('SalesOrderEdit');

		$data = self::sanitizeParametersShort($data, ['ordn|ordn', 'linenbr|int']);
		$data->ordn = self::pw('sanitizer')->ordn($data->ordn);
		$page = self::pw('page');
		$config = self::pw('config');
		$validate = new MsoValidator();

		if ($validate->order($data->ordn) === false) {
			return self::invalidSo($data);
		}

		if (self::pw('user')->is_editingorder($data->ordn) == false) {
			return self::invalidHistory($data);
		}
		$eso->set_ordn($data->ordn);

		$q = SalesOrderDetailQuery::create()->filterByOrdernumber($data->ordn)->filterByLinenbr($data->linenbr);

		if (empty($data->linenbr) || $q->count() === 0) {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Invalid Line #", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Line # $data->linenbr does not exist on SO # $data->ordn"]);
			return $page->body;
		}

		$orderitem = $q->findOne();
		$files = ['pricing' => false, 'pricehistory' => false, 'stock' => false];

		if ($orderitem->itemid != 'N') {
			$request = true;
			$mjson = self::pw('modules')->get('JsonDataFiles');

			if ($mjson->file_exists(session_id(), 'eso-pricing')) {
				$request = false;
				$modified = $mjson->file_modified(session_id(), 'eso-pricing');

				if ($modified < strtotime('-5 minutes')) {
					$request = true;
				}

				$json = $mjson->get_file(session_id(), "eso-pricing");
				if ($json && $json['itemid'] != $orderitem->itemid) {
					$request = true;
				}
			}

			if ($request) {
				$eso->request_itempricing($orderitem->itemid);
			}

			foreach (array_keys($files) as $code) {
				$json = $mjson->get_file(session_id(), "eso-$code");
				if ($mjson->file_exists(session_id(), "eso-$code") == false) {
					$json = false;
				}
				$files[$code] = $json;
			}
		}

		$page->body .= $config->twig->render('sales-orders/sales-order/edit/edit-item/display.twig', ['orderitem' => $orderitem, 'data' => $files]);
		return $page->body;
	}
}
