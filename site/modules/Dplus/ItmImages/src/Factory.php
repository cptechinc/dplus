<?php namespace Dplus\Urls\ItmImages;
// ProcessWire
use ProcessWire\WireData;
// Dplus Itm Images URLs
use Dplus\Urls\ItmImages\Sources\Client;
use Dplus\Urls\ItmImages\Itm;

/**
 * Factory
 */
class Factory extends WireData {
	/**
	 * Returns Image Url
	 * @param  string $itemID Item ID
	 * @return string
	 */
	public function imageUrl($itemID) {
		if (Itm\OptionalCodes::exists($itemID) === false) {
			return '';
		}
		$id = Itm\OptionalCodes::code($itemID, $_ENV['SYSOPCODE']);
		return $this->getClient()->imageUrl($id);
	}

	/**
	 * Return Client
	 * @return Client
	 */
	public function getClient() {
		$class = $this->getClientClassname();
		return $class::getInstance();
	}

	/**
	 * Return Class Name
	 * @return string
	 */
	public function getClientClassname() {
		$ns = __NAMESPACE__ . '\\Sources\\';
		$class = $ns . $_ENV['SOURCE'];
		return $class;
	}
}
