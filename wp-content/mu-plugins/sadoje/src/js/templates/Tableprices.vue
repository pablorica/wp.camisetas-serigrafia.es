<template>
    <table class="table-responsive"
        style="text-align: center; font-size: 12px;width: 100%; margin-top: 0px"
    >
        <tbody>
            <tr>
                <th style="border: 1px solid darkgray; background-color: lightgrey;">
                    <b>Adulto</b>
                </th>
                <th v-for="(row, index) in prices"
                    v-bind:id="'row_'+index" 
                    style="border: 1px solid darkgray; background-color: lightgrey;"
                >
                    <b>{{row.quantity}}</b>
                </th>
            </tr>
            <tr>
                <th style="border: 1px solid darkgray">
                    Blanco                        
                </th>
                <td v-for="(row, index) in prices"
                    v-bind:id="'row_'+index" 
                    style="border: 1px solid darkgray; font-size: 10px;"
                >
                    <b>{{row.price.white ?? "-"}}</b>
                </td>                
            </tr>
            <tr>
                <th style="border: 1px solid darkgray">
                    Color
                </th>
                <td v-for="(row, index) in prices"
                    v-bind:id="'row_'+index" 
                    style="border: 1px solid darkgray; font-size: 10px;"
                >
                    <b>{{row.price.color ?? "-"}}</b>
                </td> 
            </tr>
        </tbody>
    </table>


</template>

<script>  
console.log('customisations-for-cg-woo-grid: VUE Table Prices')
import { emitter } from '../vue-customisations-for-cg-woo-grid.js';

export default {
    name: 'pricetable',
    data() {
        return {
            apiurl:             '',
            prices:             '',
            rows:               [],
        }
    },
    mounted: function () {

        let element = this.$el;
        this.apiurl = element.parentElement.dataset.apiurl;
        this.prices = JSON.parse(element.parentElement.dataset.prices);

        this.buildTablePrices(); 
        
    },
    methods: {

        buildTablePrices: function() {

            let self   = this
            let getURL = self.apiurl+'/encode';

            console.log('prices');
            console.log(self.prices);

            /*
            if(self.proftypes !== ''){

                

                axios.post(getURL,{
                        serialized: self.proftypes
                    })
                    .then(function (response) {
                        //console.log(response);
                        self.rows = response.data
                        console.log(response.data);
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
            */

        },
    }
}
</script>

<style scoped>

</style>