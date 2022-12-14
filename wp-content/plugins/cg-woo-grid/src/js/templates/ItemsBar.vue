<template>
    <div id="itemsBar"
        class="cdg_cbar"
        :class="barClass"
    >
        <span class="bar">
            <span :style="{width: barWidth+'%'}"></span>
        </span>

        <div class="cdg_cbar__content">
            <span class="cdg_cbar__message" 
                v-text="message"
            ></span>
            <span class="cdg_cbar__separator"> | </span>
            <span id="cdg_total">{{items}}</span>
            <span class="cdg_cbar__slash">/</span>
            <span class="cdg_cbar__min_quantity">{{min_quantity}}</span>
            <span class="cdg_cbar__separator">&nbsp;</span>
            <span class="cdg_cbar__uds">{{itemsTitle}}</span>
        </div>
    </div>
</template>

<script>  
console.log('VUE ItemsBar loaded')

import { config, emitter } from '../vueMain.js';

export default {
        name: 'items-bar',
        data: function() {
            return {
                message:        config.bar_message,
                success:        config.bar_success,
                items:          0,
                itemsTitle:     config.bar_itemstitle,
                min_quantity:   config.min_quantity,
                barWidth:       0,
                barClass:       ''
            }
        },
        mounted: function () {
        	this.displayBar(0); 

            emitter.on('fireUpdateBar', (purchaseAmount) => {
                this.displayBar(purchaseAmount);
            }); 
        },
        methods: {
            displayBar: function (purchaseAmount) {

                var self      = this;
                self.items    = purchaseAmount  
                self.barWidth = (purchaseAmount * 100 / self.min_quantity)   
                self.barClass = '';

                if(purchaseAmount >= self.min_quantity){
                    self.barClass = 'full';
                    self.barWidth = 100;
                    self.message = self.success;
                }

            },
            resetAll: function(){
                console.log('Reset');
                this.displayBar(0); 
            }
        }
    }
</script>

<style scoped>

</style>