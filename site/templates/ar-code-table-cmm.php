<?php
	$html = $modules->get('HtmlWriter');
	$recordlocker = $modules->get('RecordLockerUser');

	if ($input->get->code) {
		$code = $input->get->text('code');

		if ($module_codetable->code_exists($code)) {
			$page->title = $page->headline = "CMM: $code";
			$customer = $module_codetable->get_code($code);
		} else {
			$page->title = $page->headline = "Create $page->title";
			$customer = new Customer();

			if ($code != 'new') {
				$customer->setCustid($code);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Customer '$code' could not be found, use the form to create it"]);
				$page->body .= $html->div('class=mb-3');
			}
		}

		if (!$customer->isNew()) {
			/**
			 * Show alert that customer is locked if
			 *  1. Customer Isn't new
			 *  2. The customer has a record lock
			 *  3. Userid does not match the lock
			 * Otherwise if not locked, create lock
			 */
			if ($recordlocker->function_locked($page->codetable, $customer->custid) && !$recordlocker->function_locked_by_user($page->codetable, $customer->custid)) {
				$msg = "$customer->custid is being locked by " . $recordlocker->get_locked_user($page->codetable, $customer->custid);
				$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Customer $customer->custid is locked", 'iconclass' => 'fa fa-lock fa-2x', 'message' => $msg]);
				$page->body .= $html->div('class=mb-3');
			} elseif (!$recordlocker->function_locked($page->codetable, $customer->custid)) {
				$recordlocker->create_lock($page->codetable, $customer->custid);
			}
		}

		$page->body .= $config->twig->render("code-tables/mar/$page->codetable/form.twig", ['page' => $page, 'table' => $page->codetable, 'code' => $customer,'recordlocker' => $recordlocker]);
		$page->js   .= $config->twig->render("code-tables/mar/$page->codetable/js.twig", ['page' => $page, 'customer' => $customer]);
	} else {
		$page->title = $page->headline = "CMM";
		$recordlocker->remove_lock($page->codetable);

		$page->body .= $config->twig->render("code-tables/mar/$page->codetable/list.twig", ['page' => $page, 'table' => $page->codetable, 'codes' => $module_codetable->get_codes(), 'response' => $session->response_codetable, 'recordlocker' => $recordlocker]);
	}

//$page->body .= $config->twig->render('code-tables/edit-code-modal.twig', ['page' => $page, 'file' => "mar/$page->codetable-form.twig"]);
//$page->js .= $config->twig->render("code-tables/mar/$page->codetable.js.twig", ['page' => $page]);
