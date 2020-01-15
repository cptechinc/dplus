<?php
	function format_currency($str) {
		return number_format($str, 2, '.', ",");
	}

	/**
	 * Returns URL with hash for JS / CSS Caches
	 * @param  string $file     From templates\
	 * @return string           URL with v?={filehash}
	 */
	function hash_templatefile($file) {
		$basefile = remove_templatefilepath($file);
		$filepath = get_templatefilepath($basefile);
		$hash = get_filehash($filepath);
		return Processwire\wire('config')->urls->templates."$basefile?v=$hash";
	}

	function remove_templatefilepath($file) {
		return str_replace(Processwire\wire('config')->paths->templates, '', $file);
	}

	function get_templatefilepath($file) {
		return Processwire\wire('config')->paths->templates . $file;
	}

	function get_filehash($file) {
		return hash_file(Processwire\wire('config')->userAuthHashType, $file);
	}

	/**
	 * Writes an array one datem per line into the dplus directory
	 * @param  array $data      Array of Lines for the request
	 * @param  string $filename What to name File
	 * @return void
	 */
	function write_dplusfile($data, $filename) {
		$file = '';
		foreach ($data as $line) {
			$file .= $line . "\n";
		}
		$vard = "/usr/capsys/ecomm/" . $filename;
		$handle = fopen($vard, "w") or die("cant open file");
		fwrite($handle, $file);
		fclose($handle);
	}
