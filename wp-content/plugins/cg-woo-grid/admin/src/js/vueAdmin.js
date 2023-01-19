import Vue from 'vue'

import Test from './templates/Test.vue';
import TestSelect from './templates/TestSelect.vue';



console.log('VUE Admin loaded')

//export var emitter = new Emitter();

window.onload = function(event) {
    if(document.getElementById("appAdminTest")) {
        const appTest = Vue.createApp(Test).mount('#appAdminTest')
    }
    if(document.getElementById("appAdminSelectTest")) {
        const appTest = Vue.createApp(TestSelect).mount('#appAdminSelectTest')
    }
};
