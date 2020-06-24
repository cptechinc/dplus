<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config_inventory = $modules->get('ConfigsWarehouseInventory');
	$config_picking   = $modules->get('ConfigsWarehousePicking');
	$http = new ProcessWire\WireHttp();

	$template = '';

	if (file_exists(__DIR__."/whse-picking-$config->company.php")) {
		$template = "whse-picking-$config->company";
	} else {
		$template = 'whse-picking-unguided';
	}


	$action = 'start-pick-unguided';

	// CHECK If Sales Order is Provided
	if ($input->get->ordn) {
		$ordn = SalesOrder::get_paddedordernumber($input->get->text('ordn'));
		$pickorder = PickSalesOrderQuery::create()->findOneByOrdernbr($ordn);

		if ($config_picking->picking_method == 'unguided') {
			$whsesession->set('bin', 'WHSE');
			$whsesession->save();
		}

		if ($whsesession->is_orderinvalid()) { // Validate Sales Order Number
			$page->formurl = $page->parent->child('template=redir')->url;
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Sales Order #$ordn Not Found"]);
			$page->body .= "<div class='form-group'></div>";
			$page->body .= $config->twig->render('warehouse/picking/sales-order-form.twig', ['page' => $page]);
			// CHECK the Order is not finished
		} elseif ($whsesession->is_usingwrongfunction()) {
			$page->body = $config->twig->render('warehouse/picking/status.twig', ['page' => $page, 'whsesession' => $whsesession]);
		} elseif ($whsesession->is_orderonhold() || $whsesession->is_orderverified() || $whsesession->is_orderinvoiced() || $whsesession->is_ordernotfound() || (!$config_inventory->allow_negativeinventory && $whsesession->is_ordershortstocked())) {
			$page->body = $config->twig->render('warehouse/picking/status.twig', ['page' => $page, 'whsesession' => $whsesession]);
		} elseif ($whsesession->needs_functionprompt()) {
			if ($input->get->removeprompt) {
				$whsesession->set_functionprompt(false);
				$whsesession->save();
				$page->fullURL->query->remove('removeprompt');
				$session->redirect($page->fullURL->getUrl());
			} else {
				$page->cancelURL = $page->parent->child('template=redir')->url.'?action=cancel-order';
				$page->fullURL->query->set('removeprompt', 'removeprompt');
				$page->removepromptURL = $page->fullURL->getUrl();
				$page->body = $config->twig->render('warehouse/picking/function-prompt.twig', ['page' => $page, 'whsesession' => $whsesession]);
			}
		} else { // PICKING FUNCTION
			$page->title = "Picking Order #$ordn";
			include __DIR__ . "/$template.php";
		}
	} else {
		$modules->get('DplusRequest')->self_request($page->parent->child('template=redir')->url."?action=$action&sessionID=".session_id());
		$page->formurl = $page->parent->child('template=redir')->url;
		$page->body = $config->twig->render('warehouse/picking/sales-order-form.twig', ['page' => $page]);
	}
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/pick-order.js'));

	include __DIR__ . "/basic-page.php";
