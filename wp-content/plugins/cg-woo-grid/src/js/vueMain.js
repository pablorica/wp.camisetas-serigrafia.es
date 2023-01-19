import Vue from 'vue'
//import Test from './templates/Test.vue';
import Matrix from './templates/Matrix.vue';
import TotalBox from './templates/TotalBox.vue';
import ItemsBar from './templates/ItemsBar.vue';
import PopUp from './templates/PopUp.vue';

import Emitter from 'tiny-emitter';

//console.log('VUE Main loaded')


export var emitter = new Emitter();

export const config = {
    api: {
        index: REST_POSTS_URL
    },
    taxonomy_x: TAXONOMY_X,
    taxonomy_y: TAXONOMY_Y,

    min_quantity:   MINIMUM_QUANTITY,
    bar_message:    BAR_MESSAGE,
    bar_success:    BAR_SUCCESS,
    bar_itemstitle: BAR_ITEMSTITLE,

    stock_zero_message: STOCK_MESSAGE_ZERO,

    popup_message1:     POPUP_MESSAGE1,
    popup_message2:     POPUP_MESSAGE2,
    popup_sucess_title: POPUP_SUCESS_TITLE,
    popup_sucess_msg:   POPUP_SUCESS_MESSAGE,
    popup_error_title:  POPUP_ERROR_TITLE,
    popup_error_msg:    POPUP_ERROR_MESSAGE,

    cartPermalink: CART_URL,
    nonce:         REST_NONCE,
    loadingif:     LOADING_GIF,
};


//const appTest = Vue.createApp(Test).mount('#appTest')
const appTotalBox = Vue.createApp(TotalBox).mount('#appTotalBox')
const appMatrix   = Vue.createApp(Matrix).mount('#appMatrix')
const appBar      = Vue.createApp(ItemsBar).mount('#appBar')
const appPopUp    = Vue.createApp(PopUp).mount('#appPopUp')
