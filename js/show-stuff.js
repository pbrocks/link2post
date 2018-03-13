jQuery(document).ready(function($){
 //    $(".sidebar-reference").click(function(){
	// 	$(".open").toggle();
	// });
	$("#sidebar-trigger").click(function(){
		$(".sidebar-reference").toggleClass("open");
		$("#sidebar-reference").toggleClass("open");
	});
});
