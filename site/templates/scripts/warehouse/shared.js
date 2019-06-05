$(function() {
	function create_binrangetable() {
		var bootstrap = new JsContento();
		var table = bootstrap.open('table', 'class=table table-striped table-condensed');
			whsesession.whse.bins.bins.forEach(function(bin) {
				table += bootstrap.open('tr', '');
					table += bootstrap.openandclose('td', '', bin.from);
					table += bootstrap.openandclose('td', '', bin.through);
				table += bootstrap.close('tr');
			});
		table += bootstrap.close('table');
		return table;
	}
});
