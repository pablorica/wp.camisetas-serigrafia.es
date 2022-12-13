<template>
    <div class="cdg_popup" 
    v-if="display"
>
        <div class="white">
            <h3 class="text-center"></h3>
            <p class="text-center"></p>
            <div class="text-center buttons">
                <button class="btn btn-danger compra">
                    {{message1}}
                </button>
                <a v-bind:href=cartPermalink 
                    class="btn btn-danger compra"
                    v-html=message2
                ></a>
                <button class="btn btn-danger cerrar"
                    v-html=close
                    @click="closePopUp()"
                ></button>
            </div>
        </div>
    </div>
</template>

<script>  
console.log('VUE PopUp loaded')

import { config, emitter } from '../vueMain.js';

export default {
        name: 'pop-up',
        data: function() {
            return {
                display:       true,
                message1:      config.popup_message1,
                message2:      config.popup_message2,
                close:         config.popup_close,
                cartPermalink: config.cartPermalink
            }
        },
        mounted: function () {
            emitter.on('showPopUp', () => {
                this.displayPopUp();
            }); 
        },
        methods: {
            displayPopUp: function () {
                var self      = this;
                self.display  = true 
            },
            closePopUp: function () {
                var self      = this;
                self.display  = false 
            },
        }
    }
</script>

<style scoped>

</style>