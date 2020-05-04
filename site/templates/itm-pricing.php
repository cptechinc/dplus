<?php
	$itm = $modules->get('Itm');
	$itm_pricing = $modules->get('ItmPricing');

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		if ($itm->item_exists($itemID)) {
			if ($input->requestMethod('POST') || $input->get->action) {
				$rm = strtolower($input->requestMethod());
				$itm_pricing->process_input($input);
				$itemID = $input->$rm->text('itemID');
				$session->redirect($page->itm_pricingURL($itemID));
			}

			if ($session->response_itm) {
				$page->body .= $config->twig->render('items/itm/response-alert.twig', ['response' => $session->response_itm]);
				$session->remove('response_itm');
			}

			$page->headline = "Pricing for $itemID";
			$itm_pricing = $modules->get('ItmPricing');
			$item = $itm->get_item($itemID);
			$itempricing = $itm_pricing->get_pricing($itemID);
			$unitsofm = UnitofMeasureSaleQuery::create()->find();
			$assortmentcodes = InvAssortmentCodeQuery::create()->find();

			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page->parent]);
			$page->body .= $config->twig->render('items/itm/pricing/form.twig', ['page' => $page, 'item' => $item, 'item_pricing' => $itempricing, 'unitsofm' => $unitsofm, 'assortmentcodes' => $assortmentcodes]);
			$page->js   .= $config->twig->render('items/itm/pricing/js.twig', ['page' => $page, 'item_pricing' => $itempricing]);
		} else {
			$session->redirect($page->itmURL($itemID));
		}
	} else {
		$session->redirect($page->itmURL());
	}

	include __DIR__ . "/basic-page.php";
