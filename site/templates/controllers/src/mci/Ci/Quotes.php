<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Screen Formatters
use Dplus\ScreenFormatters\Ci\Quotes as Formatter;
// Alias Document Finders
use Dplus\DocManagement\Finders as DocFinders;


class Quotes extends Subfunction {
	const PERMISSION_CIO = 'quotes';
	const JSONCODE       = 'ci-quotes';

/* =============================================================
	Indexes
============================================================= */
	public static function index($data) {
		$fields = ['custID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);

		if (self::validateCustidPermission($data) === false) {
			return self::displayInvalidCustomerOrPermissions($data);
		}

		if ($data->refresh) {
			self::requestJson($data);
			self::pw('session')->redirect(self::quotesUrl($data->custID), $http301 = false);
		}
		return self::quotes($data);
	}

	private static function quotes($data) {
		self::getData($data);
		self::pw('page')->headline = "CI: $data->custID Quotes";
		$html = '';
		$html .= self::displayBreadCrumbs($data);
		$html .= self::displayQuotes($data);
		return $html;
	}

/* =============================================================
	Data Retrieval
============================================================= */
	private static function getData($data) {
		$data    = self::sanitizeParametersShort($data, ['custID|text']);
		$jsonm   = self::getJsonModule();
		$json    = $jsonm->getFile(self::JSONCODE);
		$session = self::pw('session');


		if ($jsonm->exists(self::JSONCODE)) {
			if ($json['custid'] != $data->custID) {
				$jsonm->delete(self::JSONCODE);
				$session->redirect(self::quotesUrl($data->custID, $refresh = true), $http301 = false);
			}
			return true;
		}

		if ($session->getFor('ci', 'quotes') > 3) {
			return false;
		}
		$session->setFor('ci', 'quotes', ($session->getFor('ci', 'quotes') + 1));
		$session->redirect(self::quotesUrl($data->custID, $refresh = true), $http301 = false);
	}

/* =============================================================
	Display
============================================================= */
	protected static function displayQuotes($data) {
		$jsonm  = self::getJsonModule();
		$json   = $jsonm->getFile(self::JSONCODE);
		$config = self::pw('config');

		if ($jsonm->exists(self::JSONCODE) === false) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => 'Quotes File Not Found']);
		}

		if ($json['error']) {
			return $config->twig->render('util/alert.twig', ['type' => 'danger', 'title' => 'Error!', 'iconclass' => 'fa fa-warning fa-2x', 'message' => $json['errormsg']]);
		}
		$page = self::pw('page');
		$page->refreshurl = self::quotesUrl($data->custID, $refresh = true);
		$page->lastmodified = $jsonm->lastModified(self::JSONCODE);
		$customer = self::getCustomer($data->custID);
		$formatter = self::getFormatter();
		$docm      = self::getDocFinder();
		self::initHooks();
		return $config->twig->render('customers/ci/quotes/display.twig', ['customer' => $customer, 'json' => $json, 'module_formatter' => $formatter, 'blueprint' => $formatter->get_tableblueprint(), 'docm' => $docm]);
	}

/* =============================================================
	URLs
============================================================= */
	public static function quotesUrl($custID, $refreshdata = false) {
		$url = new Purl(self::ciQuotesUrl($custID));

		if ($refreshdata) {
			$url->query->set('refresh', 'true');
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Requests
============================================================= */
	private static function requestJson($vars) {
		$fields = ['custID|text', 'sessionID|text'];
		self::sanitizeParametersShort($vars, $fields);
		$vars->sessionID = empty($vars->sessionID) === false ? $vars->sessionID : session_id();
		$data = ['CIQUOTE', "CUSTID=$vars->custID"];
		self::sendRequest($data, $vars->sessionID);
	}

/* =============================================================
	Supplemental
============================================================= */
	private static function getFormatter() {
		$f = new Formatter();
		$f->init_formatter();
		return $f;
	}

	private static function getDocFinder() {
		return new DocFinders\Qt();
	}

/* =============================================================
	Hooks
============================================================= */
	public static function initHooks() {
		$m = self::pw('modules')->get('DpagesMci');

		$m->addHook('Page(pw_template=ci)::documentListUrl', function($event) {
			$event->return = Documents::documentsUrlQuote($event->arguments(0), $event->arguments(1));
		});
	}
}
