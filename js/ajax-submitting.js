jQuery(document).ready(function($) {
    $('#simple-click').click(function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST",
            url: submitting_ajax_object.submitting_ajax_ajaxurl,
            data: {
                // Variables defined from form
                action    : 'tie_into_php213',
                serialize : $('#ajaxform').serialize(),
                gisturl     : $('#gisturl').val(),
                // last      : $('#lname').val(),

                  // Admin stuff
                script_name   : 'ajax-submitting.js',
                ajaxurl: submitting_ajax_object.submitting_ajax_ajaxurl,
                nonce  : submitting_ajax_object.submitting_ajax_nonce,
            },
			// dataType: "json",
            success:function(data) {
	        // alert( 'we\'re adding stuff!!');
				$('#simple-msg').html( data );
                console.log( data );
            },

            error: function( jqXHR, textStatus, errorThrown ){
                console.log( errorThrown );
                $('#simple-msg').html( errorThrown );
            }
        });
    });
});
