<?php
	$modules->get('MqoPages')->init_mqopage();
	$html = $modules->get('HtmlWriter');

	if ($input->get->qnbr) {
		$qnbr = $input->get->text('qnbr');
		$module_edit = $modules->get('QuoteEdit');
		$module_edit->set_qnbr($qnbr);

		if (QuoteQuery::create()->filterByQuoteid($qnbr)->count()) {
			if (!QuothedQuery::create()->filterBySessionidQuote(session_id(), $qnbr)->count()) {
				$http = new ProcessWire\WireHttp();
				$http->get($page->edit_quoteURL($qnbr));
			}
			$page->title = "Editing Quote #$qnbr";
			$quote_readonly = QuoteQuery::create()->filterByQuoteid($qnbr)->findOne();
			$quote_edit =  QuothedQuery::create()->findOneBySessionidQuote(session_id(), $qnbr);
			$customer = CustomerQuery::create()->findOneByCustid($quote_readonly->custid);
			$page->listpage = $pages->get('pw_template=quotes');

			$page->body .= $config->twig->render('util/alert.twig', ['type' => 'warning', 'title' => "Attention!", 'iconclass' => 'fa fa-exclamation-circle fa-2x', 'message' => "The changes made will not be saved, because the quote is not locked."]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('quotes/quote/edit/links-header.twig', ['page' => $page, 'user' => $user, 'quote' => $quote_readonly]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('quotes/quote/edit/quote-header.twig', ['page' => $page, 'customer' => $customer, 'quote' => $quote_readonly]);
			$page->body .= $html->div('class=mb-3');

			if ($user->is_editingquote($quote_readonly->quotenumber)) {
				$page->formurl = $pages->get('template=dplus-menu, name=mqo')->child('template=redir')->url;
				$page->js .= $config->twig->render('quotes/quote/edit/shiptos.js.twig', ['varshiptos' => $module_edit->get_shiptos_json_array()]);
				$page->body .= $config->twig->render('quotes/quote/edit/edit-form.twig', ['page' => $page, 'quote' => $quote_edit, 'states' => $module_edit->get_states(), 'shipvias' => $module_edit->get_shipvias(), 'warehouses' => $module_edit->get_warehouses(), 'shiptos' => $customer->get_shiptos()]);
				$page->body .= $html->div('class=mb-3');

				$config->scripts->append(hash_templatefile('scripts/quotes/edit-quote.js'));
			}
			$page->body .= $config->twig->render('quotes/quote/edit/quote-items.twig', ['page' => $page, 'user' => $user, 'quote' => $quote_edit]);


			$page->body .= $config->twig->render("quotes/quote/quote-notes.twig", ['page' => $page, 'quote' => $quote_readonly, 'notes' => $quote_readonly->get_notes()]);
			$page->body .= $config->twig->render('quotes/quote/notes/add-note-modal.twig', ['page' => $page, 'qnbr' => $qnbr]);
			$config->scripts->append(hash_templatefile('scripts/quotes/quote-notes.js'));
		} else {
			$page->headline = $page->title = "Quote #$qnbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Quote Number is correct"]);
		}
	} else {
		$page->body = $config->twig->render('quotes/quote/lookup-form.twig', ['page' => $page]);
	}
	include __DIR__ . "/basic-page.php";
