
# filter_youtube_sanitizer

## Documentation

This plugin allows you to embed YouTube videos without compromising the privacy of the site visitor. Data will only be sent to Google by the browser once you actually click on a video.

* This filter only works on iframes. It is therefore recommended to also use the core "Multimedia plugins" filter which can convert YouTube links into iframes.
* Thumbnails are downloaded and cached by the Moodle server.
* URL parameters are preserved. So you could e.g. use links where the video starts playing at a certain offset.

## Configuration

If you have the "Multimedia plugins" filter and its YouTube player setting enabled you need to place this one behind Multimedia plugins in the filter sequence.

## Restrictions

* On mobile devices the autoplay may not work. You have to click the video twice to play it.
