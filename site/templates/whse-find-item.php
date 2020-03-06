<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config_inventory = $modules->get('ConfigsWarehouseInventory');

	$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;

	if (!$page->print && !$input->get->scan) {
		$page->body = $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
	}

	if ($input->get->scan) {
		$scan = $input->get->text('scan');
		$page->scan = $scan;
		$page->title = "Find Item Inquiry for $scan";
		$inventory = InvsearchQuery::create();
		$resultscount = InvsearchQuery::create()->countDistinctItemid(session_id());
		$items = InvsearchQuery::create()->findDistinctItems(session_id());

		if (file_exists($config->paths->templates."twig/warehouse/inventory/find-item/$config->company/results.twig")) {
			$page->body .= $config->twig->render("warehouse/inventory/find-item/$config->company/results.twig", ['page' => $page, 'config' => $config_inventory, 'resultscount' => $resultscount, 'items' => $items, 'inventory' => $inventory, 'warehouse' => $warehouse]);
		} else {
			$page->body .= $config->twig->render('warehouse/inventory/find-item/results.twig', ['page' => $page, 'config' => $config_inventory, 'resultscount' => $resultscount, 'items' => $items, 'inventory' => $inventory, 'warehouse' => $warehouse]);
		}
	}

	$config->scripts->append(hash_templatefile('scripts/warehouse/find-item.js'));

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
