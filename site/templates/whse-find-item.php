<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);

	$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;

	$page->print = ($page->template == 'print');

	if (!$page->print && !$input->get->scan) {
		$page->body = $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
	}

	if ($input->get->scan) {
		$scan = $input->get->text('scan');
		$page->scan = $scan;
		$page->title = "Find Item Inquiry for $scan";
		$inventory = InvsearchQuery::create();
		$resultscount = InvsearchQuery::create()->countDistinctItemid(session_id());
		$items = InvsearchQuery::create()->findByItemidDistinct(session_id());
		$page->body .= $config->twig->render('warehouse/inventory/find-item/results.twig', ['page' => $page, 'resultscount' => $resultscount, 'items' => $items, 'inventory' => $inventory, 'warehouse' => $warehouse]);
	}

	if ($page->print) {
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
