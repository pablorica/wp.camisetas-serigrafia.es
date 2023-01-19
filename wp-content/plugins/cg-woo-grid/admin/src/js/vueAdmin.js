import Vue from 'vue'
import Test from './templates/Test.vue';

import Emitter from 'tiny-emitter';

console.log('VUE Admin loaded')

export var emitter = new Emitter();

window.onload = function(event) {
    if(document.getElementById("appAdminTest")) {
        const appTest = Vue.createApp(Test).mount('#appAdminTest')
    }
};
