var page  = require('webpage').create();
page.settings.userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36';
page.open('https://id.sonyentertainmentnetwork.com/signin/?response_type=code&state=mXZuHBlreMaYVbc099b4DslERE&app_context=inapp_ios&device_profile=mobile&elements_visibility=no_aclink&scope=kamaji:get_players_met%20kamaji:get_account_hash%20kamaji:activity_feed_submit_feed_story%20kamaji:activity_feed_internal_feed_submit_story%20kamaji:activity_feed_get_news_feed%20kamaji:communities%20kamaji:game_list%20kamaji:ugc:distributor%20oauth:manage_device_usercodes%20psn:sceapp%20user:account.profile.get%20user:account.attributes.validate%20user:account.settings.privacy.get%20kamaji:activity_feed_set_feed_privacy%20kamaji:satchel%20kamaji:satchel_delete%20user:account.profile.update&service_entity=urn:service-entity:psn&ui=pr&smcid=psapp%3Asettings-entrance&support_scheme=sneiprls&redirect_uri=com.playstation.PlayStationApp://redirect&device_base_font_size=10&PlatformPrivacyWs1=exempt&duid=0000000d00040080027BC1C3FBB84112BFC9A4300A78E96A&client_id=ebee17ac-99fd-487c-9b1e-18ef50c39ab5&service_logo=ps&error=login_required&error_code=4165&error_description=User+is+not+authenticated#/signin?entry=%2Fsignin', function (status) {
    if (status !== 'success') {
        console.log('Unable to load the address!');
        phantom.exit();
    } else {
        window.setTimeout(function () {
            page.switchToFrame(1);
            // var captcha = page.evaluate(function() {
            //     return document.getElementsByClassName('g-recaptcha')[0];
            // });
            console.log(page.frameContent);
            phantom.exit();
        }, 4000);
    }
});

