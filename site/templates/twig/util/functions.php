<?php
	use Twig\TwigFilter;

	$convertdate = new Twig_Function('convertdate', function ($date, $format = 'm/d/Y') {
		$date = date($format, strtotime($date));
		return $date == '11/30/-0001' ? '' : $date;
	});
	$config->twig->addFunction($convertdate);

	$yesno = new Twig_Function('yesorno', function ($trueorfalse) {
		return ($trueorfalse === true || strtoupper($trueorfalse) == 'Y') ? 'yes' : 'no';
	});
	$config->twig->addFunction($yesno);

	$filter = new TwigFilter('currency', function ($money) {
		return number_format($money, 2, '.', ",");
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('attrJS', function ($string) {
		return "js-$string";
	});
	$config->twig->addFilter($filter);

	$filter = new Twig_Filter('array_key_exists', function ($array, $key) {
		return array_key_exists($key, $array);
	});
	$config->twig->addFilter($filter);
