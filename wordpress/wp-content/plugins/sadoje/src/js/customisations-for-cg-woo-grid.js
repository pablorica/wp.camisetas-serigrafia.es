
import consoleHello from './modules/consoleHello';


(function($) {


    const CDG = {

        onreadyFunctions: function() {
            consoleHello('customisations-for-cg-woo-grid is ready');

            $(window).on('resize',function() {
                //
            });
        },

        onloadFunctions: function() {
            consoleHello('customisations-for-cg-woo-grid is loaded');
        }
    };
    
    CDG.onreadyFunctions();

    $(window).on('load', function() {
        CDG.onloadFunctions();
        
    });

})( jQuery );