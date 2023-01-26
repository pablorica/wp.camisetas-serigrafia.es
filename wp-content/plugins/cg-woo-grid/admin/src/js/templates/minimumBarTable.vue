<template>
    <table class="widefat">
        <thead>
            <tr>
                <th class="row-title" 
                    v-html="theadertype"
                ></th>
                <th class="row-title" 
                    v-html="theaderitem"
                ></th>
                <th class="row-title"  
                    v-html="theaderminimum"
                ></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(row, index) in rows"
                v-bind:id="'row_'+index" 
                v-bind:class = "(index % 2 === 0)?'alternate':''"
            >
                    <th>
                        <select v-model="row.type" 
                            @change="resetRow(index)">
                            <option v-for="typeoption in typeoptions"
                                :value="typeoption.slug"
                            >
                            {{ typeoption.title }}
                            </option>
                        </select>
                    </th>
                    <th>

                    <!-- Vue Select-->
                    <selectBox
                        v-if = "selectgeturl"
                        :selectgeturl          = "selectgeturl + row.resturl"
                        :defrows               = "row"
                        :resethis              = "row.reset"
                        :proplaceholder        = "selctplachldr"
                        @changeSelectedOptions = "row.items = $event, updateRows()"
                    >
                    </selectBox>
                    <!-- / Vue Select  -->
                            
                    </th>
                    <th>
                        <input type="number" 
                            autocomplete="off"
                            size="4"
                            v-model="row.profit"
                            @input="updateRows()"
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
                <th colspan="6">
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
            theadertype:        '',
            theaderitem:        '',
            theaderminimum:     '',
            deletetitle:        '',
            tfootaddrow:        '',
            selctplachldr:      '',

            selectgeturl:       '',
            serialized:         '',
            apiurl:             '',
            typeoptions: [
                {
                    slug:       'product',
                    resturl:     'search/?subtype=product'
                }, 
                {
                    slug:       'product_cat',
                    resturl:    'product_cat/?joint'
                }, 
            ],
            rows:               [],
            frows:              [],
            derow: {
                resturl:        'search/?subtype=product',
                type:           'product',
                items:          '',
                profit:         0,
            }      
        }
    },
    mounted: function () {

        let element  = this.$el;
        let parentEl = element.parentElement;
        //console.log(element)
        this.theadertype    = parentEl.dataset.theadertype;
        this.theaderitem    = parentEl.dataset.theaderitem;
        this.theaderminimum = parentEl.dataset.theaderminimum;
        this.selectgeturl   = parentEl.dataset.selectgeturl;
        this.deletetitle    = parentEl.dataset.deletetitle;
        this.tfootaddrow    = parentEl.dataset.tfootaddrow;
        this.selctplachldr  = parentEl.dataset.selctplachldr;
        

        this.apiurl         = parentEl.dataset.apiurl;
        this.hiddenboxid    = parentEl.dataset.hiddenboxid;
        //this.serialized     = this.$refs[this.refId].value; <-- this.$refs no working with SFCs
        this.serialized     = document.getElementById(this.hiddenboxid).value;

        this.rows.push(this.derow);
        this.typeoptions = this.typeoptions.map(function (typeoption) { 
            if(typeoption.slug == 'product') {
                typeoption.title = parentEl.dataset.producttitle;
            }
            if(typeoption.slug == 'product_cat') {
                typeoption.title = parentEl.dataset.categorytitle;
            }

            return typeoption
        })

        this.buildTable(); 

    },
    
    methods: {
        resetRow: function (index) {
            //console.log('resetRow')
            //console.log('index: ' + index)
            var self = this;

            const typoption = self.typeoptions.find(function (typeoption) { 
                if(typeoption.slug == self.rows[index].type) {
                    return true
                }
            })
            self.rows[index].resturl = typoption.resturl;
            self.rows[index].items   = self.derow.items
            self.rows[index].profit  = self.derow.profit
            self.rows[index].reset   = true;
 
            //console.log(self.rows)
            self.updateRows(); 
        },
        buildTable: function () {
            config.buildTable(this);
        },
        updateRows: function () {

            for (var i=0; i < this.rows.length; ++i) {
                let row = this.rows[i]
                //console.log('row')
                //console.log(row)

                if(typeof row.items[0] !== 'undefined'
                   && typeof row.items[0].id !== 'undefined'
                ) {
                    row.items = row.items.map(function (item) { 
                        return {
                            id:item.id, 
                            title:item.title?item.title:item.name
                        }
                    })
                    this.frows[i] = row
                    row.reset = false;
                } 
                //console.log('frows')
                //console.log(this.frows)
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
