<?php namespace Controllers\Mpo\ApInvoice;
// Propel ORM Library
use Propel\Runtime\Util\PropelModelPager as ModelPager;
use Propel\Runtime\Collection\ObjectCollection;
// Dplus Model
use DocumentQuery, Document;
// Dplus CodeValidators
use Dplus\CodeValidators\Mpo as MpoValidator;
// Dplus Document Finders
use Dplus\DocManagement\Finders\ApInvoice as Docm;
use Dplus\DocManagement\Copier;
// Mvc Controllers
use Mvc\Controllers\AbstractController;

class Documents extends Base {
/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['invnbr|text', 'document|text', 'folder|text'];
		self::sanitizeParametersShort($data, $fields);

		if (empty($data->invnbr)) {
			return self::lookupScreen($data);
		}

		if ($data->document && $data->folder) {
			/** @var Docm **/
			$docm = self::docm();
			$file = $docm->getDocumentByFilename($data->folder, $data->document);
			$copier = Copier::getInstance();
			$copier->copyFile($file->getDocumentFolder()->directory, $data->document);

			if ($copier->isInDirectory($data->document)) {
				self::pw('session')->redirect(self::pw('config')->url_webdocs.$data->document, $http301 = false);
			}
		}
		return self::invoice($data);
	}

	public static function invoice($data) {
		self::sanitizeParametersShort($data, ['invnbr|invnbr']);
		/** @var MpoValidator **/
		$validate = self::validator();

		if ($validate->invoice($data->invnbr) === false && $validate->invoice($data->invnbr) === false) {
			return self::invalidSo($data);
		}
		self::pw('page')->headline = "Invoice #$data->invnbr Documents";

		if ($validate->invoice($data->invnbr) || $validate->invoice($data->invnbr)) {
			return self::documents($data);
		}
	}

	public static function documents($data) {
		self::sanitizeParametersShort($data, ['invnbr|text']);
		$page = self::pw('page');
		$config   = self::pw('config');
		/** @var MpoValidator **/
		$validate = self::validator();

		if ($validate->invoice($data->invnbr) === false && $validate->invoice($data->invnbr) === false) {
			return self::invalidPo($data);
		}
		/** @var Docm **/
		$docm      = self::docm();
		$documents = $docm->getDocm($data->invnbr);
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
		$html .= self::pw('config')->twig->render('purchase-orders/purchase-order/documents.twig', ['documents' => $documents, 'invnbr' => $data->invnbr]);
		return $html;
	}
}
