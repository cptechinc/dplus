<?php
	use Propel\Runtime\ActiveQuery\Criteria;

	$query = SalesOrderQuery::create();
	$query->filterbySalesPerson('RDB');
	$query->orderByOehdordrdate('DESC');

	// filterByOehdorigordrnbr - no range
	// filterByArcucustid - no range
	// filterByOehdcustpo
	// filterByOehdordrdate - no range
	// filterByOehdordrtot - range
	// filterByOehdstat - no range

	if ($input->get->filter) {
		if ($input->get->text('ordernumber1') || $input->get->text('ordernumber2')) {
			$query->filterByOrdn(array($input->get->text('ordernumber1'), $input->get->text('ordernumber2')));
		}

		if ($input->get->text('custid1')) {
			$custid = strtoupper($input->get->text('custid1'));
			$query->filterByArcucustid("%$custid%", Criteria::LIKE);
		}

		if ($input->get->text('custpo')) {
			$custpo = strtoupper($input->get->text('custpo'));
			$query->filterByOehdcustpo("%$custpo%", Criteria::LIKE);
		}

		if ($input->get->text('orderdate1')) {
			$orderdate = date_format($input->get->text('orderdate1'), 'Ymd');
			$query->filterByOehdordrdate("%$orderdate%", Criteria::LIKE);
		}

		// works with two
		// does not work with just min
		// works with just max
		if ($input->get->text('order_total1') || $input->get->text('order_total2')) {
			$query->filterByOehdordrtot(array('min' => $input->get->text('order_total1'), 'max' => $input->get->text('order_total2')));
		}
	}

	$count = $query->count();
	$orders = $query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('sales-orders/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $config->twig->render('sales-orders/sales-orders-list.twig', ['orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	$page->body .= "Count: " . $count . "<br>";
	include __DIR__ . "/basic-page.php";
