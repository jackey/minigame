(function ($)){
	$(document).ready(function () {
		function is_valid_email(email, callback) {
			var url = "/validation/is_valid_email";
			$.ajax({
				url: url,
				data: {
					email: email
				},
				type:"POST",
				success: function (data) {
					data = $.parseJSON(data);
					if (data.success == 1) {
						callback(true);
					}
					else {
						callback(false, data.message);
					}
				}
			});
		}
	});
}(jQuery);