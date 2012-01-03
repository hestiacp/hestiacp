$(document).ready(function(){

var area = $('.backups-list .detailed-restore-url');


area.hover(
	function() {
		$(this).prev().hide();
	}, 
	function() {
		$(this).prev().show();
	});
});