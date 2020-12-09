<?php

$rm = strtolower($input->requestMethod());
$values = $input->$rm;

$filter = $modules->get('FilterXrefItemMxrfe');
$mxrfe  = $modules->get('XrefMxrfe');


$page->show_breadcrumbs = false;
$page->body .= $config->twig->render('items/mxrfe/bread-crumbs.twig', ['page' => $page]);

if ($values->vendorID) {
	$vendorID = $values->text('vendorID');
	$vendor = $mxrfe->vendor($vendorID);
	
	if ($values->vendoritemID) {

	} else {
		$filter->vendorid($vendorID);
		$filter->apply_sortby($page);
		$xrefs = $filter->query->paginate($input->pageNum, $session->display);
		$page->body .= $config->twig->render('items/mxrfe/list/vendor/page.twig', ['page' => $page, 'mxrfe' => $mxrfe, 'xrefs' => $xrefs, 'vendor' => $vendor]);
	}
} else {
	$filter = $modules->get('FilterVendors');
	$filter->vendorid($mxrfe->vendorids());
	$filter->apply_sortby($page);
	$vendors = $filter->query->paginate($input->pageNum, $session->display);
	$page->body .= $config->twig->render('items/mxrfe/search/vendor/results.twig', ['page' => $page, 'vendors' => $vendors]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $vendors->getNbResults()]);
	// $page->js   .= $config->twig->render('items/mxrfe/list/js.twig', ['page' => $page]);
}

include __DIR__ . "/basic-page.php";
