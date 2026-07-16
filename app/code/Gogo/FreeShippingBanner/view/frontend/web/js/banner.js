define(['jquery', 'mage/cookies', 'domReady!'], function ($) {
    'use strict';

    return {
        init: function (config) {
            var $banner = $('#gogo-fsb-wrapper'),
                $close = $banner.find('.gogo-fsb-close'),
                cookieName = config.cookieName,
                cookieLifetime = config.cookieLifetime,
                storageKey = config.storageKey,
                storageTimestampKey = config.storageTimestampKey;

            if (!$banner.length || !config.isEnabled) {
                return;
            }

            if (this.isDismissed(cookieName, storageKey, storageTimestampKey, cookieLifetime)) {
                $banner.remove();
                return;
            }

            if ($close.length) {
                $close.on('click', function () {
                    $banner.fadeOut(300, function () {
                        $banner.remove();
                        $.mage.cookies.set(cookieName, '1', {
                            lifetime: cookieLifetime
                        });
                        try {
                            localStorage.setItem(storageKey, '1');
                            localStorage.setItem(storageTimestampKey, String(Date.now()));
                        } catch (e) {
                            // localStorage may be disabled
                        }
                    });
                });
            }
        },

        isDismissed: function (cookieName, storageKey, storageTimestampKey, cookieLifetime) {
            if ($.mage.cookies.get(cookieName) === '1') {
                return true;
            }

            try {
                if (localStorage.getItem(storageKey) !== '1') {
                    return false;
                }

                var closedAt = parseInt(localStorage.getItem(storageTimestampKey), 10);

                if (!closedAt || isNaN(closedAt)) {
                    return true;
                }

                return Date.now() - closedAt < cookieLifetime * 1000;
            } catch (e) {
                return false;
            }
        }
    };
});
