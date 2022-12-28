<template>
    <table class="cdg_table">
        <tbody>
            <tr>
                <th>
                    <span class="cdg_alert">1</span>{{wording.title1}}
                </th>
                <td>
                    <div>
                        <b>{{wording.titleFront1}}</b><br>
                        <select 
                            id="estampado_delantero" 
                            name="estampado_delantero"
                            v-model="indexFront"
                        >
                            <option value="">{{wording.defaultSelect1}}</option>
                            <template v-for="(row, index) in rows">
                                <option :value="index"
                                    v-html="row.stamping"
                                ></option>
                            </template>						
                        </select>
                    </div>
                    <div>
                        <b>{{wording.titleRear1}}</b><br>
                        <select 
                            id="estampado_trasero" 
                            name="estampado_trasero"
                            v-model="indexRear"
                        >
                            <option value="">{{wording.defaultSelect1}}</option>
                            <template v-for="(row, index) in rows">
                                <option :value="index"
                                    v-html="row.stamping"
                                ></option>
                            </template>						
                        </select>
                    </div>
                    <div style="clear: both;float: none"></div>
                </td>
            </tr>
            <tr>
                <th>
                    <span class="cdg_alert">2</span>{{wording.title2}}
                </th>
                <td>
                    <div
                      :class="(Number.isInteger(indexFront)? '' : 'disabled')"
                    >
                        <b>{{wording.titleFront2}}</b><br>
                        <select 
                            id="tamano_delantero" 
                            name="tamano_delantero" 
                            :disabled="!Number.isInteger(indexFront)"
                        >
                            <option v-if="!Number.isInteger(indexFront)"
                                value=""
                            >{{wording.disabledSelect2}}</option>
                            <template v-if="Number.isInteger(indexFront)">
                                <option value="">{{wording.defaultSelect2}}</option>
                                <option v-html="rows[indexFront].area1"></option>
                                <option v-html="rows[indexFront].area2"></option>
                            </template>
                        </select>
                    </div>
                    <div
                      :class="(Number.isInteger(indexRear)? '' : 'disabled')"
                    >
                        <b>{{wording.titleRear2}}</b><br>
                        <select 
                            id="tamano_trasero" 
                            name="tamano_trasero" 
                            :disabled="!Number.isInteger(indexRear)"
                        >
                            <option v-if="!Number.isInteger(indexRear)"
                                value=""
                            >{{wording.disabledSelect2}}</option>
                            <template v-if="Number.isInteger(indexRear)">
                                <option value="">{{wording.defaultSelect2}}</option>
                                <option v-html="rows[indexRear].area1"></option>
                                <option v-html="rows[indexRear].area2"></option>
                            </template>
                        </select>
                    </div>
                    <div style="clear: both;float: none"></div>
                </td>
            </tr>
            <tr>
                <th><span class="cdg_alert">3</span> {{baggedtitle}} </th>
                <td class="cdg_radio">
                    <input type="radio" 
                        id="yes" 
                        v-bind:value="baggedprice" 
                        v-model="bagged" 
                        @change="changeCookie()"
                    />
                    <label for="yes">Si</label>

                    <input type="radio" 
                        id="no" 
                        value="0" 
                        v-model="bagged"
                        @change="changeCookie()" 
                    />
                    <label for="no">No</label>
                    &nbsp;
                    <small v-html="baggedwording"></small>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<script>  
//console.log('customisations-for-cg-woo-grid: VUE Budgeteer loaded')
import { emitter } from '../vue-customisations-for-cg-woo-grid.js';

export default {
    name: 'budgeteer',
    data() {
        return {
            apiurl:        '',
            serwording:    '',
            wording:       {},
            serialized:    '',
            rows:          [],
            indexFront:    '',
            indexRear:     '',
            bagged:         0,
            baggedtitle:   '',
            baggedwording: '',
        }
    },
    mounted: function () {
        
        let element        = this.$el;
        this.apiurl        = element.parentElement.dataset.apiurl;
        this.serialized    = element.parentElement.dataset.serialized;
        this.serwording    = element.parentElement.dataset.wording;

        this.baggedprice   = element.parentElement.dataset.baggedprice;
        this.baggedtitle   = element.parentElement.dataset.baggedtitle;
        this.baggedwording = element.parentElement.dataset.baggedwording;
        
        this.buildBugeteer(); 
        //this.$cookie.set("keyName", keyValue, "expiring time")
        //$cookies.set(keyName, value[, expires[, path[, domain[, secure[, sameSite]]]]])
        window.$cookies.set("addsRegPrice","",0);
    },
    methods: {

        buildBugeteer: function() {
            let self   = this
            let getURL = self.apiurl+'/encode';

            if(self.serialized !== ''){

                axios.post(getURL,{
                        serialized: self.serialized
                    })
                    .then(function (response) {
                        //console.log(response);
                        self.rows = response.data
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }

            if(self.serwording !== ''){

                axios.post(getURL,{
                        serialized: self.serwording
                    })
                    .then(function (response) {
                        //console.log(response);
                        self.wording = response.data
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        },
        changeCookie: function() {
            let self   = this
            window.$cookies.set("addsRegPrice",self.bagged);
            //TODO this works, but it would be better to find a way to trigerr the update Total function using VUE elements , or tiny-emitter
            document.getElementById("updateTotal").click();
        }
    }
}
</script>

<style scoped>

</style>