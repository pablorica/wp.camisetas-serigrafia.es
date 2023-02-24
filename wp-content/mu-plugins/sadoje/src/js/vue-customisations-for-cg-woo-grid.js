//vue libraries are already loaded by cg-woo-grid plugin
import Vue from 'vue'
import VueCookies from 'vue-cookies'
// default options config: { expires: '1d', path: '/', domain: '', secure: '', sameSite: 'Lax' }

import Test from './templates/Test.vue';
import Budgeteer from './templates/Budgeteer.vue';
import Estimator from './templates/Estimator.vue';

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

//const appTest      = Vue.createApp(Test).mount('#appTest')
const appBudgeteer = Vue.createApp(Budgeteer).use(VueCookies).mount('#appBudgeteer')
const appEstimator = Vue.createApp(Estimator).use(VueCookies).mount('#appEstimator')
