<?php namespace Dplus\Connections\Apis\WooCommerce;
// WooCommerce API Library
use Automattic\WooCommerce\Client as ApiClient;
use Automattic\WooCommerce\HttpClient\HttpClientException;

/**
 * Client
 * Extends WooCommerce API client
 */
class Client extends ApiClient {
	/**
	 * Returns if Connnection is successful
	 * @return bool
	 */
	public function connect() {
		try {
			$this->get('products', ['per_page' => 1]);
		} catch (HttpClientException $e) {
			return false;
		}
		return true;
	}

	/**
	 * Return Item By ID
	 * @param  string $id Woo Commerce ID
	 * @return stdClass
	 */
	public function item($id) {
		return $this->get("products/$id");
	}

	/**
	 * Return Item By Sku
	 * @param  string $sku SKU
	 * @return stdClass
	 */
	public function itemBySku($sku) {
		$results = $this->get("products", ['sku' => $sku]);
		return $results[0];
	}
}
