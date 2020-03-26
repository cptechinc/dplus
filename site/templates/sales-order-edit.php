<?php
	$config_salesorders = $modules->get('ConfigsSalesOrders');
	$modules->get('DpagesMso')->init_salesorder_hooks();
	$html = $modules->get('HtmlWriter');
	$lookup_orders = $modules->get('LookupSalesOrder');

	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');

		if ($lookup_orders->lookup_salesorder($ordn)) {
			if (!OrdrhedQuery::create()->filterBySessionidOrder(session_id(), $ordn)->count()) {
				$modules->get('DplusRequest')->self_request($page->edit_orderURL($ordn));
			}
			$module_edit = $modules->get('SalesOrderEdit');
			$module_edit->set_ordn($ordn);
			$order = $module_edit->get_order_edit();

			$customer = CustomerQuery::create()->findOneByCustid($order->custid);
			$page->title = "Editing Sales Order #$ordn";
			$page->listpage = $pages->get('pw_template=sales-orders');
			$page->formurl = $pages->get('template=dplus-menu, name=mso')->child('template=redir')->url;
			$page->lookupURL = $pages->get('pw_template=ii-item-lookup')->httpUrl;

			$page->body .= $config->twig->render('sales-orders/sales-order/edit/links-header.twig', ['page' => $page, 'user' => $user, 'order' => $order]);
			$page->body .= $config->twig->render('sales-orders/sales-order/edit/sales-order-header.twig', ['page' => $page, 'customer' => $customer, 'order' => $module_edit->get_order_static()]);

			if ($user->is_editingorder($order->ordernumber)) {
				$page->body .= $config->twig->render('sales-orders/sales-order/edit/edit-form.twig', ['page' => $page, 'order' => $order, 'states' => $module_edit->get_states(), 'shipvias' => $module_edit->get_shipvias(), 'warehouses' => $module_edit->get_warehouses(), 'termscodes' => $module_edit->get_termscodes(), 'shiptos' => $customer->get_shiptos()]);
			}

			if ($modules->get('ConfigsCi')->option_lastsold  == 'cstk') {
				$lastsold = $modules->get('LastSoldItemsCustomerCstk');
				$lastsold->custID = $order->custid;
				$lastsold->shiptoID = $order->shiptoid;
				$lastsold->function = 'eso';

				if ($lastsold->has_pricing()) {
					$lastsold->request_pricing();
				}
			} else {
				$lastsold = false;
			}

			if ($config->twigloader->exists("sales-orders/sales-order/edit/$config->company/order-items.twig")) {
				$page->body .= $config->twig->render("sales-orders/sales-order/edit/$config->company/order-items.twig", ['page' => $page, 'order' => $order, 'module_edit' => $module_edit, 'user' => $user]);
			} else {
				$page->body .= $config->twig->render('sales-orders/sales-order/edit/order-items.twig', ['page' => $page, 'order' => $order, 'module_edit' => $module_edit, 'user' => $user]);
			}

			if ($user->is_editingorder($order->ordernumber)) {
				$page->body .= $html->div('class=mt-3');
				$page->body .= $html->h3('class=text-secondary', 'Add Item');

				if ($config->twigloader->exists("sales-orders/sales-order/edit/$config->company/add-item-form.twig")) {
					$page->body .= $config->twig->render("sales-orders/sales-order/edit/$config->company/add-item-form.twig", ['page' => $page, 'order' => $order]);
				} else {
					$page->body .= $config->twig->render('sales-orders/sales-order/edit/add-item-form.twig', ['page' => $page, 'order' => $order]);
				}

				$page->js .= $config->twig->render('sales-orders/sales-order/edit/item-lookup.js.twig', ['page' => $page, 'order' => $order]);

				if ($input->get->q) {
					$q = $input->get->text('q');
					$module_edit->request_itemsearch($q);
					$results = PricingQuery::create()->findBySessionid(session_id());
					$page->body .= $config->twig->render('cart/lookup-results.twig', ['q' => $q, 'results' => $results]);
				}

				$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => array('shiptos' => $module_edit->get_shiptos_json_array())]);
				$page->body .= $config->twig->render('sales-orders/sales-order/edit/last-sales/modal.twig', ['page' => $page, 'module_edit' => $module_edit, 'lastsold' => $lastsold, 'loader' => $config->twigloader, 'company' => $config->company]);
				$config->scripts->append(hash_templatefile('scripts/orders/edit-order.js'));
				$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
			}
			$module_qnotes = $modules->get('QnotesSalesOrder');
			$page->body .= $html->div('class=mb-4');
			$page->body .= $config->twig->render('sales-orders/sales-order/qnotes.twig', ['page' => $page, 'qnotes_so' => $module_qnotes, 'ordn' => $ordn]);
			$page->body .= $config->twig->render('sales-orders/sales-order/notes/note-modal.twig', ['page' => $page, 'ordn' => $ordn]);
			$config->scripts->append(hash_templatefile('scripts/quotes/quote-notes.js'));
		} elseif ($lookup_orders->lookup_saleshistory($ordn)) {
			$page->headline = $page->title = "Sales Order #$ordn is not editable";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Sales Order #$ordn is in Sales History"]);
		} else {
			$page->headline = $page->title = "Sales Order #$ordn could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Order Number is correct or if it is in Sales History"]);
		}
	} else {
		$page->body = $config->twig->render('sales-orders/sales-order/lookup-form.twig', ['page' => $page]);
	}
	include __DIR__ . "/basic-page.php";
