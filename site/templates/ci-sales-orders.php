<?php
	use Propel\Runtime\ActiveQuery\Criteria;

	$query = SalesOrderQuery::create();
	$query->filterbySalesPerson('RDB');
    $custID = $input->get->text('custID');
    $query->filterByCustId("$custID");
	$query->orderByOehdordrdate('DESC');

	if ($input->get->filter) {
		if ($input->get->text('ordernumber_from') && $input->get->text('ordernumber_through')) {
			$query->filterByOrderNumber(array($input->get->text('ordernumber_from'), $input->get->text('ordernumber_through')));
		} else if ($input->get->text('ordernumber_from')) {
			$query->filterByOrderNumber($input->get->text('ordernumber_from'));
		} else if ($input->get->text('ordernumber_through')) {
			$query->filterByOrderNumber($input->get->text('ordernumber_through'));
		}

		if ($input->get->text('custpo')) {
			$custpo = $input->get->text('custpo');
			$query->filterByOehdcustpo("%$custpo%", Criteria::LIKE);
		}

		if ($input->get->text('orderdate_from') || $input->get->text('orderdate_through')) {
			$orderdate_from = date("Ymd", strtotime($input->get->text('orderdate_from')));
			if (empty($input->get->text('orderdate_through'))) {
				$orderdate_through = date('Ymd');
			} else {
				$orderdate_through = date("Ymd", strtotime($input->get->text('orderdate_through')));
			}
			if ($orderdate_from && $orderdate_through) {
				$query->filterByOrderDate(array($orderdate_from, $orderdate_through));
			} else if ($orderdate_from) {
				$query->filterByOrderDate($orderdate_from);
			} else if ($orderdate_through) {
				$query->filterByOrderDate($orderdate_through);
			}
		}

		if ($input->get->text('order_total_from') && $input->get->text('order_total_through')) {
			$query->filterByOrderTotal(array($input->get->text('order_total_from'), $input->get->text('order_total_through')));
		} else if ($input->get->text('order_total_from')) {
			$query->filterByOehdordrtot($input->get->text('order_total_from'), Criteria::GREATER_EQUAL);
		} else if ($input->get->text('order_total_through')) {
			$query->filterByOehdordrtot($input->get->text('order_total_through'), Criteria::LESS_EQUAL);
		}

		if ($input->get->status) {
			$statuses = array();

			foreach ($input->get->status as $status) {
				$sanitized = $sanitizer->text($status);

				if (array_key_exists($sanitized, SalesOrder::$status_descriptions)) {
					$statuses[] = $sanitized;
				}
			}
			$query->filterByOrderStatus($statuses);
		} else {
			$input->get->status = array();
		}
	} else {
		$input->get->status = array();
	}

	$count = $query->count();
	$orders = $query->paginate($input->pageNum, 10);

	$page->body = $config->twig->render('customers/ci/customer/orders-search-form.twig', ['page' => $page, 'input' => $input, 'custid' => $custID]);
	$page->body .= $config->twig->render('customers/ci/customer/sales-orders-list.twig', ['orders' => $orders, 'orderpage' => $pages->get('pw_template=sales-order-view')->url]);
	$page->body .= $config->twig->render('util/paginator.twig', ['page' => $page, 'pagenbr' => $input->pageNum, 'resultscount'=> $orders->getNbResults()]);
	include __DIR__ . "/basic-page.php";
