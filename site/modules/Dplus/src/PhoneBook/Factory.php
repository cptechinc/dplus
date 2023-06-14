<?php namespace Dplus\PhoneBook;
// ProcessWire
use ProcessWire\WireData;


/**
 * PhoneBook
 * 
 * Class for retrieving PhoneBook classes
 */
class Factory extends WireData {
	const CLASSMAP = [
		'customer' => Customer::class,
		'c'        => Customer::class,
		'customer-contact' => CustomerContact::class,
		'cc'               => CustomerContact::class,
		'customer-shipto'  => CustomerShipto::class,
		'cs'               => CustomerShipto::class,
	];

	private static $instance;

	public static function instance() {
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Return if class exists by Key
	 * @param  string $key
	 * @return bool
	 */
	public static function exists($key) {
		return array_key_exists(strtolower($key), self::CLASSMAP);
	}

	/**
	 * Return Class
	 * @param  string $key
	 * @return WireData
	 */
	public function fetch($key) {
		if ($this->exists($key) == false) {
			return false;
		}
		$className = self::CLASSMAP[strtolower($key)];
		return $className::instance();
	}
}