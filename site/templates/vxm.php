<?php
	$vxm = $modules->get('XrefVxm');

	if ($input->get->vendorID) {
	} elseif ($input->get->itemID) {
	} elseif ($input->get->search) {
		$q = $input->get->text('q');
		$searchtype = $input->get->text('search');

		if ($searchtype == 'vendors') {
			$exact_query = VendorQuery::create();

			if ($exact_query->filterByVendorid($q)->count() == 1) {
				$session->redirect($pages->vxm_vendorURL($q));
			} else {
				$page->headline = "VXM: Searching vendors for '$q'";
				$search_vendors = $modules->get('FilterVendors');
				$search_vendors->init_query($user);
				$search_vendors->filter_search($q);
				$search_vendors->apply_sortby($page);
				$query = $search_vendors->get_query();
				$vendors = $query->paginate($input->pageNum, 10);
				$page->searchURL = $page->url;
				$page->body .= $config->twig->render('items/vxm/search/vendors-search.twig', ['page' => $page, 'vendors' => $vendors]);
				$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $vendors->getNbResults()]);
			}
		} elseif ($searchtype == 'items') {

		}
	} else {
		$page->body .= $config->twig->render('items/vxm/vxm-search.twig', ['page' => $page]);
	}

	include __DIR__ . "/basic-page.php";
