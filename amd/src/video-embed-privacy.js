define(['jquery'], function($) {
    "use strict";
	return {
		init: function() {
			// $(function() {
				var applyVideoWrapper = function() {
					$(".video-wrapped").each(function() {
						$(this).empty();
						$(this).append($('<div class="video-wrapped-play">').html($(this).attr('data-embed-play')));
						$(this).on('click mousedown', function(ev) {
							var videoWrapped = $(this);
							videoWrapped.find('.video-wrapped-play').remove();
							var videoWrapped = $(this).parents('.video-wrapped');
							$(this).append($(this).attr('data-embed-frame').replace(/(\/embed\/[^"]*\\?[^"]*)/, '$1&autoplay=1'));
							$(this).find('button').mousedown();
							$(this).removeAttr('style').off("click");
						});
					});
					preventBubbling();
				}
				var preventBubbling = function() {
					$('.yt-link-wrapper a, yt-link-wrapper span').each(function(index) {
						$(this).on('click mousedown', function(event) {
							event.stopPropagation();
						});
					});
				}
				applyVideoWrapper();
			// });
		}


	}
});
