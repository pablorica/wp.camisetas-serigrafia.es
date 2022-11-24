import Vue from 'vue'
import App from './templates/Test.vue';

console.log('VUE Main loaded')


export const config = {
    api: {
        index: REST_POSTS_URL
    },
    nonce: REST_NONCE,
    loadingif: LOADING_GIF,
};


(function($) {
    $(window).on('load', function() {
        if($("#appTest").length){
            console.log('Loading VUE Test')
            const app = Vue.createApp(App).mount('#appTest')
        }
    });
})( jQuery );

