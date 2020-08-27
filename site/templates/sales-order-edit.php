<?php
	$modules->get('DpagesMso')->init_salesorder_hooks();
	$html = $modules->get('HtmlWriter');
	$lookup_orders = $modules->get('LookupSalesOrder');
	$eso = $modules->get('SalesOrderEdit');

	$rm = strtolower($input->requestMethod());
	$values = $input->$rm;

	if ($values->action) {
		$eso->process_input($input);

		if ($values->exit) {
			$url = $page->so_viewURL($values->text('ordn'));
		} else {
			$url = $page->so_editURL($values->text('ordn'));
		}
		$session->redirect($url, $http301 = false);
	}

	if ($values->ordn) {
		$ordn = $values->text('ordn');

		if ($lookup_orders->lookup_salesorder($ordn)) {

			if ($eso->can_order_be_edited($ordn))  {
				$eso->request_so_edit($ordn);
			}

			if ($eso->exists_editable($ordn)) {
				$eso->set_ordn($ordn);
				$order = $eso->get_editable_header($ordn);
				$customer = CustomerQuery::create()->findOneByCustid($order->custid);
				$page->title = "Editing Sales Order #$ordn";
				$page->listpage = $pages->get('pw_template=sales-orders');
				$page->formurl = $pages->get('template=dplus-menu, name=mso')->child('template=redir')->url;
				$page->lookupURL = $pages->get('pw_template=ii-item-lookup')->httpUrl;

				$page->body .= $config->twig->render('sales-orders/sales-order/edit/links-header.twig', ['page' => $page, 'user' => $user, 'order' => $order]);
				$page->body .= $config->twig->render('sales-orders/sales-order/edit/sales-order-header.twig', ['page' => $page, 'customer' => $customer, 'order' => $eso->get_order_static($ordn)]);

				if ($user->is_editingorder($order->ordernumber)) {
					$page->body .= $config->twig->render('sales-orders/sales-order/edit/edit-form.twig', ['page' => $page, 'order' => $order, 'states' => $eso->get_states(), 'shipvias' => $eso->get_shipvias(), 'warehouses' => $eso->get_warehouses(), 'termscodes' => $eso->get_termscodes(), 'shiptos' => $customer->get_shiptos()]);
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
					$page->body .= $config->twig->render("sales-orders/sales-order/edit/$config->company/order-items.twig", ['page' => $page, 'order' => $order, 'eso' => $eso, 'user' => $user]);
				} else {
					$page->body .= $config->twig->render('sales-orders/sales-order/edit/order-items.twig', ['page' => $page, 'order' => $order, 'eso' => $eso, 'user' => $user]);
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
						$eso->request_itemsearch($q);
						$results = PricingQuery::create()->findBySessionid(session_id());
						$page->body .= $config->twig->render('sales-orders/sales-order/edit/item-lookup-results.twig', ['q' => $q, 'results' => $results, 'soconfig' => $eso->config('so') ]);
					}

					$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => array('shiptos' => $eso->get_shiptos_json_array())]);
					$page->body .= $config->twig->render('sales-orders/sales-order/edit/last-sales/modal.twig', ['page' => $page, 'ordn' => $ordn, 'lastsold' => $lastsold, 'loader' => $config->twigloader, 'company' => $config->company]);
					$config->scripts->append(hash_templatefile('scripts/orders/edit-order.js'));
					$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
				}
				$module_qnotes = $modules->get('QnotesSalesOrder');
				$page->body .= $html->div('class=mb-4');
				$page->body .= $config->twig->render('sales-orders/sales-order/qnotes.twig', ['page' => $page, 'qnotes_so' => $module_qnotes, 'ordn' => $ordn]);
				$page->body .= $config->twig->render('sales-orders/sales-order/notes/note-modal.twig', ['page' => $page, 'ordn' => $ordn]);
				$config->scripts->append(hash_templatefile('scripts/quotes/quote-notes.js'));
			} else {
				if ($input->get->load) {
					$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => $page->title, 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Order Number # $ordn can not be loaded for editing"]);
				} else {
					$eso->request_so_edit($ordn);
					$page->fullURL->query->set('load', 1);
					$session->redirect($page->fullURL->getUrl(), $http301 = false);
				}
			}
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
