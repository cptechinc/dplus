<?php
	$module_ii = $modules->get('DpagesMii');
	$module_ii->init_iipage();

	$html = $modules->get('HtmlWriter');
	$lookup_ii = $modules->get('LookupItemIi');

	$itemID = $input->get->text('itemID');



	if ($input->get->itemID) {
		if ($lookup_ii->lookup_itm($itemID)) {
			$item = ItemMasterItemQuery::create()->findOneByItemid($itemID);
		} else {
			$page->headline = $page->title = "Item $itemID could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Check if the item ID is correct"]);
		}
	} else {
		$page->headline = $page->title = "Item ID was not provided";
		$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Item ID was not provided"]);
	}
