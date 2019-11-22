<?php namespace ProcessWire;

include_once(__DIR__.'/Configs.trait.php');

trait ConfigTraits {
	/**
	 * Sets Config Values
	 * NOTE: Will set with default value if not set correctly
	 * @return void
	 */
	public function init_configdata() {
		foreach(self::DEFAULT_CONFIG as $key => $value) {
			if (isset($this->data[$key])) {
				if (empty($this->data[$key])) {
					$this->data[$key] = $value;
				} else {
					$this->data[$key] = $this->data[$key];
				}
			} else {
				$this->data[$key] = $value;
			}
		}
	}
}
