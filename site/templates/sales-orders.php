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
		if ($input->get->text('ordernumber1') && $input->get->text('ordernumber2')) {
			$query->filterByOrderNumber(array($input->get->text('ordernumber1'), $input->get->text('ordernumber2')));
		} else if ($input->get->text('ordernumber1')) {
			$query->filterByOrderNumber($input->get->text('ordernumber1'));
		} else if ($input->get->text('ordernumber2')) {
			$query->filterByOrderNumber($input->get->text('ordernumber2'));
		}

		if ($input->get->text('custid1') || $input->get->text('custid2')) {
			$custid1 = strtoupper($input->get->text('custid1'));
			$custid2 = strtoupper($input->get->text('custid2'));
			if ($custid1 && $custid2) {
				$query->filterByCustId(array($custid1, $custid2));
			} else if ($custid1) {
				$query->filterByCustId("%$custid1%");
			} else if ($custid2) {
				$query->filterByCustId("%$custid2%");
			}
		}

		if ($input->get->text('custpo')) {
			$custpo = strtoupper($input->get->text('custpo'));
			$query->filterByOehdcustpo("%$custpo%", Criteria::LIKE);
		}

		if ($input->get->text('orderdate1') || $input->get->text('orderdate2')) {
			$orderdate1 = date("Ymd", strtotime($input->get->text('orderdate1')));
			if (empty($input->get->text('orderdate2'))) {
				$orderdate2 = date('Ymd');
			} else {
				$orderdate2 = date("Ymd", strtotime($input->get->text('orderdate2')));
			}
			if ($orderdate1 && $orderdate2) {
				$query->filterByOrderDate(array($orderdate1, $orderdate2));
			} else if ($orderdate1) {
				$query->filterByOrderDate($orderdate1);
			} else if ($orderdate2) {
				$query->filterByOrderDate($orderdate2);
			}
		}

		if ($input->get->text('order_total1') && $input->get->text('order_total2')) {
			$query->filterByOrderTotal(array($input->get->text('order_total1'), $input->get->text('order_total2')));
		} else if ($input->get->text('order_total1')) {
			$query->filterByOehdordrtot($input->get->text('order_total1'), Criteria::GREATER_EQUAL);
		} else if ($input->get->text('order_total2')) {
			$query->filterByOehdordrtot($input->get->text('order_total2'), Criteria::LESS_EQUAL);
		}

		if ($input->get->text('status-n') || $input->get->text('status-i') || $input->get->text('status-p') || $input->get->text('status-v')) {
			$status = array($input->get->text('status-n'), $input->get->text('status-i'), $input->get->text('status-p'), $input->get->text('status-v'));
			$query->filterByOrderStatus($status);
		}

	}

	$count = $query->count();
	$orders = $query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('sales-orders/search-form.twig', ['page' => $page, 'input' => $input]);
	$page->body .= $config->twig->render('sales-orders/sales-orders-list.twig', ['orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	$page->body .= "Count: " . $count . "<br>";
	include __DIR__ . "/basic-page.php";
