<template>
    <div class="cdg_popup" 
    v-if="display"
>
        <div class="white">
            <h3 class="text-center">{{title}}</h3>
            <p class="text-center">{{body}}</p>
            <div class="wrapper-buttons">
                <button class="button-close"
                    v-html=message1
                    @click="closePopUp()"
                ></button>
                <a v-bind:href=cartPermalink 
                    class="button button-danger"
                    v-html=message2
                ></a>
                
            </div>
        </div>
    </div>
</template>

<script>  
//console.log('VUE PopUp loaded')

import { config, emitter } from '../vueMain.js';

export default {
        name: 'pop-up',
        data: function() {
            return {
                display:       false,
                message1:      config.popup_message1,
                message2:      config.popup_message2,
                title:         config.popup_sucess_title,
                body:          config.popup_sucess_msg,
                cartPermalink: config.cartPermalink,
                minQuantity:   config.min_quantity
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
                self.display  = true;
            },
            errorPopUp: function () {
                var self      = this;
                self.display  = true;
                self.title    = config.popup_error_title;
                self.body     = config.popup_error_msg;
            },
            closePopUp: function () {
                var self      = this;
                self.display  = false;
                emitter.emit('fireReset');

            },
        }
    }
</script>

<style scoped>

</style>