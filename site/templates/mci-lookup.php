<?php
	$html = $modules->get('HtmlWriter');

	$query = CustomerQuery::create();
	$exact_query = CustomerQuery::create();

	if ($input->get->q) {
		$q = strtoupper($input->get->text('q'));

		$page->headline = "CI: Searching for '$q'";
		$col_custid = Customer::get_aliasproperty('custid');
		$col_name = Customer::get_aliasproperty('name');
		$columns = array($col_custid, $col_name);
		$query->search_filter($columns, strtoupper($q));
	}

	if ($page->has_orderby()) {
		$orderbycolumn = $page->orderby_column;
		$sort = $page->orderby_sort;
		$tablecolumn = Customer::get_aliasproperty($orderbycolumn);
		$query->sortBy($tablecolumn, $sort);
	}

	if ($user->is_salesrep()) {
		$query->filterByCustid($user->get_customers(), Criteria::IN);
	}

	$customers = $query->paginate($input->pageNum, 10);

	$page->searchURL = $page->url;

	$search = $config->twig->render('customers/customer-search.twig', ['page' => $page, 'customers' => $customers]);
	$paginator = $config->twig->render('util/paginator.twig', ['page' => $page, 'resultscount'=> $customers->getNbResults()]);
	$page->body = $html->div('id=lookup', $search.$paginator);

if ($config->ajax) {
	echo $page->body;
} else {
	include __DIR__ . "/basic-page.php";
}
