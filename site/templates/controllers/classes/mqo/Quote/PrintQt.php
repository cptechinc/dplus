<?php namespace Controllers\Mqo\Quote;

use stdClass;
// Purl URI Library
use Purl\Url as Purl;
// Propel Query
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
use QuoteQuery, Quote as QtModel;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module;
// Dplus Document Finders
use Dplus\DocManagement\Finders as DocFinders;
// Dplus Configs
use Dplus\Configs;
// Dplus Classes
use Dplus\CodeValidators\Mqo as MqoValidator;

class PrintQt extends Base {
	public static function index($data) {
		$fields = ['qnbr|text', 'download|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->qnbr)) {
			return self::invalidQt($data);
		}
		return self::quote($data);
	}

	public static function quote($data) {
		$data = self::sanitizeParametersShort($data, ['qnbr|text', 'download|text']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->quote($data->qnbr) === false) {
			return self::invalidQt($data);
		}

		if ($validate->quoteAccess($data->qnbr, self::pw('user')) === false) {
			return self::qtAccessDenied($data);
		}
		$page->headline = "Quote #$data->qnbr";

		$pdfmaker = self::pw('modules')->get('PdfMaker');
		$pdfmaker->set_fileID("quote-$data->qnbr");
		$pdfmaker->set_filetype('quote');
		if ($data->download) {
			header("Content-type:application/pdf");
			// It will be called downloaded.pdf
			header("Content-Disposition:attachment;filename=".$pdfmaker->get_filename());
			// The PDF source is in original.pdf
			readfile($config->directory_webdocs.$pdfmaker->get_filename());
		}
		if (empty($data->download) && !$page->is_pdf()) {
			$page->show_title = false;
			$pdfmaker->set_url(self::pdfUrl($data->qnbr));
			$pdfmaker->generate_pdf();
		}
		return self::print($data);
	}

	public static function print($data) {
		$data = self::sanitizeParametersShort($data, ['qnbr|text', 'download|text']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->quote($data->qnbr) === false) {
			return self::invalidQt($data);
		}
		$page->print = true;
		$page->title = "Quote #$data->qnbr";
		$quote = QuoteQuery::create()->filterByQuoteid($data->qnbr)->findOne();

		$barcoder   = self::pw('modules')->get('BarcodeMaker');
		$htmlwriter = self::pw('modules')->get('HtmlWriter');
		$html = '';

		if ($page->is_pdf() === false) {
			$html .= $config->twig->render("quotes/quote/print/print-actions.twig", ['qnbr' => $data->qnbr]);
			$html .= $htmlwriter->div('class=clearfix mb-3');
		}

		$html .= $config->twig->render("quotes/quote/print/header.twig", ['quote' => $quote, 'barcoder' => $barcoder, 'dpluscustomer' => $config->customer]);
		$html .= $htmlwriter->div('class=clearfix mb-4');
		$html .= $config->twig->render("quotes/quote/print/items.twig", ['quote' => $quote, 'configSo' => Configs\So::config()]);
		$html .= $config->twig->render("quotes/quote/print/totals.twig", ['quote' => $quote]);
		return $html;
	}

	public static function downloadPdfUrl($qnbr) {
		$url = new Purl(self::quotePrintUrl($qnbr));
		$url->query->set('download', 'pdf');
		$url->query->set('print', 'true');
		return $url->getUrl();
	}

	public static function pdfUrl($qnbr) {
		$requestor = self::pw('modules')->get('DplusRequest');
		$printurl = new Purl(self::quotePrintUrl($qnbr));
		$url = new Purl($requestor->get_self_path($printurl->path));
		$url->set('host', '127.0.0.1');
		$url->set('scheme', 'http');
		$url->query->set('qnbr', $qnbr);
		$url->query->set('print', 'true');
		$url->query->set('pdf', 'true');
		return $url->getUrl();
	}

	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMso');

		$m->addHook('Page(pw_template=quote-view)::downloadPdfUrl', function($event) {
			$event->return = self::downloadPdfUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=quote-view)::pdfUrl', function($event) {
			$event->return = self::pdfUrl($event->arguments(0));
		});
	}
}
