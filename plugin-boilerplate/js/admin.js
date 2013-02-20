(function ($) {
	$(function () {

		var data = {
			action: 'verify_github_username',
			github: ''
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		$('#WP_Github_Tools_Settings\\[github\\]').blur(function(){
				data.github = $(this).val();
				$.post(ajaxurl, data, function(response) {
					response = JSON.parse(response);
					console.log(response);

					if(response.message === 'Not Found'){
						console.log('Not found');
					} else if(response.message === 'API limit reached') {
						console.log('API');
					} else {
						console.log('Found');
					}
				});
			}
		);
	});
}(jQuery));