<?php namespace Controllers\Wm\Inventory;
// Purl Library
use Purl\Url as Purl;
// ProcessWire Classes, Modules
use ProcessWire\User;
// Stock Status Report
use Dplus\Wm\Reports\Inventory\StockStatus\Factory as Report;
// Mvc Controllers
use Controllers\Wm\Base;

class StockStatus extends Base {
	const DPLUSPERMISSION = 'wm';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['download|text'];
		self::sanitizeParametersShort($data, $fields);
		if (static::validateUserPermission() === false) {
			return static::renderUserNotPermittedAlert();
		}
		self::pw('page')->headline = "Inventory Stock Report";
		if ($data->download) {
			return self::download($data);
		}
		return self::report($data);
	}

	private static function download($data) {
		$report = new Report();

		switch ($data->download) {
			case 'xlsx':
				$file = $report->exportSpreadsheet();
				$mime = mime_content_type($file);
				header('Content-Description: File Transfer');
				header("Content-Type: $mime; charset=utf-8");
				header("Content-Disposition: attachment; filename=\"".basename($file)."\"");
				header("Content-Transfer-Encoding: binary");
				header("Expires: 0");
				header("Pragma: public");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
				header('Content-Length: ' . filesize($file)); //Remove
				readfile($file);
				exit;
				break;
		}
	}

	private static function report($data) {
		$report = new Report();
		$report->generate();
		return self::display($data, $report);
	}

/* =============================================================
	Displays
============================================================= */
	private static function display($data, Report $report) {
		$twig = self::pw('config')->twig;
		return $twig->render('warehouse/inventory/stock-status/report.twig', ['data' => $report->getReportData(), 'columns' => $report->getReporter()->getAllColumns()]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function url() {
		return self::pw('pages')->get('pw_template=whse-stock-status')->url;
	}

	public static function downloadUrl($type) {
		$url = new Purl(self::url());
		$url->query->set('download', $type);
		return $url->getUrl();
	}

/* =============================================================
	Validator, Module Getters
============================================================= */
	

/* =============================================================
	Init
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('WarehouseManagement');

		$m->addHook('Page(pw_template=whse-stock-status)::downloadUrl', function($event) {
			$event->return = self::downloadUrl($event->arguments(0));
		});
	}
}
