<template>
  <div class="price-container">
    <div v-if="prices.length" class="table-section">
      <div v-if="checkPriceAvailability('adult', 'color') || checkPriceAvailability('adult', 'white')" class="table-wrapper">
        <div class="table-header">
          <b>Adulto</b>
        </div>

        <table>
          <thead>

          </thead>
          <thead>
          <tr>
            <th>Tipo</th>
            <th v-for="(row, index) in prices" :key="index" :id="'row_'+index">
              {{ row.quantity }}
            </th>
          </tr>
          </thead>
          <tbody>
          <tr v-if="checkPriceAvailability('adult', 'white')">
            <td>Blanco</td>
            <td v-for="(row, index) in prices" :key="index" :id="'row_'+index">
              {{ row.price.adult.white ?? "-" }}
            </td>
          </tr>
          <tr v-if="checkPriceAvailability('adult', 'color')">
            <td>Color</td>
            <td v-for="(row, index) in prices" :key="index" :id="'row_'+index">
              {{ row.price.adult.color ?? "-" }}
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <div v-if="checkPriceAvailability('child', 'color') || checkPriceAvailability('child', 'white')" class="table-wrapper">
        <div class="table-header">
          <b>Niño</b>
        </div>
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
          <tr v-if="checkPriceAvailability('child', 'white')">
            <td>Blanco</td>
            <td v-for="(row, index) in prices" :key="index" :id="'row_'+index">
              {{ row.price.child.white ?? "-" }}
            </td>
          </tr>
          <tr v-if="checkPriceAvailability('child', 'color')">
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
import { emitter } from '../vue-customisations-for-cg-woo-grid.js';

export default {
    name: 'pricetable',
    data() {
        return {
            apiurl:             '',
            prices:             '',
            rows:               [],
            hasWhiteVariation:  false,
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
      checkPriceAvailability: function ( type, color) {
        return this.prices.some(item => item.price[type][color] !== null);
      }
    }
}
</script>

<style scoped>

</style>