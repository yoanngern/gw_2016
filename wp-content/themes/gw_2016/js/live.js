$(document).ready(function () {


    var videoId = $("#fbinsert").data("video");

    var appId = "1160743764014114";

    var pageId = "422383907852542";


    $.ajaxSetup({cache: true});
    $.getScript('//connect.facebook.net/fr_CH/all.js', function () {
        FB.init({
            appId: appId,
            version: 'v2.5' // or v2.0, v2.1, v2.2, v2.3
        });

        FB.api('/' + videoId, function (response) {

            console.log(response);

            var iframe = '<iframe width="100%" height="auto" src="https://www.facebook.com/video/embed?video_id=' + response.id + '&autoplay=true&allowfullscreen=true"></iframe>';

            $("#fbinsert").html(iframe);
        });


        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.5&appId=" + appId;
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));


        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.8&appId=" + appId;
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v2.8&appId=" + appId;
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

    });
});


