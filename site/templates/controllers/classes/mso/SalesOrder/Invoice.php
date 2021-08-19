<?php namespace Controllers\Mso\SalesOrder;

use stdClass;
// Purl URI Library
use Purl\Url as Purl;
// Propel Query
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dplus Model
use SalesHistoryQuery, SalesHistory;
// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;
// Dplus Code Validators
use Dplus\CodeValidators\Mso as MsoValidator;
// Mvc Controllers
use Controllers\Mso\SalesOrder\SalesOrder;
use Controllers\Mii\Ii;
use Controllers\Mci\Ci\Ci;


class Invoice extends SalesOrder {

/* =============================================================
	Displays
============================================================= */
	public static function invoice($data) {
		self::sanitizeParametersShort($data, ['ordn|ordn']);

		if (self::validator()->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}
		$page   = self::pw('page');
		$config = self::pw('config');
		$order = SalesHistoryQuery::create()->findOneByOrdernumber($data->ordn);
		$docm = self::docm();
		$twig = [
			'header' => $config->twig->render("sales-orders/sales-history/header-display.twig", ['config' => self::configSo(), 'order' => $order, 'docm' => $docm]),
			'items'  => self::itemsDisplay($order, $data)
		];
		$twig = self::_orderDetails($order, $data, $twig);
		$html = $config->twig->render("sales-orders/sales-history/page.twig", ['html' => $twig, 'order' => $order]);
		return $html;
	}

	protected static function itemsDisplay(ActiveRecordInterface $order, $data) {
		$config = self::pw('config');
		$twigloader = $config->twig->getLoader();

		if ($twigloader->exists("sales-orders/sales-history/$config->company/items.twig")) {
			return $config->twig->render("sales-orders/sales-history/$config->company/items.twig", ['config' => self::configSo(), 'order' => $order]);
		}
		return $config->twig->render("sales-orders/sales-history/items.twig", ['config' => self::configSo(), 'order' => $order]);
	}

	protected static function qnotesDisplay($data) {
		$qnotes = self::pw('modules')->get('QnotesSalesHistory');
		return self::pw('config')->twig->render('sales-orders/sales-order/qnotes.twig', ['qnotes_so' => $qnotes, 'ordn' => $data->ordn]);
	}
}
