<?php namespace Dplus\Urls\ItmImages\Sources;
// Dplus Connections
use Dplus\Connections\Api\WooCommerce;

/**
 * WooCommerce
 * Client that calls WooCommerce API to get Image URLs
 */
class WooCommerce implements Client {
	private $api;
	private static $instance;

	/**
	 * Return Instance
	 * @return self
	 */
	public static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		$this->api = WooCommerce\WooCommerce::client();
	}

	/**
	 * Returns URL to image for Item
	 * @param  string $id Item ID / SKU
	 * @return string
	 */
	public function imageUrl($id) {
		switch ($_ENV['SYSOPCODE_MAPS_TO']) {
			case 'SKU':
				return $this->api->imageUrlBySku($id);
				break;
			default:
				return $this->api->imageUrlById($id);
				break;
		}
	}

	/**
	 * Return URL to Item Image
	 * @param  string $id WooCommmerce's ID
	 * @return string
	 */
	public function imageUrlById($id) {
		$item = $this->api->item($id);
		return $item->images[0]->src;
	}

	/**
	 * Return URL to Item Image
	 * @param  string $sku SKU
	 * @return string
	 */
	public function imageUrlBySku($sku) {
		$item = $this->api->itemBySku($sku);
		return $item->images[0]->src;
	}
}
