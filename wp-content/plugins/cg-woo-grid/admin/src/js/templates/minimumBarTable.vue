<template>
    <table class="widefat">
        <thead>
            <tr>
                <th class="row-title" 
                    v-html="theaderitem"
                ></th>
                <th v-html="theaderminimum"></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(row, index) in rows"
                v-bind:id="'row_'+index" 
                v-bind:class = "(index % 2 === 0)?'alternate':''"
            >
                    <th>

                    <!-- Vue Select-->
                    <selectBox
                        v-if="selectgeturl"
                        :selectgeturl = "selectgeturl"
                        :defrows = "defrows"
                        @changeSelectedOptions="row.items = $event, updateRows()"
                    >
                    </selectBox>
                    <!-- / Vue Select  -->
                            
                    </th>
                    <th>
                        <input type="number" 
                            autocomplete="off"
                            size="4"
                            v-model="row.profit"
                            @input="updateRows('profit')"
                        >
                    </th>
                    <th style="text-align:right">
                        <button class="button button-secondary button-delete" 
                            style="background:#DC3232;color:#FFF;border-color:#FFF;"
                            @click="deleteRow(index, $event)"
                            v-html="deletetitle"
                        ></button>
                    </th>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5">
                    <p style="text-align:right">
                        <button class="button button-secondary" 
                            @click="addRow($event)"
                            v-html="tfootaddrow"
                        ></button>
                    </p>
                </th>
            </tr>
        </tfoot>
    </table>
</template>

<script>  

console.log('VUE admin Table Minimum Bar loaded')

import { config } from '../vueAdmin.js';
import selectBox from './selectBox.vue';

export default {
    name: 'cdg-minimumbar',
    components: {
        selectBox
    },
    data: function() {
        return {
            theaderitem:        '',
            theaderminimum:     '',
            selectgeturl:       '',
            deletetitle:        '',
            tfootaddrow:        '',
            serialized:         '',
            apiurl:             '',
            rows: [{
                items:          '',
                profit:         0,
            }],
            frows:              [],
            defrows:            []         
        }
    },
    mounted: function () {

        let element  = this.$el;
        let parentEl = element.parentElement;
        //console.log(element)
        this.theaderitem    = parentEl.dataset.theaderitem;
        this.theaderminimum = parentEl.dataset.theaderminimum;
        this.selectgeturl   = parentEl.dataset.selectgeturl;
        this.deletetitle    = parentEl.dataset.deletetitle;
        this.tfootaddrow    = parentEl.dataset.tfootaddrow;
        this.apiurl         = parentEl.dataset.apiurl;
        
        //this.serialized     = this.$refs[this.refId].value; <-- this.$refs no working with SFCs
        //console.log(document.getElementById("cg-woo-grid_minimum_options"))
        this.hiddenboxid   = "cg-woo-grid_minimum_options"
        this.serialized    = document.getElementById(this.hiddenboxid).value;
        //console.log(this.serialized )

        this.buildTable(); 

    },
    
    methods: {
        buildTable: function () {
            config.buildTable(this);
        },
        updateRows: function (options = '') {

            // for (var i=0; i < this.rows.length ; ++i) {
            //     let frow = {}
            //     let row = this.rows[i]
            //     frow.profit = row.profit
            //     frow.items = row.items.map(item => item.id)
            //     this.rows[i] = frow
            // }
                
            for (var i=0; i < this.rows.length; ++i) {
                let row = this.rows[i]

                if(typeof row.items[0] !== 'undefined'
                   && typeof row.items[0].id !== 'undefined'
                ) {
                    row.items = row.items.map(function (item) { 
                        return {id:item.id, title:item.title}
                    })
                    this.frows[i] = row
                } 
                
            }

            config.updateRows(this)
            
        },
        addRow: function(event) {
            config.addRow(event, this)
        },
        deleteRow(row, event) {
            config.deleteRow(event, this, row)
        }
    },
}
</script>
