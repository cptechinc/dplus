<?php
	use ProcessWire\WireHttp;

	$warehousepacking = $modules->get('WarehousePacking');
	$warehousepacking->set_sessionID(session_id());
	$whsesession = $warehousepacking->get_whsesession();
	$warehouse   = $warehousepacking->get_warehouseconfig();
	$http = new WireHttp();

	if ($input->get->ordn) {
		$ordn = $input->get->text('ordn');

		if (SalesOrderQuery::create()->orderExists($ordn)) {
			$warehousepacking->set_ordn($ordn);
			$http->get($pages->get('template=redir, redir_file=sales-order')->httpUrl."?action=get-order-notes&ordn=$ordn&sessionID=".session_id());
			$page->body = $config->twig->render('warehouse/packing/order-notes.twig', ['page' => $page, 'notes' => $warehousepacking->get_packingnotes()]);
			$page->body .= $config->twig->render('warehouse/packing/select-line-form.twig', ['page' => $page, 'warehousepacking' => $warehousepacking, 'lines' => $warehousepacking->get_packsalesorderdetails()]);
		} else {
			$page->body = $config->twig->render('warehouse/packing/status.twig', ['page' => $page, 'message' => "Error finding Sales Order # $ordn"]);
		}
	} else {
		$page->formurl = $page->child('template=redir')->url;
		$page->body = $config->twig->render('warehouse/packing/sales-order-form.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
