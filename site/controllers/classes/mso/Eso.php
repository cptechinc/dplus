<?php namespace Controllers\Mso;

use stdClass;
// Propel Query
use Propel\Runtime\ActiveQuery\Criteria;
// Dpluso Model
use PricingQuery, Pricing;
use OrdrhedQuery, Ordrhed as SalesOrderEditable;
// Dplus Model
use SalesOrderQuery, SalesOrder;
use SalesOrderDetailQuery, SalesOrderDetail;
use CustomerQuery, Customer;
use ItemMasterItemQuery, ItemMasterItem;
use ConfigSalesOrderQuery, ConfigSalesOrder as ConfigSo;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\SalesOrderEdit as EsoCRUD;
// Dplus Validators
use Dplus\CodeValidators\Mso as MsoValidator;
use Dplus\CodeValidators\Min as MinValidator;
// Dplus Filters
use Dplus\Filters\Mso\SalesHistory\Detail as SalesHistoryDetailFilter;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

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
			$url = $page->so_editURL($data->ordn);
			if (in_array($data->action, ['unlock-order', 'exit']) || isset($data->exit)) {
				$url = $page->so_viewURL($data->ordn);
			}
			self::pw('session')->redirect($url, $http301 = false);
		}
		self::pw('session')->redirect(self::pw('input')->url(), $http301 = false);
	}

	public static function so($data) {
		$data = self::sanitizeParametersShort($data, ['ordn|ordn']);
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
		$session = self::pw('session');

		if ($eso->exists_editable($data->ordn) === false || $eso->can_order_be_edited($data->ordn)) {
			if ($session->getFor('load-eso', $data->ordn) > 0) {
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "SO # $data->ordn can not be loaded for editing"]);
				return $page->body;
			}
			$eso->request_so_edit($data->ordn);
			$session->setFor('load-eso', $data->ordn, 1);
			$session->redirect($page->fullURL->getUrl(), $http301 = false);
		}
		$session->removeFor('load-eso', $data->ordn);
		return self::soEditForm($data, $eso, $page, $config);
	}

	private static function soEditForm($data, EsoCRUD $eso) {
		self::pw('modules')->get('DpagesMso')->init_salesorder_hooks();
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Editing Sales Order #$data->ordn";
		$order = $eso->get_editable_header($data->ordn);
		$html = '';
		$html .= self::soEditHeader($eso, $order);
		$html .= self::soEditItems($eso, $order);
		$html .= self::itemLookupForm($order);
		$html .= self::qnotes($order->ordernumber);
		$html .= self::setupCstkLastSold($order);
		$html .= self::js($eso, $order->ordernumber);
		$html .= $config->twig->render('sales-orders/sales-order/edit/edit-item/modal.twig', ['ordn' => $order->ordernumber]);
		return $html;
	}

	private static function soEditHeader(EsoCRUD $eso, SalesOrderEditable $order) {
		$html = '';
		$page  = self::pw('page');
		$config = self::pw('config');
		$customer = CustomerQuery::create()->findOneByCustid($order->custid);
		$page->listpage = self::pw('pages')->get('pw_template=sales-orders');
		$page->formurl  = self::pw('pages')->get('template=dplus-menu, name=mso')->child('template=redir')->url;
		$html .= $config->twig->render('sales-orders/sales-order/edit/links-header.twig', ['order' => $order]);
		$html .= $config->twig->render('sales-orders/sales-order/edit/sales-order-header.twig', ['customer' => $customer, 'order' => $eso->get_order_static($order->ordernumber)]);

		if (self::pw('user')->is_editingorder($order->ordernumber)) {
			$html .= $config->twig->render('sales-orders/sales-order/edit/edit-form.twig', ['eso' => $eso, 'order' => $order, 'states' => $eso->get_states(), 'shipvias' => $eso->get_shipvias(), 'warehouses' => $eso->get_warehouses(), 'termscodes' => $eso->get_termscodes(), 'shiptos' => $customer->get_shiptos()]);
		}
		return $html;
	}

	private static function soEditItems(EsoCRUD $eso, SalesOrderEditable $order) {
		$html = '';
		$config = self::pw('config');

		if ($config->twigloader->exists("sales-orders/sales-order/edit/$config->company/order-items.twig")) {
			$html .= $config->twig->render("sales-orders/sales-order/edit/$config->company/order-items.twig", ['order' => $order, 'eso' => $eso]);
		} else {
			$html .= $config->twig->render('sales-orders/sales-order/edit/order-items.twig', ['order' => $order, 'eso' => $eso]);
		}
		$html .= $config->twig->render('sales-orders/sales-order/specialorder-modal.twig', ['ordn' => $order->ordernumber]);
		self::pw('page')->js   .= $config->twig->render('sales-orders/sales-order/specialorder-modal.js.twig', ['ordn' => $order->ordernumber]);
		return $html;
	}

	private static function itemLookupForm(SalesOrderEditable $order) {
		$html = '';
		if (self::pw('user')->is_editingorder($order->ordernumber) === false) {
			return $html;
		}
		$config = self::pw('config');
		$page   = self::pw('page');
		$html   .= '<div class="mt-3"></div>';

		if ($config->twigloader->exists("sales-orders/sales-order/edit/lookup/$config->company/form.twig")) {
			$html .= $config->twig->render("sales-orders/sales-order/edit/lookup/$config->company/form.twig", ['order' => $order]);
			$page->js .= $config->twig->render("sales-orders/sales-order/edit/lookup/$config->company/js.twig", ['order' => $order, 'eso' => $eso]);
		} else {
			$html .= $config->twig->render('sales-orders/sales-order/edit/lookup/form.twig', ['order' => $order]);
			$page->js .= $config->twig->render('sales-orders/sales-order/edit/lookup/js.twig', ['order' => $order]);
		}

		$input = self::pw('input');

		if ($input->get->q) {
			$q = $input->get->text('q');
			$eso = self::pw('modules')->get('SalesOrderEdit');
			$eso->set_ordn($order->ordernumber);
			$eso->request_itemsearch($q);
			$results = PricingQuery::create()->findBySessionid(session_id());
			$html .= $config->twig->render('sales-orders/sales-order/edit/lookup/results.twig', ['q' => $q, 'results' => $results, 'soconfig' => $eso->config('so') ]);
		}
		return $html;
	}

	private static function qnotes($ordn) {
		$config = self::pw('config');
		$page   = self::pw('page');
		$qnotes = self::pw('modules')->get('QnotesSalesOrder');
		$html   = '<div class="mb-4"></div>';
		$html   .= $config->twig->render('sales-orders/sales-order/qnotes.twig', ['qnotes_so' => $qnotes, 'ordn' => $ordn]);
		return $html;
	}

	private static function js(EsoCRUD $eso, $ordn) {
		$config = self::pw('config');
		$page   = self::pw('page');
		$html   = '';

		if (self::pw('user')->is_editingorder($ordn)) {
			$html .= $config->twig->render('util/js-variables.twig', ['variables' => array('shiptos' => $eso->get_shiptos_json_array())]);
			$page->js   .= $config->twig->render('sales-orders/sales-order/edit/js/js.twig', ['eso' => $eso]);
			$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		}
		return $html;
	}

	private static function setupCstkLastSold($order) {
		$html = '';
		$modules = self::pw('modules');
		$config  = self::pw('config');
		$page    = self::pw('page');

		if ($modules->get('ConfigsCi')->option_lastsold == 'cstk') {
			$lastsold = $modules->get('LastSoldItemsCustomerCstk');
			$lastsold->custID = $order->custid;
			$lastsold->shiptoID = $order->shiptoid;
			$lastsold->function = 'eso';

			if ($lastsold->has_pricing()) {
				$lastsold->request_pricing();
			}
			$html .= $config->twig->render('sales-orders/sales-order/edit/last-sales/modal.twig', ['ordn' => $order->ordernumber, 'lastsold' => $lastsold, 'loader' => $config->twigloader, 'company' => $config->company]);
		}
		return $html;
	}

	private static function invalidSo($data) {
		$page = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Sales Order #$data->ordn not found";
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Sales Order Not Found, check if it\'s in History', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "SO # $data->ordn can not be found"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::lookupForm();
		return $html;
	}

	private static function invalidHistory($data) {
		$page = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Sales Order #$data->ordn is not editable";
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Order #$data->ordn is in Sales History", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "SO # $data->ordn is invoiced, and in history"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::lookupForm();
		return $html;
	}

	private static function lookupForm() {
		$config = self::pw('config');
		$html = $config->twig->render('sales-orders/sales-order/lookup-form.twig');
		return $html;
	}

	public static function editItem($data) {
		$eso = self::pw('modules')->get('SalesOrderEdit');

		$data = self::sanitizeParametersShort($data, ['ordn|ordn', 'linenbr|int', 'itemID|text']);
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

		// Validate Line Exists
		$q = SalesOrderDetailQuery::create()->filterByOrdernumber($data->ordn)->filterByLinenbr($data->linenbr);

		if ($data->linenbr !== 0 && $q->count() === 0) {
			$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Invalid Line #", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Line # $data->linenbr does not exist on SO # $data->ordn"]);
			return $html;
		}

		$orderitem = $q->findOneOrCreate();
		self::_setupOrderItem($eso, $orderitem, $data);
		$files = self::setupItemJsonFiles($eso, $orderitem);
		$html  = $config->twig->render('sales-orders/sales-order/edit/edit-item/display.twig', ['eso' => $eso, 'orderitem' => $orderitem, 'data' => $files]);
		return $html;
	}

	private static function _setupOrderItem(EsoCRUD $eso, SalesOrderDetail $orderitem, stdClass $data) {
		if ($orderitem->isNew()) {
			$orderitem->setOrdernumber($data->ordn);
			$orderitem->setLinenbr(0);
			$orderitem->setKit('N');
			$orderitem->setSpecialorder('N');

			$minvalidator = new MinValidator();

			if (empty($data->itemID) || $minvalidator->itemid($data->itemID) === false) {
				$data->itemID = ItemMasterItem::ITEMID_NONSTOCK;
				$orderitem->setSpecialorder('D');
			}
			$orderitem->setItemid($data->itemID);

			if ($orderitem->itemid != ItemMasterItem::ITEMID_NONSTOCK) {
				$eso = self::pw('modules')->get('SalesOrderEdit');
				$eso->request_itemsearch($orderitem->itemid);

				$pricing = PricingQuery::create()->filterBySessionid(session_id())->findOneByItemid($orderitem->itemid);
				$orderitem->setPrice($pricing ? $pricing->price : 0.0);
			}

			// SET ITEM WAREHOUSE
			// First Default to User, but if config is set to customer update it to customer warehouse
			$soconfig = self::pw('modules')->get('ConfigureSo')->config();
			$orderitem->setWhseid(self::pw('user')->whseid);

			if ($soconfig->default_ship_whse == ConfigSo::SHIP_WHSE_CUSTOMER) {
				$customer = $eso->customer($data->ordn);
				$orderitem->setWhseid($customer->whseid);
			}
		}

		if ($orderitem->nsvendorid == '') {
			$vxm = self::pw('modules')->get('XrefVxm');

			if ($vxm->poordercode_primary_exists($orderitem->itemid)) {
				$xref = $vxm->get_primary_poordercode_itemid($orderitem->itemid);
				$orderitem->setNsvendorid($xref->vendorid);
				$orderitem->setNsvendoritemid($xref->vendoritemid);
			}
		}
	}

	/**
	 * Get Json Files
	 * @param  EsoCRUD          $eso
	 * @param  SalesOrderDetail $orderitem
	 * @return array
	 */
	private static function setupItemJsonFiles(EsoCRUD $eso, SalesOrderDetail $orderitem) {
		$files = ['pricing' => false, 'stock' => false];

		if ($orderitem->itemid != ItemMasterItem::ITEMID_NONSTOCK) {
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

			$custID = SalesOrderQuery::create()->select(SalesOrder::aliasproperty('custid'))->findOneByOrdernumber($orderitem->ordernumber);
			$filter = new SalesHistoryDetailFilter();
			$filter->filterCustomerHistory($custID);
			$filter->query->filterByQty_ordered(1, Criteria::GREATER_EQUAL);
			$filter->query->limit(5);
			$files['pricehistory'] = $filter->query->find();
		}
		return $files;
	}
}
