<?php
	use Twig\TwigFilter;

	$convertdate = new Twig_Function('convertdate', function ($action, $format = 'm/d/Y') {
		switch ($action->actiontype) {
			case 'note':
				$icon = "fa fa-sticky-note-o"
				break;
		
		}
		return $date == '11/30/-0001' ? '' : $date;
	});
	$config->twig->addFunction($convertdate);
