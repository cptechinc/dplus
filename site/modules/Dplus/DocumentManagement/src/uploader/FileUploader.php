<?php namespace Dplus\DocManagement;

use ProcessWire\WireUpload;

class FileUploader extends WireUpload {
	/**
	 * Sanitize/validate a given filename
	 *
	 * @param string $value Filename
	 * @param array $extensions Allowed file extensions
	 * @return bool|string Returns boolean false if invalid or string of potentially modified filename if valid
	 *
	 */
	public function validateFilename($value, $extensions = array()) {
		$value = basename($value);
		if($value[0] == '.') return false; // no hidden files
		if($this->lowercase) $value = function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);

		$value = trim($value, "_");
		if(!strlen($value)) return false;

		$p = pathinfo($value);

		if(!isset($p['extension'])) return false;

		$extension = strtolower($p['extension']);

		$basename = basename($p['basename'], ".$extension");

		// replace any dots in the basename with underscores
		$basename = trim(str_replace(".", "_", $basename), "_");
		$value = "$basename.$extension";

		if(count($extensions)) {
			if(!in_array($extension, $extensions)) $value = false;
		}
		return $value;
	}
}
