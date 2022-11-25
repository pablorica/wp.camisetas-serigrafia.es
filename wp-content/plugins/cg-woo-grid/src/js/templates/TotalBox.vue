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
                        <input id="cdg_price_u" 
                            type="text" 
                            name="price_u" 
                            v-bind:value="results.unitpricevalue" 
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
                        <tbody></tbody>
                    </table>
                </div>
                <div class="total">
                    <h5 style="margin-bottom: 15px;">
                        ( 
                        <small v-html="results.unitpricetitle + ' :'"></small> 
                        <span id="span_price_u" 
                            v-html="results.formatzeroprice"
                        ></span> 
                        )
                    </h5>
                    <h5>
                        <small v-html="results.subtotal + ' :'"></small> 
                        <span id="TotalPrice"
                            v-html="results.formatzeroprice"
                        ></span>
                    </h5>
                    <h5>
                        <small v-html="results.vat + ' :'"></small> 
                        <span id="taxesRow"
                            v-html="results.formatzeroprice"
                        ></span>
                    </h5>
                    <h4>
                        <small v-html="results.total + ' :'"></small> 
                        <span id="TotalPriceIVA"
                            v-html="results.formatzeroprice"
                        ></span>
                    </h4>
                </div>
            </div>
            <button id="cdg_add_products" 
                class="btn btn-primary text-uppercase text-center"
                disabled 
                v-html="results.addtocart"
            >
            </button>
            <p>&nbsp;</p>
            <input type="hidden" 
                name="add-to-cart" 
                v-bind:value="results.product_id"
            >
            <input type="hidden" 
                name="product_id" 
                v-bind:value="results.product_id"
            >
            <input id="cdg_description" 
                type="hidden" 
                name="custom_options[description]" 
                value=""
            ><br>
            <input id="cdg_price" 
                type="hidden" 
                name="custom_options[custom_price]" 
                value="0"
            ><br>
        </div>
  </template>

<script>  
console.log('VUE TotalBox loaded')

import { config, emitter } from '../vueMain.js';

export default {
        name: 'total-box',
        data: function() {
            return {
            	loading: 	    false,
            	noresults:      config.noresults,
            	empty: 		    false,
                product_id:     null,
                results: 	    []
            }
        },
        mounted: function () {
        	this.buildTotalBox(); 

            emitter.on('fireReset', () => {
                this.resetAll();
            });   
            
            emitter.on('firePopulateBox', ($response) => {
                this.populateBox($response);
            });
        },
        methods: {
            buildTotalBox: function () {

                var self = this;
                self.empty   = true;

                (function($){

                    //self.product_id = 6016
                    if( $('#cdg_form').length
                        && !self.product_id
                    ){
                        self.product_id = $('#cdg_form').data('currentproduct')
                    }
                    console.log('self.product_id: ' +self.product_id);
                    
                    
                    if(self.product_id !== null) {

                        self.empty = false;
                        
                        var getURL = config.api.index + 
                        '/totalbox/product_id='+self.product_id;

                        self.loading = true;
                        console.log('buildTotalBox loading '+ getURL);

                        $.get( getURL , function( r ){
                            self.loading  = false;
                            self.results = r;                            
                        }).done(function() {
                            
                            setTimeout(() => {
                                $('#phpBoxTotal').addClass('hide');
                            }, 250)
                            
                        }).fail(function() {
                            //console.log('woops');
                            self.loading = false;
                            self.matrix   = [];
                            self.empty   = true;
                        });
                    }
                })( jQuery );
                

	        },
            populateBox: function ($response) {

                var self = this;
                self.empty   = true;

                console.log('populateBox');
                console.log($response)

                /*
                $('#cdg_total').html(r[0]);
                $('#cdg_description').val(r[7]);
                $('#TotalPriceIVA').html(r[5]);
                $('#TotalPrice').html(r[4]);
                $('#cdg_price_u').val(r[3]);
                $('#cdg_price').val(r[6]);
                
                $('#tabla_precios tbody').html(r[13]);
                $('#taxesRow').html(r[14]);
                $('#span_price_u').html(r[15]);
                $('button#cdg_add_products').prop("disabled",false);
                */


                },
            resetAll: function(){
                console.log('Reset');
                this.empty = false;
                this.buildBox();
            }
        }
    }
</script>

<style scoped>

</style>