jQuery(document).ready(function($){
	$("#simple-click").click(function(e){
		e.preventDefault();	//STOP default action
		// $("#ajaxform").submit(function(e){
			// $("#simple-msg").html("<img src='loading.gif'/>");
			var postData = $(this).serializeArray();
			// var formURL = $(this).attr("action");
			var formURL = submitting_ajax_object.submitting_ajax_ajaxurl;
			$.ajax({
				url : formURL,
				type: "POST",
				action: 'tie_into_php21',
				nonce: submitting_ajax_object.submitting_ajax_nonce,
				data : postData,
				success:function(data, textStatus, jqXHR){
					$("#simple-msg").html('<pre><code class="prettyprint">'+data+'</code></pre>');

				},
				error: function(jqXHR, textStatus, errorThrown){
					$("#simple-msg").html('<pre><code class="prettyprint">AJAX Request Failed<br/> textStatus='+textStatus+', errorThrown='+errorThrown+'</code></pre>');
				}
			});
			// e.unbind();
		});	
		// $("#ajaxform").submit(); //SUBMIT FORM
	// });
});
