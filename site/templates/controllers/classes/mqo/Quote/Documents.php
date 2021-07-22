<?php namespace Controllers\Mqo\Quote;


class Documents extends Base {

	public static function index($data) {
		$fields = ['qnbr|text', 'document|text', 'folder|text'];
		$data = self::sanitizeParametersShort($data, $fields);

		if (empty($data->qnbr)) {
			return self::lookupScreen($data);
		}

		if ($data->document && $data->folder) {
			$docm = self::docm();
			$docm->moveDocument($data->folder, $data->document);
			self::pw('session')->redirect(self::pw('config')->url_webdocs.$data->document, $http301 = false);
		}
		return self::qt($data);
	}

	public static function qt($data) {
		$data = self::sanitizeParametersShort($data, ['qnbr|qnbr']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->quote($data->qnbr) === false) {
			return self::invalidQt($data);
		}

		if ($validate->quoteAccess($data->qnbr, self::pw('user')) === false) {
			return self::qtAccessDenied($data);
		}

		$page->headline = "Quote #$data->qnbr Documents";
		return self::documents($data);
	}

	public static function documents($data) {
		$data = self::sanitizeParametersShort($data, ['qnbr|qnbr']);
		$page = self::pw('page');
		$config   = self::pw('config');
		$validate = self::validator();

		if ($validate->quote($data->qnbr) === false) {
			return self::invalidQt($data);
		}
		$docm      = self::docm();
		$documents = $docm->getDocuments($data->qnbr);
		$html      = $config->twig->render('quotes/quote/quote-documents.twig', ['documents' => $documents]);
		return $html;
	}
}
