//vue libraries are already loaded by cg-woo-grid plugin
import Vue from 'vue'
import Test from './templates/Test.vue';

import Emitter from 'tiny-emitter';

console.log('VUE Main loaded')

export var emitter = new Emitter();
export const config = {
    api: {
        //index: REST_POSTS_URL
    },
    //nonce:         REST_NONCE,
    //loadingif:     LOADING_GIF,
};


(function($) {
    $(window).on('load', function() {
        if($("#appTest").length){
            console.log('customisations-for-cg-woo-grid: Loading VUE Test')
            const app = Vue.createApp(Test).mount('#appTest')
        }
    });
})( jQuery );
