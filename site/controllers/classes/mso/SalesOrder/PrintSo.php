<?php namespace Controllers\Mso\SalesOrder;
// Purl URI Library
use Purl\Url as Purl;
// Dplus Model
use SalesOrderQuery, SalesHistoryQuery, CustomerQuery;
// Dplus Validators
use Dplus\CodeValidators\Mso as MsoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class PrintSo extends Base {

	public static function index($data) {
		$fields = ['ordn|text', 'download|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->ordn)) {
			return self::invalidSo($data);
		}
		return self::so($data);
	}

	public static function so($data) {
		$data = self::sanitizeParametersShort($data, ['ordn|ordn', 'download|text']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}

		if ($validate->orderAccess($data->ordn, self::pw('user')) === false) {
			return self::soAccessDenied($data);
		}
		$page->headline = "Sales Order #$data->ordn";

		if ($validate->invoice($data->ordn) || $validate->order($data->ordn)) {
			$pdfmaker = self::pw('modules')->get('PdfMaker');
			$pdfmaker->set_fileID("order-$data->ordn");
			$pdfmaker->set_filetype('order');
			if ($data->download) {
				header("Content-type:application/pdf");
				// It will be called downloaded.pdf
				header("Content-Disposition:attachment;filename=".$pdfmaker->get_filename());
				// The PDF source is in original.pdf
				readfile($config->directory_webdocs.$pdfmaker->get_filename());
			}
			if (empty($data->download) && !$page->is_pdf()) {
				$page->show_title = false;
				$pdfmaker->set_url($page->orderPdfUrl($data->ordn));
				$pdfmaker->generate_pdf();
			}
			return self::print($data);
		}
	}

	public static function print($data) {
		$data = self::sanitizeParametersShort($data, ['ordn|ordn', 'download|text']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}
		$page->print = true;

		$session = self::pw('session');
		$html = '';

		$type = 'order';
		$order = SalesOrderQuery::create()->findOneByOrdernumber($data->ordn);

		if ($validate->invoice($data->ordn)) {
			$type = 'history';
			$order = SalesHistoryQuery::create()->findOneByOrdernumber($data->ordn);
		}
		$customer = CustomerQuery::create()->findOneByCustid($order->custid);

		$barcoder      = self::pw('modules')->get('BarcodeMaker');
		$dpluscustomer = self::pw('pages')->get('/config/customer/');

		$twig = [
			'header' => $config->twig->render("sales-orders/sales-$type/print/header.twig", ['customer' => $customer, 'order' => $order, 'dpluscustomer' => $dpluscustomer, 'barcoder' => $barcoder]),
			'items'  => '',
			'totals' => $config->twig->render("sales-orders/sales-$type/print/totals.twig", ['order' => $order])
		];

		if ($config->twigloader->exists("sales-orders/sales-$type/print/$config->company/items.twig")) {
			$twig['items'] = $config->twig->render("sales-orders/sales-$type/print/$config->company/items.twig", ['config' => self::configSo(), 'order' => $order]);
		} else {
			$twig['items'] = $config->twig->render("sales-orders/sales-$type/print/items.twig", ['config' => self::configSo(), 'order' => $order]);
		}

		$html = $config->twig->render("sales-orders/sales-order/print/display.twig", ['html' => $twig, 'ordn' => $data->ordn]);

		return $html;
	}

	public static function orderDownloadPdfUrl($ordn) {
		$url = new Purl(self::orderPrintUrl($ordn));
		$url->query->set('download', 'pdf');
		$url->query->set('print', 'true');
		return $url->getUrl();
	}

	public static function orderPdfUrl($ordn) {
		$requestor = self::pw('modules')->get('DplusRequest');
		$printurl = new Purl(self::orderPrintUrl($ordn));
		$url = new Purl($requestor->get_self_path($printurl->path));
		$url->set('host', '127.0.0.1');
		$url->set('scheme', 'http');
		$url->query->set('ordn', $ordn);
		$url->query->set('print', 'true');
		$url->query->set('pdf', 'true');
		return $url->getUrl();
	}

	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMso');

		$m->addHook('Page(pw_template=sales-order-view)::orderDownloadPdfUrl', function($event) {
			$event->return = self::orderDownloadPdfUrl($event->arguments(0));
		});

		$m->addHook('Page(pw_template=sales-order-view)::orderPdfUrl', function($event) {
			$event->return = self::orderPdfUrl($event->arguments(0));
		});
	}
}
