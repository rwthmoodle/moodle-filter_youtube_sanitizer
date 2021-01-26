define(['jquery'], function($) {
    "use strict";
    return {
        init: function() {
            $(".yt-player").each(function() {
                $(this).one('click', function() {
                    $(this).empty();
                    $(this).removeAttr('style');
                    $(this).append($(this).data('embed-frame'));
                });
                $(this).find('.yt-privacynote').click(function(e) {
                    e.stopPropagation();
                });
            });
        }
    };
});
