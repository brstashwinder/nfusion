define([
    'uiComponent',
	'jquery'

], function (Component,$) {
    'use strict';

	var countDownDate;
	var updateurl = window.checkoutConfig.url;
			var now ;
			var param = 'ajax=1';
			var response = false;
				$.ajax({
					showLoader: true,
					url: updateurl,
					data: param,
					type: "POST",
					dataType: 'json'
				}).done(function (data) {
				   countDownDate = data.end;
				   now = data.now;
				  response = true;
				});
				var x = setInterval(function() {
				console.log(now);
				console.log(countDownDate);
				var distance = countDownDate - now;
				var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
				var seconds = Math.floor((distance % (1000 * 60)) / 1000);
				$("#timer").html(minutes+":"+seconds+" minutes remaining");
				if(response){
					$("#timer").show();
				}
				now = parseInt(now) + 1000;
					if (distance < 0) {
					clearInterval(x);
					$("#timer").html("EXPIRED");
					window.location.href = updateurl;
				}
				}, 1000);
				return Component.extend({
        defaults: {
            template: 'SDBullion_Nfusions/checkout/shipping/additional-block'
        }
    });
});