require(['jquery'], function($) {
    $(function() {
        /*$(".video-wrapped").each(function() {
            $(this).empty().append($('<div class="video-wrapped-play">').html($(this).attr('data-embed-play')));
			$(this).click(function(e) {
				$(this).html($(this).attr('data-embed-frame').replace(/(\/embed\/[^"]*\\?[^"]*)/, '$1?autoplay=1'));
				$(this).find('button').mousedown();
				$(this).removeAttr('style').unbind("click");
			});
        });*/
		function applyVideoWrapper() {

			$(".video-wrapped").each(function(obj) {
				$(this).empty().append($('<div class="video-wrapped-play">').html($(this).attr('data-embed-play')));
				$(this).click(function() {
					var videoWrapped = $(this);
					videoWrapped.find('.video-wrapped-play').remove();
					var videoWrapped = $(this).parents('.video-wrapped');

					$(this).append($(this).attr('data-embed-frame').replace(/(\/embed\/[^"]*\\?[^"]*)/, '$1?autoplay=1'));
					$(this).find('button').mousedown();
					$(this).removeAttr('style').unbind("click");
				});
			});
			preventBubbling();
		}
		function preventBubbling() {
			$('.yt-link-wrapper a, yt-link-wrapper span').each(function(index) {
				console.log(index, ':  ', $(this));
				console.log($(this));
				$(this).click(function(event) {
					event.stopPropagation();
					console.log($(event));
				});
			});
		}

		applyVideoWrapper();
		/*$('.button-yt').each(function(e) {
			$(this).StopPropagation();
		})*/
    });
});
