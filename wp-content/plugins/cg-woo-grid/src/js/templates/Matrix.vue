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

    <div id="matrixGrid" >
        <div class="loading" 
            v-bind:class = "(loading===false)?'hide':''"
        >
            <i class="fa fa-refresh fa-spin"></i>
        </div>
        <div class="cdg_product" 
            v-for="(attribute, key) in matrix"  
            v-bind:data-color="attribute.yslug" 
        >
            <span>
                <div>
                    <figure v-html="attribute.image">
                    </figure>
                    <br>
                    {{attribute.yslug}}
                </div>
            </span>
            <div>
                <div>
                    <span v-for="(valueX, keyX) in attribute.x" 
                        v-bind:data-talla="valueX.name" 
                    >
                        {{valueX.name}}
                        <br>
                        <input type="number" 
                            placeholder="0"
                            min="0" 
                            max="500"
                            v-model="quantities[valueX.slug]"
                            @change="updateTotal"
                        >
                    </span>
                </div>
            </div>
        </div>
    </div>
  </template>

<script>  
console.log('VUE Matrix loaded')

import { config, emitter } from '../vueMain.js';

export default {
        name: 'attributes-matrix',
        data: function() {
            return {
            	loading: 	    false,
            	noresults:      config.noresults,
            	empty: 		    false,
                product_id:     null,
                taxonomy_y:     'pa_color',
                taxonomy_x:     'pa_tamano',
                min_quantity:   50,
                matrix: 	    [],
                quantities: 	{}
            }
        },
        mounted: function () {
        	this.buildMatrix(); 
            
            emitter.on('fireReset', () => {
                this.resetAll();
            });     
        },
        methods: {
            buildMatrix: function () {

                var self = this;
                self.empty   = true;

                (function($){

                    //self.product_id = 6016
                    if( $('#cdg_form').length
                        && self.product_id === null
                    ){
                        self.product_id = $('#cdg_form').data('currentproduct')
                    }
                    //console.log('self.product_id: ' +self.product_id);
                    
                    if(self.product_id !== null) {

                        self.empty = false;
                        
                        var getURL = config.api.index + 
                        '/attmatrix/product_id='+self.product_id+
                        '/taxonomy_y='+self.taxonomy_y+
                        '/taxonomy_x='+self.taxonomy_x;

                        self.loading = true;
                        //console.log('loading '+ getURL);
                    
                        //$('#phpMatrix').addClass('loading');

                        $.get( getURL , function( r ){
                            self.loading  = false;
                            self.matrix = r;

                            //console.log(r);
                            self.matrix.map((attribute, key) => { 
                                attribute.yslug = attribute.y.toLowerCase();
                            });
                            
                        }).done(function() {
                            
                            setTimeout(() => {
                                $('#phpMatrix').addClass('hide');
                            }, 250)
                            setTimeout(() => {
                                $('#matrixGrid').removeClass('loading');
                            }, 500)
                            
                        }).fail(function() {
                            //console.log('woops');
                            self.loading = false;
                            self.matrix   = [];
                            self.empty   = true;
                        });
                    }
                })( jQuery );
                

	        },
            updateTotal: function(){

                var self = this;

                (function($){

                    let purchaseAmount = 0, 
                        _letSend = false,
                        _data={}

                    if( $('#cdg_form input[name="product_id"]').length
                        && self.product_id === null
                    ){
                        self.product_id = $('#cdg_form input[name="product_id"]').val()
                        //console.log('self.product_id: ' +self.product_id);
                    }
                    _data['product_id'] = self.product_id;
                    _data['taxonomy_y'] = self.taxonomy_y;
                    _data['taxonomy_x'] = self.taxonomy_x;
                    _data['quantities'] = self.quantities;
                    /*
                    $('#cdg_sizes input').each(function(){
                        if($(this).val() != 0){
                            _data[$(this).attr('name')] = $(this).val();
                            purchaseAmount += parseInt($(this).val());
                        }
                    });
                    */

                    if(_letSend !== false)	{
                        _letSend.abort();
                    }

                    var postURL = config.api.index + '/updatetotalbox';

                    console.log('updateTotal');
                    console.log(postURL);
                    console.log(_data);

                    _letSend = $.post({
                        url: postURL,
                        data: _data,
                        beforeSend: function() {

                            let barWidth = (purchaseAmount * 100 / (purchaseAmount > self.min_quantity ? purchaseAmount : self.min_quantity))
                            $('.cdg_cbar > .bar > span').width( barWidth+'%' );
                            $('#cdg_total').html(purchaseAmount);	
                            $('#cdg_form .cdg_total .loading').stop(true).fadeIn(500);
                            if(purchaseAmount >= self.min_quantity){
                                //$('.cdg_total').css('display', 'block');
                                
                                $('.cdg_cbar > .bar > span').css({'background-color': 'green'});
                                $('.cdg_cbar .pull-right__header').css({'color': 'green'});
                                $('.cdg_cbar .pull-right__header').text('Ya puedes hacer tu pedido | ');
                                $('.cdg_cbar .pull-right__slash, .cdg_cbar #cdg_total50').hide();

                            } else {
                                //$('.cdg_total').css('display', 'none');

                                $('.cdg_cbar > .bar > span').css({'background-color': 'red'});
                                $('.cdg_cbar .pull-right__header').css({'color': 'red'});
                                $('.cdg_cbar .pull-right__header').text('Cantidad mínima de pedido | ');
                                $('.cdg_cbar .pull-right__slash, .cdg_cbar #cdg_total50').show();
                            }
                        },
                        success: function( response ) {
                            //var r = response.split('---');
                            $('#cdg_form .cdg_total .loading').stop(true).fadeOut(0);

                            if(purchaseAmount >= self.min_quantity){	

                                emitter.emit('firePopulateBox', response );
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


                            } else {

                                emitter.emit('firePopulateBox', false );
                                /*
                                $('.cdg_total').css('display', 'none');
                                $('#cdg_description').val('');
                                $('#TotalPriceIVA').html(0);
                                $('#TotalPrice').html('<span class="woocommerce-Price-amount amount">0.00&nbsp;<span class="woocommerce-Price-currencySymbol">€</span></span>');
                                $('#cdg_price_u').val(0);
                                $('#cdg_total_b').val(0);
                                $('#cdg_total_c').val(0);
                                $('#cdg_price').val(0);
                                
                                $('#tabla_precios tbody').html('');
                                $('#taxesRow').html('<span class="woocommerce-Price-amount amount">0.00&nbsp;<span class="woocommerce-Price-currencySymbol">€</span></span>');
                                $('#span_price_u').html('<span class="woocommerce-Price-amount amount">0.00&nbsp;<span class="woocommerce-Price-currencySymbol">€</span></span>');
                                $('button#cdg_add_products').prop("disabled",true);
                                */
                            }

                            //total = r[0];
                            
                            _letSend = false;
                        }
                    });

                })( jQuery );
                
                
            },
            resetAll: function(){
                console.log('Reset Posts');
                this.empty = false;
                this.buildMatrix();
            }
        }
    }
</script>

<style scoped>

</style>