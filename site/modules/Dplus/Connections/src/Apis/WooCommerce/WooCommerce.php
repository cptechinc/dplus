<?php namespace Dplus\Connections\Apis\WooCommerce;

/**
 * WooCommerce
 * Class to Return API Client
 */
class WooCommerce {
	const VERSION = 'wc/v3';

	const CLIENTOPTIONS = [
		'wp_api' => true,
		'version' => self::VERSION,
		'query_string_auth' => true  //USE if https:
	];

	private static $client;

	/**
	 * Return Client for API
	 * @return Client
	 */
	public static function client() {
		if (empty(self::$client)) {
			self::$client = new Client(
				$_ENV['WOOCOMMERCE_URL'],
				$_ENV['WOOCOMMERCE_API_KEY'],
				$_ENV['WOOCOMMERCE_API_SECRET'],
				self::CLIENTOPTIONS
			);
		}
		return self::$client;
	}
}
