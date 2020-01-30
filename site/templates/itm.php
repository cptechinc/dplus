<?php
	$page->title = "ITM";

	if ($input->get->itemID) {

		$itemID = $input->get->text('itemID');

		$q = ItemMasterItemQuery::create();
		$q->filterByItemid($itemID);

		if ($q->count()) {
			$page->title .= " $itemID";
			$item = $q->findOne();
			$itemgroups = ItemGroupCodeQuery::create()->find();
			$pricecodes = ItemPricingQuery::create()->find();
			$page->body .= $config->twig->render('items/itm/itm-form.twig', ['page' => $page, 'item' => $item, 'itemgroups' => $itemgroups, 'pricecodes' => $pricecodes]);
		} else {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item ID $itemID not found in the Item Master"]);
		}
	}

	include __DIR__ . "/basic-page.php";
