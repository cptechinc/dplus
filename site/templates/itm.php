<?php
	$page->title = "ITM";
	$itm = $modules->get('Itm');

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');

		$q = ItemMasterItemQuery::create();
		$q->filterByItemid($itemID);

		if ($q->count()) {
			$page->title .= " $itemID";
			$item = $q->findOne();

			$page->customerlookupURL = $pages->get('pw_template=mci-lookup')->url;
			$page->body .= $config->twig->render('items/itm/itm-links.twig', ['page' => $page, 'page_itm' => $page]);
			$page->body .= $config->twig->render('items/itm/itm-form.twig', ['page' => $page, 'item' => $item, 'm_itm' => $itm]);
			$page->body .= $config->twig->render("util/ajax-modal.twig");
			$page->js   .= $config->twig->render("items/itm/js.twig", ['page' => $page]);
		} else {
			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => "Error!", 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Item ID $itemID not found in the Item Master"]);
		}
	} elseif ($input->get->q) {
		$q = strtoupper($input->get->text('q'));
		$exact_query = ItemMasterItemQuery::create();

		if ($exact_query->filterByItemid($q)->count() == 1) {
			$session->redirect($page->itmURL($q));
		} else {
			$page->headline = "ITM: Searching Items for '$q'";
			$search_items = $modules->get('FilterItemMaster');
			$search_items->init_query($user);
			$search_items->filter_search($q);
			$search_items->apply_sortby($page);
			$query = $search_items->get_query();
			$items = $query->paginate($input->pageNum, 10);
			$page->searchURL = $page->url;
			$page->body .= $config->twig->render('items/vxm/search/item/item-search.twig', ['page' => $page, 'items' => $items]);
			$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);
		}
	} else {
		$page->searchURL = $page->url;
		$page->body .= $config->twig->render('items/item-search.twig', ['page' => $page, 'items' => array()]);
	}
	include __DIR__ . "/basic-page.php";
