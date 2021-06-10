<?php namespace Controllers\Mso\SalesOrder;

// Dplus Classes
use Dplus\CodeValidators\Mso as MsoValidator;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Documents extends Base {

	public static function index($data) {
		$fields = ['ordn|text', 'document|text', 'folder|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->ordn)) {
			return self::invalidSo($data);
		}

		if ($data->document && $data->folder) {
			$docm = self::docm();
			$docm->moveDocument($data->folder, $data->document);
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
		$documents = $docm->getDocuments($data->ordn);
		$html      = $config->twig->render('sales-orders/sales-order/documents.twig', ['documents' => $documents]);
		return $html;
	}
}
