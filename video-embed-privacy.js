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
		$('.small > a').each(function(obj) {
			console.log(obj);
			$(this).on('click', function(e) {
				if(e.bubbles == true) {
					console.log('BLA');
				}
				console.log($(this));
				console.log(e.currentTarget);
				e.preventDefault();
			});
		});
		/*$('.button-yt').each(function(e) {
			$(this).StopPropagation();
		})*/
		$(".video-wrapped").each(function(obj) {
			var oOverlay = $(obj).find('.overlay');
            $(this).empty().append($('<div class="video-wrapped-play">').html($(this).attr('data-embed-play')));
			$('.video-wrapped').click(function(e) {
				var videoWrapped = $(this);
				videoWrapped.find('.video-wrapped-play').remove();
				var videoWrapped = $(this).parents('.video-wrapped');
				$(this).append($(this).attr('data-embed-frame').replace(/(\/embed\/[^"]*\\?[^"]*)/, '$1?autoplay=1'));
				$(this).find('button').mousedown();
				$(this).removeAttr('style').unbind("click");
			});
        });
    });
});
