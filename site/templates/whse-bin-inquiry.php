<?php
	$page->print = ($page->template == 'print');
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);

	if ($input->get->binID) {
		$binID = $input->get->text('binID');
		$page->binID = $binID;
		$page->title = "Bin Inquiry for $binID";
		$items = InvsearchQuery::create()->findDistinctItems(session_id(), $binID);
		$resultscount = InvsearchQuery::create()->countDistinctItemid(session_id(), $binID);
		$inventory = InvsearchQuery::create();
		$page->body =  $config->twig->render('warehouse/inventory/bin-inquiry/results.twig', ['page' => $page, 'resultscount' => $resultscount, 'items' => $items, 'warehouse' => $warehouse, 'inventory' => $inventory]);
	} else {
		$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;
		$page->body =  $config->twig->render('warehouse/inventory/bin-form.twig', ['page' => $page]);
		$page->body .= $config->twig->render('warehouse/inventory/bins-modal.twig', ['warehouse' => $warehouse]);
	}
	
	// Add JS
	$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/shared.js'));
	$config->scripts->append(hash_templatefile('scripts/warehouse/bin-inquiry.js'));

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
