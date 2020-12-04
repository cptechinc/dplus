<?php
	$modules->get('DpagesMqo')->init_mqopage();
	$html = $modules->get('HtmlWriter');

	if ($input->get->qnbr) {
		$qnbr = $input->get->text('qnbr');
		$module_edit = $modules->get('Eqo');
		$module_edit->set_qnbr($qnbr);

		if (QuoteQuery::create()->filterByQuoteid($qnbr)->count()) {
			if (!QuothedQuery::create()->filterBySessionidQuote(session_id(), $qnbr)->count()) {
				$modules->get('DplusRequest')->self_request($page->edit_quoteURL($qnbr));
			}
			$page->title = "Editing Quote #$qnbr";
			$quote_readonly = $module_edit->get_quote_static();
			$quote_edit     = $module_edit->get_quote_edit();
			$customer = CustomerQuery::create()->findOneByCustid($quote_readonly->custid);
			$page->listpage = $pages->get('pw_template=quotes');

			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('quotes/quote/edit/links-header.twig', ['page' => $page, 'user' => $user, 'quote' => $quote_readonly]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('quotes/quote/edit/quote-header.twig', ['page' => $page, 'customer' => $customer, 'quote' => $quote_readonly]);
			$page->body .= $html->div('class=mb-3');

			//if ($user->is_editingquote($quote_readonly->quotenumber)) {
				$page->formurl = $pages->get('template=dplus-menu, name=mqo')->child('template=redir')->url;
				$page->lookupURL = $pages->get('pw_template=ii-item-lookup')->httpUrl;
				//$page->js .= $config->twig->render('quotes/quote/edit/shiptos.js.twig', ['varshiptos' => $module_edit->get_shiptos_json_array()]);

				$page->body .= $config->twig->render('quotes/quote/edit/edit-form.twig', ['page' => $page, 'quote' => $quote_edit, 'states' => $module_edit->get_states(), 'shipvias' => $module_edit->get_shipvias(), 'warehouses' => $module_edit->get_warehouses(), 'shiptos' => $customer->get_shiptos()]);
				$page->body .= $html->div('class=mb-3');

				if ($modules->get('ConfigsCi')->option_lastsold  == 'cstk') {
					$lastsold = $modules->get('LastSoldItemsCustomerCstk');
					$lastsold->custID = $quote_readonly->custid;
					$lastsold->shiptoID = $quote_readonly->shiptoid;
					$lastsold->function = 'eqo';
					$lastsold->request_pricing();
				} else {
					$lastsold = false;
				}

				$page->body .= $config->twig->render('quotes/quote/edit/quote-items.twig', ['page' => $page, 'user' => $user, 'quote' => $quote_edit]);
				$page->body .= $html->h3('class=text-secondary', 'Add Item');
				$page->body .= $config->twig->render('quotes/quote/edit/lookup/form.twig', ['page' => $page, 'quote' => $quote_readonly]);
				$page->js .= $config->twig->render('quotes/quote/edit/lookup/js.twig', ['page' => $page, 'quote' => $quote_readonly]);

				if ($input->get->q) {
					$q = $input->get->text('q');
					$module_edit->request_itemsearch($q);
					$results = PricingQuery::create()->findBySessionid(session_id());
					$page->body .= $config->twig->render('cart/lookup-results.twig', ['q' => $q, 'results' => $results]);
				}
				$page->body .= $html->div('class=mb-3');
				$page->body .= $config->twig->render('util/js-variables.twig', ['variables' => array('shiptos' => $module_edit->get_shiptos_json_array())]);
				$config->scripts->append(hash_templatefile('scripts/quotes/edit-quote.js'));
			//}

			$page->body .= $config->twig->render("quotes/quote/quote-notes.twig", ['page' => $page, 'quote' => $quote_readonly, 'notes' => $quote_readonly->get_notes()]);
			$page->body .= $config->twig->render('quotes/quote/notes/note-modal.twig', ['page' => $page, 'qnbr' => $qnbr]);
			$page->body .= $config->twig->render('quotes/quote/edit/last-sales/modal.twig', ['page' => $page, 'qnbr' => $qnbr, 'lastsold' => $lastsold, 'loader' => $config->twigloader, 'company' => $config->company]);
			$config->scripts->append(hash_templatefile('scripts/quotes/quote-notes.js'));
			$config->scripts->append(hash_templatefile('scripts/lib/jquery-validate.js'));
		} else {
			$page->headline = $page->title = "Quote #$qnbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Quote Number is correct"]);
		}
	} else {
		$page->body = $config->twig->render('quotes/quote/lookup-form.twig', ['page' => $page]);
	}
	include __DIR__ . "/basic-page.php";
