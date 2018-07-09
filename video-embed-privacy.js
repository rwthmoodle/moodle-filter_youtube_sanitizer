require(['jquery'], function($) {
    $(function() {
        $(".video-wrapped").each(function() {
	
            $(this)
            .empty()
            .append($('<div class="video-wrapped-play">').html($(this).attr('data-embed-play')));
			$(this)
            .click(function(e) {
				$(this).html($(this).attr('data-embed-frame').replace(/(\/embed\/[^"]*\\?[^"]*)/, '$1?autoplay=1'));
				$(this).find('button').mousedown();
				$(this).removeAttr('style').unbind("click");
			});
        });
    });
});
