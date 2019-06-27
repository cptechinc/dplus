<?php
	use ItemsearchQuery, Itemsearch;

	$module_ii = $modules->get('MiiPages');
	$module_ii->init_iipage();

	if ($input->get->itemID) {
		$itemID = $input->get->text('itemID');
		$query = ItemsearchQuery::create();
		$query->filterActive();
		$query->filterByOrigintype([Itemsearch::ORIGINTYPE_VENDOR, Itemsearch::ORIGINTYPE_ITEM]);
		$query->filterByItemid($itemID);

		if ($query->count()) {
			$page->title = "Item Information: $itemID";
			$item = ItemMasterItemQuery::create()->findOneByItemid($itemID);
			$page->body = $config->twig->render('items/ii/item/description.twig', ['item' => $item]);
			$page->body .= $config->twig->render('items/ii/item/item-data.twig', ['item' => $item]);

			$itempricing = ItemPricingQuery::create()->findOneByItemid($itemID);
			$page->body .= $config->twig->render('items/ii/item/pricing.twig', ['itempricing' => $itempricing]);
		} else {
			$page->headline = $page->title = "Item $itemID could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['title' => $page->title, 'msg' => "Check if the item ID is correct"]);
		}
	} else {
		$q = $input->get->q ? $input->get->text('q') : '';
		$query = ItemsearchQuery::create();
		$query->filterActive();
		$query->filterByOrigintype([Itemsearch::ORIGINTYPE_VENDOR, Itemsearch::ORIGINTYPE_ITEM]);
		$query->where("MATCH(Itemsearch.itemid, Itemsearch.refitemid, Itemsearch.desc1, Itemsearch.desc2) AGAINST (? IN BOOLEAN MODE)", $q);
		$query->groupby('itemid');

		if ($query->count() == 1) {
			$item = $query->findOne();
			$session->redirect($page->url."?itemID=$item->itemid");
		} else {
			$items = $query->paginate($input->pageNum, 10);
		}

		$page->searchURL = $page->url;
		$page->body = $config->twig->render('items/item-search.twig', ['page' => $page, 'items' => $items]);
	}

	include __DIR__ . "/basic-page.php";
