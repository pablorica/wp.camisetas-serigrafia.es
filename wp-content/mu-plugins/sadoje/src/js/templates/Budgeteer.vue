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
                            v-model="indexFront"
                            @change="changeCookie()"
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
                            v-model="indexRear"
                            @change="changeCookie()"
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
                            v-model="areaFront"
                            @change="changeCookie()"
                            :disabled="!Number.isInteger(indexFront)"
                        >
                            <option v-if="!Number.isInteger(indexFront)"
                                value=""
                            >{{wording.disabledSelect2}}</option>
                            <template v-if="Number.isInteger(indexFront)">
                                <option value="">{{wording.defaultSelect2}}</option>
                                <option value="1" v-html="rows[indexFront].area1"></option>
                                <option value="2" v-html="rows[indexFront].area2"></option>
                            </template>
                        </select>
                    </div>
                    <div
                      :class="(Number.isInteger(indexRear)? '' : 'disabled')"
                    >
                        <b>{{wording.titleRear2}}</b><br>
                        <select 
                            v-model="areaRear"
                            @change="changeCookie()"
                            :disabled="!Number.isInteger(indexRear)"
                        >
                            <option v-if="!Number.isInteger(indexRear)"
                                value=""
                            >{{wording.disabledSelect2}}</option>
                            <template v-if="Number.isInteger(indexRear)">
                                <option value="">{{wording.defaultSelect2}}</option>
                                <option value="1" v-html="rows[indexRear].area1"></option>
                                <option value="2" v-html="rows[indexRear].area2"></option>
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
            areaFront:     '',
            indexRear:     '',
            areaRear:      '',
            bagged:         0,
            baggedtitle:   '',
            baggedwording: '',
            cookieName:    "cgWooGrid",
            totalProducts: 0
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
            let self      = this
            let cookieObj = {}
            console.log('addOn changeCookie')

            
            cookieObj.descStamping = '';

            if( typeof self.indexFront !== 'undefined'
                && Number.isInteger(self.indexFront) 
            ) {
                cookieObj.front = {}
                let keyFront    = self.indexFront

                cookieObj.front.screenPrice = self.rows[keyFront].screen_price
                cookieObj.front.stamping    = self.rows[keyFront].stamping

                if( typeof self.areaFront !== 'undefined') {
                    //console.log(self.areaFront)
                    if(self.areaFront === '1') {
                        cookieObj.front.area       = self.rows[keyFront].area1
                        cookieObj.front.whitePrice = self.rows[keyFront].white_price1
                        cookieObj.front.colorPrice = self.rows[keyFront].color_price1
                    }
                    if(self.areaFront === '2') {
                        cookieObj.front.area       = self.rows[keyFront].area2
                        cookieObj.front.whitePrice = self.rows[keyFront].white_price2
                        cookieObj.front.colorPrice = self.rows[keyFront].color_price2
                    }
                }

                cookieObj.descStamping += '\nEstampación en pecho' 
                    +' ('+ cookieObj.front.stamping
                    + ' ' + cookieObj.front.area + ') ';
            }

            if( typeof self.indexRear !== 'undefined'
                && Number.isInteger(self.indexRear) 
            ) {
                cookieObj.rear = {}
                let keyRear    = self.indexRear

                cookieObj.rear.screenPrice = self.rows[keyRear].screen_price
                cookieObj.rear.stamping    = self.rows[keyRear].stamping

                if( typeof self.areaRear !== 'undefined') {
                    //console.log(self.areaRear)
                    if(self.areaRear === '1') {
                        cookieObj.rear.area       = self.rows[keyRear].area1
                        cookieObj.rear.whitePrice = self.rows[keyRear].white_price1
                        cookieObj.rear.colorPrice = self.rows[keyRear].color_price1
                    }
                    if(self.areaRear === '2') {
                        cookieObj.rear.area       = self.rows[keyRear].area2
                        cookieObj.rear.whitePrice = self.rows[keyRear].white_price2
                        cookieObj.rear.colorPrice = self.rows[keyRear].color_price2
                    }
                }

                cookieObj.descStamping += '\nEstampación en espalda' 
                    +' ('+ cookieObj.rear.stamping
                    + ' ' + cookieObj.rear.area + ') ';
            }

            cookieObj.bagged = self.bagged
            if(self.bagged > 0 ) {
                cookieObj.descStamping += '\nEmbolsado';
            }


            if(typeof document.getElementById("cdg_total").innerHTML !== 'undefined'){
                self.totalProducts  = parseInt(
                    document
                    .getElementById("cdg_total")
                    .innerHTML
                );
                //console.log('AddOn: total products '+self.totalProducts)
                cookieObj.totalProducts = self.totalProducts
            }
            
            //console.log('Is cookie? '+window.$cookies.isKey(self.cookieName))
            if(window.$cookies.isKey(self.cookieName)) {
                let oldCookieObj = window.$cookies.get(self.cookieName);

                if(typeof oldCookieObj.description !== 'undefined') {

                    cookieObj.description = [
                        oldCookieObj.description,
                        cookieObj.descStamping
                    ]
                    delete oldCookieObj.description
                } else {
                    cookieObj.description = [
                        '',
                        cookieObj.descStamping
                    ]
                }
                

                delete oldCookieObj.front
                delete oldCookieObj.rear
                

                cookieObj = {...oldCookieObj,...cookieObj};
            } else {
                cookieObj.description = [
                    '',
                    cookieObj.descStamping
                ]
            }
            delete cookieObj.descStamping

            const cookieJSON = JSON.stringify(cookieObj);
            window.$cookies.set(self.cookieName,cookieJSON);
            console.log('AddOn cookie')
            console.log(window.$cookies.get(self.cookieName))

            /* TODO this works and trigers the updateTotal function, 
            but it would be better to find a way to triger that function 
            using VUE elements , or tiny-emitter */
            document.getElementById("updateTotal").click();
        }
    }
}
</script>

<style scoped>

</style>