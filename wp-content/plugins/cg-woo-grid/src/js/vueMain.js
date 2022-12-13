import Vue from 'vue'
import Test from './templates/Test.vue';
import Matrix from './templates/Matrix.vue';
import TotalBox from './templates/TotalBox.vue';
import ItemsBar from './templates/ItemsBar.vue';
import PopUp from './templates/PopUp.vue';

import Emitter from 'tiny-emitter';

console.log('VUE Main loaded')

export var emitter = new Emitter();
export const config = {
    api: {
        index: REST_POSTS_URL
    },
    min_quantity: MINIMUM_QUANTITY,
    taxonomy_x:   TAXONOMY_X,
    taxonomy_y:   TAXONOMY_Y,

    bar_message:    BAR_MESSAGE,
    bar_success:    BAR_SUCCESS,
    bar_itemstitle: BAR_ITEMSTITLE,

    popup_message1: POPUP_MESSAGE1,
    popup_message2: POPUP_MESSAGE2,
    popup_close:    POPUP_CLOSE,

    cartPermalink: CART_URL,
    nonce:         REST_NONCE,
    loadingif:     LOADING_GIF,
};


(function($) {
    $(window).on('load', function() {
        if($("#appTest").length){
            console.log('Loading VUE Test')
            //const app = Vue.createApp(Test).mount('#appTest')
        }

        if($("#appTotalBox").length){
            console.log('Loading VUE TotalBox')
            const app = Vue.createApp(TotalBox).mount('#appTotalBox')
        }

        if($("#appMatrix").length){
            console.log('Loading VUE Matrix')
            const app = Vue.createApp(Matrix).mount('#appMatrix')
        }

        if($("#appBar").length){
            console.log('Loading VUE ItemsBar')
            const app = Vue.createApp(ItemsBar).mount('#appBar')
        }

        if($("#appPopUp").length){
            console.log('Loading VUE PopUp')
            const app = Vue.createApp(PopUp).mount('#appPopUp')
        }


    });
})( jQuery );
