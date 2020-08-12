<?php
	$whsesession = WhsesessionQuery::create()->findOneBySessionid(session_id());
	$warehouse   = WarehouseQuery::create()->findOneByWhseid($whsesession->whseid);
	$config->inventory = $modules->get('ConfigsWarehouseInventory');
	$searchinventory = $modules->get('SearchInventory');

	$page->formurl = $page->parent('template=warehouse-menu')->child('template=redir')->url;

	if (!$page->print && !$input->get->scan) {
		$page->body = $config->twig->render('warehouse/item-form.twig', ['page' => $page]);
	}

	if ($input->get->scan) {
		$scan = $input->get->text('scan');
		$page->scan = $scan;
		$page->title = "Find Item Inquiry for $scan";
		$items = $searchinventory->get_items_distinct();

		if (file_exists($config->paths->templates."twig/warehouse/inventory/find-item/$config->company/results.twig")) {
			$page->body .= $config->twig->render("warehouse/inventory/find-item/$config->company/results.twig", ['page' => $page, 'config' => $config->inventory, 'inventory' => $searchinventory, 'warehouse' => $warehouse]);
		} else {
			$page->body .= $config->twig->render('warehouse/inventory/find-item/results.twig', ['page' => $page, 'config' => $config->inventory, 'inventory' => $searchinventory, 'warehouse' => $warehouse]);
		}
	}

	$config->scripts->append(hash_templatefile('scripts/warehouse/find-item.js'));

	if ($page->print) {
		$page->show_title = true;
		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
