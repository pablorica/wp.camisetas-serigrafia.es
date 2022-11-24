import Vue from 'vue'
import App from './templates/Test.vue';
import Matrix from './templates/Matrix.vue';
import Emitter from 'tiny-emitter';

console.log('VUE Main loaded')

export var emitter = new Emitter();
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

        if($("#appMatrix").length){
            console.log('Loading VUE Matrix')
            const app = Vue.createApp(Matrix).mount('#appMatrix')
        }


    });
})( jQuery );
