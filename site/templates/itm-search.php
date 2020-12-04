<?php
	$q = $input->get->q ? $input->get->text('q') : '';
	$page->title = $q ? "Item results for '$q'" : "Search for an Item";

	$filter_itm = $modules->get('FilterItemMaster');
	$filter_itm->init_query($user);
	$filter_itm->search($q);
	$query = $filter_itm->get_query();
	$items = $query->paginate($input->pageNum, 10);

	$page->searchURL = $page->url;
	$page->body .= $config->twig->render('items/item-search.twig', ['page' => $page, 'items' => $items]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $items->getNbResults()]);

	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
