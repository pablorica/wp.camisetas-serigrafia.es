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
                        <br/>
                        <i>
                            <small v-html="valueX.stock"></small>
                        </i>
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
                taxonomy_y:     config.taxonomy_y,
                taxonomy_x:     config.taxonomy_x,
                min_quantity:   config.min_quantity,
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
                    _data['quantities'] = Object.assign({}, self.quantities);

                    //console.log('quantities: ');
                    for(var ikey in _data['quantities']){
                        //console.log('input slug: ',ikey);
                        //console.log('input value: ',_data['quantities'][ikey]);
                        purchaseAmount += parseInt(_data['quantities'][ikey]);
                    }

                    if(_letSend !== false)	{
                        _letSend.abort();
                    }

                    var postURL = config.api.index + '/updatetotalbox';

                    //console.log('updateTotal');
                    //console.log(postURL);
                    //console.log(_data);

                    let barWidth = (purchaseAmount * 100 / self.min_quantity)                    
                    if(purchaseAmount >= self.min_quantity){
                        //$('.cdg_total').css('display', 'block');
                        emitter.emit('hideTotalBox');
                        barWidth = 100;
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

                    $('.cdg_cbar > .bar > span').width( barWidth+'%' );
                    
                    _letSend = $.post( 
                        postURL,
                        _data
                    )
                    .done(function( response ) {
                        //$('#cdg_form .cdg_total .loading').stop(true).fadeOut(0);
                            //console.log('firePopulateBox');
                            if(purchaseAmount >= self.min_quantity){	
                                //console.log('firePopulateBox', response);
                                emitter.emit('firePopulateBox', response );
                            } else {
                                console.log('firePopulateBox: false');
                                emitter.emit('firePopulateBox', false );
                            }

                            //total = r[0];
                    })
                    .fail(function() {
                        console.log('woops');
                        self.loading = false;
                        self.matrix     = [];
                        self.quantities = {};
                        self.empty      = true;
                    })
                    .always(function() {
                        console.log('updateTotal POST finished');
                        _letSend = false;
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