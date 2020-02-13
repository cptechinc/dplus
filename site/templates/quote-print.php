<?php
	$modules->get('DpagesMqo')->init_mqopage();
	$html = $modules->get('HtmlWriter');

	if ($input->get->qnbr) {
		$qnbr = $input->get->text('qnbr');

		if (QuoteQuery::create()->filterByQuoteid($qnbr)->count()) {
			$page->print = true;
			$page->title = "Quote #$qnbr";
			$quote = QuoteQuery::create()->filterByQuoteid($qnbr)->findOne();

			$dpluscustomer = $pages->get('/config/customer/');
			$barcoder = $modules->get('BarcodeMaker');

			if (!$page->is_pdf()) {
				$page->pdfURL = $page->get_downloadpdfURL();
				$page->body .= $config->twig->render("quotes/quote/print/print-actions.twig", ['page' => $page]);
				$page->body .= $html->div('class=clearfix mb-3');
			}

			$page->body .= $config->twig->render("quotes/quote/print/quote-header.twig", ['page' => $page, 'quote' => $quote, 'barcoder' => $barcoder, 'dpluscustomer' => $config->customer]);

			$page->body .= $html->div('class=clearfix mb-4');
			$page->body .= $config->twig->render("quotes/quote/print/quote-items.twig", ['page' => $page, 'quote' => $quote]);
			$page->body .= $config->twig->render("quotes/quote/print/quote-totals.twig", ['page' => $page, 'quote' => $quote]);
		} else {
			$page->headline = $page->title = "Quote #$qnbr could not be found";
			$page->body = $config->twig->render('util/error-page.twig', ['msg' => "Check if the Quote Number is correct"]);
		}
	} else {
		$page->body = $config->twig->render('quotes/quote/lookup-form.twig', ['page' => $page]);
	}

	$pdfmaker = $modules->get('PdfMaker');
	$pdfmaker->set_fileID("quote-$quote->quotenumber");
	$pdfmaker->set_filetype('quote');



	if ($input->get->download) {
		header("Content-type:application/pdf");
		// It will be called downloaded.pdf
		header("Content-Disposition:attachment;filename=".$pdfmaker->get_filename());
		// The PDF source is in original.pdf
		readfile($config->directory_webdocs.$pdfmaker->get_filename());
	} elseif (!$page->is_pdf()) {
		$page->show_title = false;
		$pdfmaker->set_url($page->get_printpdfURL());
		$pdfmaker->generate_pdf();
	}

	if ($page->print) {
		$page->show_title = true;

		if ($page->is_pdf()) {
			$page->show_title = false;
		}

		include __DIR__ . "/blank-page.php";
	} else {
		include __DIR__ . "/basic-page.php";
	}
