<template>
  <div class="price-container">
    <!-- Adult Section -->
    <div class="table-section">
      <div class="table-header">
        <b>Adulto</b>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
          <tr>
            <th>Tipo</th>
            <th v-for="(row, index) in prices" :key="index" :id="'row_'+index">
              {{ row.quantity }}
            </th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td>Blanco</td>
            <td v-for="(row, index) in prices" :key="index" :id="'row_'+index">
              {{ row.price.adult.white ?? "-" }}
            </td>
          </tr>
          <tr>
            <td>Color</td>
            <td v-for="(row, index) in prices" :key="index" :id="'row_'+index">
              {{ row.price.adult.color ?? "-" }}
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <div class="table-header">
        <b>Niño</b>
      </div>

      <div class="table-wrapper">
        <table>
          <thead>
          <tr>
            <th>Tipo</th>
            <th v-for="(row, index) in prices" :key="index" :id="'row_'+index">
              {{ row.quantity }}
            </th>
          </tr>
          </thead>
          <tbody>
          <tr>
            <td>Blanco</td>
            <td v-for="(row, index) in prices" :key="index" :id="'row_'+index">
              {{ row.price.child.white ?? "-" }}
            </td>
          </tr>
          <tr>
            <td>Color</td>
            <td v-for="(row, index) in prices" :key="index" :id="'row_'+index">
              {{ row.price.child.color ?? "-" }}
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
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

            console.log('prices URL');
            console.log(getURL);

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