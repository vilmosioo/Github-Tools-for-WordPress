(function ($) {
	$(function () {

		var data = {
			action: 'verify_github_username',
			github: ''
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$('#WP_Github_Tools_Settings\\[github\\]').blur(function(){
				data.github = $(this).val();
				$('#github-tools-feedback').hide();
				$(".github-tools-image").hide();
				$('#github-tools-information-bar').hide();
				$("#github-tools-loading").show();
				$.post(ajaxurl, data, function(response) {
					response = JSON.parse(response);
					$("#github-tools-loading").hide();
					if(response.message === 'Not Found'){
						$('#github-tools-feedback').html('<strong>Error: </strong>Your github profile was not found. Are you sure the username is correct?').show();
						$("#github-tools-no").show();
					} else if(response.message === 'API limit reached') {
						$('#github-tools-information-bar').html("<p><strong>Error: </strong>API limit reached. Please try again after 60 minutes max.</p>").show();
					} else {
						$('#github-tools-feedback').text('Valid!').show();
						$("#github-tools-yes").show();
					}
				});
			}
		);
	});
}(jQuery));