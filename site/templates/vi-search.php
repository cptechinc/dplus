<?php
	$page->title = "Search Vendors";
	$modules->get('DpagesMvi')->init_vipage();
	$html = $modules->get('HtmlWriter');
	$validate = new Dplus\CodeValidators\Map();

	$filter_vendors = $modules->get('FilterVendors');
	$filter_vendors->init_query($user);

	if ($input->get->q) {
		$q = strtoupper($input->get->text('q'));

		if ($validate->vendorid($q)) {
			$session->redirect($page->get_vi_vendorURL($q), $http301 = false);
		}

		$page->headline = "Searching Vendors for '$q'";
		$filter_vendors->search($q);
	}

	$filter_vendors->apply_sortby($page);
	$query = $filter_vendors->get_query();
	$vendors = $query->paginate($input->pageNum, 10);

	$page->searchURL = $page->url;
	$page->body = $config->twig->render('vendors/vendors-search.twig', ['page' => $page, 'vendors' => $vendors]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $vendors->getNbResults()]);


	if ($config->ajax) {
		echo $page->body;
	} else {
		include __DIR__ . "/basic-page.php";
	}
