App.Listeners.WEB.keypress_domain_name = function () {
	$('#v_domain').bind('keypress input', function () {
		clearTimeout(window.frpUserTimeout);
		window.frpUserTimeout = setTimeout(function () {
			$('#v-custom-doc-domain-main').text($('#v_domain').val());
			$('#v-custom-doc-domain-main').val($('#v_domain').val());
			App.Actions.WEB.update_custom_doc_root(13, 12);
		}, 100);
	});
};

//
// Page entry point
App.Listeners.WEB.keypress_domain_name();

$(function () {
	$('#v_domain').change(function () {
		var prefix = 'www.';
		if (document.getElementById('v_domain').value.split('.').length > 2) {
			document.getElementById('v_aliases').value = '';
		} else {
			document.getElementById('v_aliases').value =
				prefix + document.getElementById('v_domain').value;
		}
	});
});
