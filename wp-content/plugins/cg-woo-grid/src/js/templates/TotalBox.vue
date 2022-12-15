<template>
    <div v-if="empty" class="row index-row">
        <div class="row">
            <div class="col-12">
                <!-- section -->
                <section class="hv-50">
                    <!-- article -->
                    <article id="post-404">
                        <h2 
                            class="text-center mt-5"
                            v-html="noresults">
                        </h2>
                    </article>
                    <!-- /article -->
                </section>
                <!-- /section -->
            </div><!-- /.col-12 -->
        </div><!-- /.row -->
    </div><!-- /.container -->

    <div id="boxTotal"
            class="cdg_total"
        >
            <div class="loading" 
                v-bind:class = "(loading===false)?'hide':''"
            >
                <i class="fa fa-refresh fa-spin"></i>
            </div>
            <div>
                <h3 v-html="results.boxtitle"></h3>
                <div id="cdg_units" 
                    class="units"
                >
                    <div style="margin-bottom: 15px;text-transform: none;">
                        <span v-html="results.unitpricetitle"></span>:
                        <input id="cdg_unitprice" 
                            type="text" 
                            name="unitprice" 
                            v-bind:value="results.unitprice" 
                            disabled
                        >
                    </div>
                    <div style="clear: both;"></div>
                </div>
                <div id="cdg_prizes_table" 
                    class="units"
                >
                    <table id="prizes_table">
                        <thead>
                            <tr>
                                <th v-html="results.taxonomy_y_name"></th>
                                <th v-html="results.taxonomy_x_name"></th>
                                <th v-html="results.pricetitle"></th>
                                <th v-html="results.unitstitle"></th>
                                <th v-html="results.amountitle"
                                    class="text-right"
                                ></th>
                            </tr>
                        </thead>
                        <tbody v-html="results.html">
                        </tbody>
                    </table>
                </div>
                <div class="total">
                    <h5 style="margin-bottom: 15px;">
                        ( 
                        <small v-html="results.unitpricetitle + ' :'"></small> 
                        <span id="span_unitpricetax" 
                            v-html="results.unitpricetax"
                        ></span> 
                        )
                    </h5>
                    <h5>
                        <small v-html="results.subtotaltitle + ' :'"></small> 
                        <span id="subtotal"
                            v-html="results.subtotalformatted"
                        ></span>
                    </h5>
                    <h5>
                        <small v-html="results.vattitle + ' :'"></small> 
                        <span id="taxes"
                            v-html="results.taxesformatted"
                        ></span>
                    </h5>
                    <h4>
                        <small v-html="results.totaltitle + ' :'"></small> 
                        <span id="total"
                            v-html="results.totalformatted"
                        ></span>
                    </h4>
                </div>
            </div>
            <button id="cdg_add_products" 
                class="button alt add_to_cart_button"
                v-bind:disabled="(addcartenabled === false)"
                v-html="results.addtocarttitle"
                @click="addtocart"
            >
            </button>
            <p>&nbsp;</p>
        </div>
  </template>

<script>  
//console.log('VUE TotalBox loaded')

import { config, emitter } from '../vueMain.js';

export default {
        name: 'total-box',
        data: function() {
            return {
            	loading: 	    false,
            	noresults:      config.noresults,
            	empty: 		    false,
                product_id:     null,
                addcartenabled: false,
                addurl:         null,
                results: 	    [],
                fixed: 	        []
            }
        },
        mounted: function () {

            let element = this.$el;
            this.product_id = element.parentElement.dataset.currentproduct;
            //console.log(this.product_id);


            emitter.on('fireReset', () => {
                this.resetAll();
            });   
            
            emitter.on('firePopulateBox', ($response) => {
                this.populateBox($response);
            });

            emitter.on('hideTotalBox', () => {
                this.loading = false;
            });

            this.buildTotalBox(); 
        },
        methods: {
            buildTotalBox: function () {

                var self   = this;
                self.empty = true;

                if(self.product_id !== null) {

                    self.empty = false;
                    
                    var getURL = config.api.index + 
                    '/totalbox/product_id='+self.product_id;

                    self.loading = true;
                    //console.log('buildTotalBox loading '+ getURL);
                    (function($){   
                        $.get( 
                            getURL , 
                            function( r ){
                                self.loading  = false;
                                self.results = r;
                            }
                        ).done(function() {

                            self.fixed = self.results

                            self.results.html              = ''
                            self.results.unitprice         = self.results.zeroprice
                            self.results.unitpricetax      = self.results.formatzeroprice
                            self.results.subtotalformatted = self.results.formatzeroprice
                            self.results.taxesformatted    = self.results.formatzeroprice
                            self.results.totalformatted    = self.results.formatzeroprice
                            
                        }).fail(function() {
                            //console.log('woops');
                            self.loading = false;
                            self.matrix   = [];
                            self.empty   = true;
                        });
                    })( jQuery );
                }

	        },
            populateBox: function (response) {

                var self     = this;
                self.loading = false;

                if(response) {
                    self.results        = { ...self.fixed, ...response } 
                    self.addurl         = self.results.addurl
                    self.addcartenabled = response.addcartenabled;  
                } else {
                    self.loading = true;
                }
                

                //console.log('populateBox');
                //console.log(response)
                // for(var rkey in response){
                //     console.log('response key: ',rkey);
                //     console.log('input value: ',response[rkey]);
                // }

                //console.log('addurl', self.addurl);
            },
            addtocart: function() {
                var self      = this;
                self.loading  = true;
                
                //console.log('addurl', self.addurl);
                if(self.addurl) {

                    let baseURL = window.location.href
                    if(baseURL.includes('?')) {
                        baseURL = window.location.href.split('?')[0]
                    }

                    console.log('baseURL: ', baseURL);
                    
                    var getURL = baseURL + 
                    '?add-to-cart='+
                    self.addurl;

                    self.loading = true;
                    //console.log('adding to cart: ', getURL);
                    (function($){
                        $.get( getURL , function( r ){
                            //self.loading  = false;
                            //console.log('results: ' + r);
                            if(r.startsWith("<success>1</success>"))
                            {
                                //console.log('Updated!!! ');
                                emitter.emit('showPopUp');
                            }
                        }).done(function() {
                            //console.log('Added to cart');
                            self.loading  = false;
                        }).fail(function() {
                            //console.log('woops');
                            self.loading = true;
                        });
                    })( jQuery );
                }
            },
            resetAll: function(){
                console.log('Reset');
                this.empty = false;
                this.buildTotalBox();
            }
        }
    }
</script>

<style scoped></style>
