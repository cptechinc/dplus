<?php namespace Controllers\Mso\SalesOrder;

use stdClass;
// Propel Query
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;

// ProcessWire Classes, Modules
use ProcessWire\Page, ProcessWire\Module;
// Dplus Classes
use Dplus\CodeValidators\Mso as MsoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Documents extends AbstractController {
	static $validate;
	static $docm;
	static $configSo;

	public static function index($data) {
		$fields = ['ordn|text', 'document|text', 'folder|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->ordn)) {
			return self::invalidSo($data);
		}

		if ($data->document && $data->folder) {
			$docm->move_document($data->folder, $data->document);
			self::pw('session')->redirect(self::pw('config')->url_webdocs.$data->document, $http301 = false);
		}
		return self::so($data);
	}

	public static function so($data) {
		$data = self::sanitizeParametersShort($data, ['ordn|ordn']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}

		if ($validate->orderAccess($data->ordn, self::pw('user')) === false) {
			return self::soAccessDenied($data);
		}
		$page->headline = "Sales Order #$data->ordn Documents";

		if ($validate->invoice($data->ordn) || $validate->order($data->ordn)) {
			return self::documents($data);
		}
	}

	public static function documents($data) {
		$data = self::sanitizeParametersShort($data, ['ordn|ordn']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->order($data->ordn) === false && $validate->invoice($data->ordn) === false) {
			return self::invalidSo($data);
		}
		$docm      = self::docm();
		$documents = $docm->get_documents($data->ordn);
		$html      = $config->twig->render('sales-orders/sales-order/documents.twig', ['documents' => $documents]);
		return $html;
	}


	private static function invalidSo($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Sales Order #$data->ordn not found";
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Sales Order Not Found', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "Order # $data->ordn can not be found"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::lookupForm();
		return $html;
	}

	private static function soAccessDenied($data) {
		$page   = self::pw('page');
		$config = self::pw('config');
		$page->headline = "Access Denied";
		$html = $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Sales Order Access Denied', 'iconclass' => 'fa fa-warning fa-2x', 'message' => "You don't have access to Order # $data->ordn"]);
		$html .= '<div class="mb-3"></div>';
		$html .= self::lookupForm();
		return $html;
	}

	private static function lookupForm() {
		$config = self::pw('config');
		$html = $config->twig->render('sales-orders/sales-order/lookup-form.twig');
		return $html;
	}

	private static function validator() {
		if (empty(self::$validate)) {
			self::$validate = new MsoValidator();
		}
		return self::$validate;
	}

	private static function docm() {
		if (empty(self::$docm)) {
			self::$docm = self::pw('modules')->get('DocumentManagementSo');
		}
		return self::$docm;
	}
}
