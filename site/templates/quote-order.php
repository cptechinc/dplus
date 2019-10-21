<?php
	$modules->get('DpagesMqo')->init_mqopage();
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

			$page->title = "Push Quote #$qnbr to Order";
			$quote_readonly = $module_edit->get_quote_static();
			$quote_edit     =  $module_edit->get_quote_edit();
			$customer = CustomerQuery::create()->findOneByCustid($quote_readonly->custid);
			$page->listpage = $pages->get('pw_template=quotes');
			$page->formurl = $pages->get('template=dplus-menu, name=mqo')->child('template=redir')->url;

			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('quotes/quote/order/links-header.twig', ['page' => $page, 'quote' => $quote_readonly]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('quotes/quote/edit/quote-header.twig', ['page' => $page, 'customer' => $customer, 'quote' => $quote_readonly]);
			$page->body .= $html->div('class=mb-3');
			$page->body .= $config->twig->render('quotes/quote/order/quote-items.twig', ['page' => $page, 'quote' => $quote_edit]);
			$config->scripts->append(hash_templatefile('scripts/quotes/order-quote.js'));
		} else {
			$page->headline = $page->title = "Quote #$qnbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Quote Number is correct"]);
		}
	} else {
		$page->body = $config->twig->render('quotes/quote/lookup-form.twig', ['page' => $page]);
	}
	include __DIR__ . "/basic-page.php";
