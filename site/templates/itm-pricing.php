<?php
	$itm = $modules->get('Itm');

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		if ($itm->item_exists($itemID)) {
			$page->headline = "Pricing for $itemID";
			$itm_pricing = $modules->get('ItmPricing');
			$item = $itm->get_item($itemID);
			$itempricing = $itm_pricing->get_pricing($itemID);
			$unitsofm = UnitofMeasureSaleQuery::create()->find();

			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);
			$page->body .= $config->twig->render('items/itm/pricing/form.twig', ['page' => $page, 'item' => $item, 'item_pricing' => $itempricing, 'unitsofm' => $unitsofm]);
		} else {
			$session->redirect($page->itmURL($itemID));
		}
	} else {
		$session->redirect($page->itmURL());
	}

	include __DIR__ . "/basic-page.php";
