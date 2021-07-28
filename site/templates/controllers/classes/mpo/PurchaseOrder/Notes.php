<?php namespace Controllers\Mpo\PurchaseOrder;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager as ModelPager;
use Propel\Runtime\Collection\ObjectCollection;
// Dplus Model
use DocumentQuery, Document;
// Dplus CodeValidators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Dplus Document Finders
use Dplus\DocManagement\Finders\PurchaseOrder as DocumentsPo;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Documents extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['ponbr|text', 'document|text', 'folder|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->ponbr)) {
			return self::lookupScreen($data);
		}

		if ($data->document && $data->folder) {
			/** @var DocumentsPo **/
			$docm = self::docm();
			$docm->moveDocument($data->folder, $data->document);
			self::pw('session')->redirect(self::pw('config')->url_webdocs.$data->document, $http301 = false);
		}
		return self::so($data);
	}

	public static function so($data) {
		self::sanitizeParametersShort($data, ['ponbr|ponbr']);
		/** @var MpoValidator **/
		$validate = self::validator();

		if ($validate->po($data->ponbr) === false && $validate->invoice($data->ponbr) === false) {
			return self::invalidSo($data);
		}
		self::pw('page')->headline = "Purchase Order #$data->ponbr Documents";

		if ($validate->invoice($data->ponbr) || $validate->po($data->ponbr)) {
			return self::documents($data);
		}
	}

	public static function documents($data) {
		self::sanitizeParametersShort($data, ['ponbr|ponbr']);
		$page = self::pw('page');
		$config   = self::pw('config');
		/** @var MpoValidator **/
		$validate = self::validator();

		if ($validate->po($data->ponbr) === false && $validate->invoice($data->ponbr) === false) {
			return self::invalidPo($data);
		}
		/** @var DocumentsPo **/
		$docm      = self::docm();
		$documents = $docm->getDocumentsPo($data->ponbr);
		return self::documentsDisplay($data, $documents);
	}

/* =============================================================
	Displays
============================================================= */
	protected static function lookupScreen($data) {
		self::pw('page')->headline = "Purchase Order Documents";
		return parent::lookupScreen($data);
	}

	private static function documentsDisplay($data, ObjectCollection $documents) {
		$html  = self::breadCrumbs();
		$html .= self::pw('config')->twig->render('purchase-orders/purchase-order/documents.twig', ['documents' => $documents, 'ponbr' => $data->ponbr]);
		return $html;
	}
}
