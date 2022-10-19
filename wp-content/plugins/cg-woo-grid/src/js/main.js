import sayHello from './modules/sayHello';
import consoleHello from './modules/consoleHello';


(function($) {

    $.isMobile = function(type) {
        var reg = [];
        var any = {
            blackberry: 'BlackBerry',
            android: 'Android',
            windows: 'IEMobile',
            opera: 'Opera Mini',
            ios: 'iPhone|iPad|iPod'
        };
        console.log(navigator.userAgent)
        type = 'undefined' === typeof type ? '*' : type.toLowerCase();
        if ('*' == type) reg = $.map(any, function(v) {
            return v;
        });
        else if (type in any) reg.push(any[type]);
        
        if (isIOS()) {
            return true;
        }

        return !!(reg.length && navigator.userAgent.match(new RegExp(reg.join('|'), 'i')));

        function isIOS() {
            console.log(navigator.platform)
            console.log(navigator.maxTouchPoints)
            if (/iPad|iPhone|iPod/.test(navigator.platform)) {
              return true;
            } else {
              return navigator.maxTouchPoints &&
                navigator.maxTouchPoints > 2 &&
                /MacIntel/.test(navigator.platform);
            }
        }
    };

    $.isSafari = function() {
        return /^((?!chrome|android).)*safari/i.test(navigator.userAgent);
    }


    const CDG = {

        onreadyFunctions: function() {
            //sayHello('JS is ready');
            consoleHello('cg-woo-grid is ready');

            $(window).on('resize',function() {
                //CDG.addMSGonResize();
            });
        },

        onloadFunctions: function() {
            //CDG.addMSGonLoad();
            consoleHello('cg-woo-grid is loaded');
        }
    };
    
    CDG.onreadyFunctions();

    $(window).on('load', function() {
        CDG.onloadFunctions();
    });

})( jQuery );