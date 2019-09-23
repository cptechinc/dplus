<?php
	use ItemsearchQuery, Itemsearch;
	use WarehouseQuery, Warehouse;

	$module_ii = $modules->get('MiiPages');
	$module_ii->init_iipage();

	$html = $modules->get('HtmlWriter');

	$itemID = $input->get->text('itemID');

	$itemquery = ItemsearchQuery::create();
	$itemquery->filterActive();
	$itemquery->filterByOrigintype([Itemsearch::ORIGINTYPE_VENDOR, Itemsearch::ORIGINTYPE_ITEM]);
	$itemquery->filterByItemid($itemID);

	$load_item = $modules->get('IiLoadItem');
	$load_item->set_itemID($itemID);
	$item = $load_item->get_item();

	if ($input->get->itemID) {
		if ($itemquery->count()) {

		} else {
			$page->headline = $page->title = "Item $itemID could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Check if the item ID is correct"]);
		}
	} else {
		$page->headline = $page->title = "Item ID was not provided";
		$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Item ID was not provided"]);
	}
