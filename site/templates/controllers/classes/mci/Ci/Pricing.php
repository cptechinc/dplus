<?php namespace Controllers\Mci\Ci;
// Purl URI Manipulation Library
use Purl\Url as Purl;
// Dplus Models
use Customer;
// ProcessWire
use ProcessWire\WireData;
// Controllers
use Controllers\Mii\Ii;
use ItemMasterItem;

class Pricing extends AbstractSubfunctionController {
	const PERMISSION_CIO = 'pricing';
	const TITLE      = 'CI: Pricing';
	const SUMMARY    = 'View Customer Pricing';
	const JSONCODE   = 'ii-pricing';
	const SUBFUNCTIONKEY = 'pricing';
	
/* =============================================================
	Indexes
============================================================= */
	public static function index(WireData $data) {
		$fields = ['rid|int', 'itemID|text', 'refresh|bool'];
		self::sanitizeParametersShort($data, $fields);
		self::throw404IfInvalidCustomerOrPermission($data);

		if (empty($data->itemID)) {
			return self::selectItem($data);
		}

		if ($data->refresh) {
			self::requestJson(self::prepareJsonRequest($data));
			self::pw('session')->redirect(self::pricingUrl($data->rid, $data->itemID), $http301 = false);
		}

		return self::pricing($data);
	}

	private static function selectItem(WireData $data) {
		$customer = self::getCustomerByRid($data->rid);

		self::pw('page')->custid   = $customer->id;
		self::pw('page')->headline = "CI: $customer->name Pricing";
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/lib/jquery-validate.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl('scripts/ajax-modal.js'));
		self::pw('config')->scripts->append(self::getFileHasher()->getHashUrl(self::jsPath() . 'select-item.js'));
		return self::displaySelectItem($data);
	}

	private static function pricing(WireData $data) {
		$json = self::fetchData($data);
		$customer = self::getCustomerByRid($data->rid);
		self::pw('page')->custid   = $customer->id;
		self::pw('page')->headline = "CI: $customer->name Pricing for $data->itemID";

		$html = '';
		$html .= self::displayPricing($data, $customer, $json);
		return $html;
	}

/* =============================================================
	Data Retrieval
============================================================= */
	/**
	 * Return URL to Fetch Data
	 * @param  WireData $data
	 * @return string
	 */
	protected static function fetchDataRedirectUrl(WireData $data) {
		return self::pricingUrl($data->rid, $data->itemID, $refresh=true);
	}

	/**
	 * Return if JSON Data matches for this Customer ID
	 * @param  WireData $data
	 * @param  array    $json
	 * @return bool
	 */
	protected static function validateJsonFileMatches(WireData $data, array $json) {
		return $json['itemid'] == $data->itemID && $json['custid'] == self::getCustidByRid($data->rid);
	}

/* =============================================================
	Display
============================================================= */
	private static function displaySelectItem(WireData $data) {
		return self::renderSelectItem($data);
	}

	protected static function displayPricing(WireData $data, Customer $customer, $json = []) {
		$jsonFetcher   = self::getJsonFileFetcher();
		if (empty($json)) {
			return self::renderJsonNotFoundAlert($data, 'Pricing');
		}

		if ($json['error']) {
			return self::renderJsonError($data, $json);
		}
		$page = self::pw('page');
		$page->refreshurl   = self::pricingUrl($data->rid, $data->itemID, $refresh=true);
		$page->lastmodified = $jsonFetcher->lastModified(self::JSONCODE);
		$item = Ii\Pricing::getItmItem($data->itemID);
		return self::renderPricing($data, $customer, $item, $json);
	}

/* =============================================================
	Render HTML
============================================================= */
	private static function renderSelectItem(WireData $data) {
		return self::pw('config')->twig->render('customers/ci/.new/pricing/select-item/display.twig');
	}

	private static function renderPricing(WireData $data, Customer $customer, ItemMasterItem $item, array $json) {
		return self::pw('config')->twig->render('customers/ci/.new/pricing/display.twig', ['item' => $item, 'customer' => $customer, 'json' => $json]);
	}
	

/* =============================================================
	URLs
============================================================= */
	public static function pricingUrl($custID, $itemID = '', $refreshdata = false) {
		$url = new Purl(self::ciPricingUrl($custID));

		if ($itemID) {
			$url->query->set('itemID', $itemID);

			if ($refreshdata) {
				$url->query->set('refresh', 'true');
			}
		}
		return $url->getUrl();
	}

/* =============================================================
	Data Requests
============================================================= */
	protected static function prepareJsonRequest(WireData $data) {
		$fields = ['rid|int', 'itemID|text', 'custID|string', 'sessionID|text'];
		self::sanitizeParametersShort($data, $fields);
		if (empty($data->custID)) {
			$data->custID = self::getCustidByRid($data->rid);
		}
		return ['CIPRICE', "ITEMID=$data->itemID", "CUSTID=$data->custID"];
	}
}
