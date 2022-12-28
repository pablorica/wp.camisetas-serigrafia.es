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

    <button id="updateTotal" @click="updateTotal">Click me</button>
    <form id="matrixGrid" 
        ref="matrixForm"
    >
        <div class="loading" 
            v-bind:class = "(loading===false)?'hide':''"
        >
            <i class="fa fa-refresh fa-spin"></i>
        </div>
        
        <div class="cdg_product" 
            v-for="(attribute, key) in matrix"
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
                    <span v-for="(valueX, keyX) in attribute.x">
                        {{valueX.name}}
                        <br>
                        <input type="number" 
                            placeholder="0"
                            min="0" 
                            autocomplete="off"
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
    </form>
  </template>

<script>  
//console.log('VUE Matrix loaded')

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

            let element = this.$el;
            this.product_id = element.parentElement.dataset.currentproduct;
            //console.log(this.product_id);
            
            emitter.on('fireReset', () => {
                this.resetAll();
            });  
            emitter.on('fireUpdateAll', () => {
                this.updateTotal();
            });  

            this.buildMatrix(); 
        },
        methods: {
            buildMatrix: function () {

                var self = this;
                self.empty   = true;
                    
                if(self.product_id !== null) {

                    self.empty = false;
                    
                    var getURL = config.api.index + 
                    '/attmatrix/product_id='+self.product_id+
                    '/taxonomy_y='+self.taxonomy_y+
                    '/taxonomy_x='+self.taxonomy_x;

                    self.loading = true;
                    //console.log('loading '+ getURL);
                    
                    (function($){
                        $.get( getURL , function( r ){
                            self.loading  = false;
                            self.matrix = r;

                            //console.log(r);
                            self.matrix.map((attribute, key) => { 
                                attribute.yslug = attribute.y.toLowerCase();
                            });
                            
                        }).done(function() {
                            //setTimeout(() => {
                                //$('#phpMatrix').addClass('hide');
                            //}, 250)
                            setTimeout(() => {
                                //$('#matrixGrid').removeClass('loading');
                                self.$refs.matrixForm.reset();
                                self.quantities = {};
                            }, 500)
                            
                        }).fail(function() {
                            //console.log('woops');
                            self.loading = false;
                            self.matrix   = [];
                            self.empty   = true;
                        });
                    })( jQuery );
                }
	        },
            updateTotal: function(){
                console.log('updateTotal')

                var self = this;

                let purchaseAmount = 0, 
                    _letSend = false,
                    _data={}

                _data['product_id'] = self.product_id;
                _data['taxonomy_y'] = self.taxonomy_y;
                _data['taxonomy_x'] = self.taxonomy_x;
                let quantities = Object.assign({}, self.quantities);
                //console.log(quantities)
                const removeZeros = item => (
                    Object
                    .keys(item)
                    .filter(key => item[key] > 0 )
                    .reduce((newObj, key) => Object.assign(newObj, {[key]: item[key]}), {})
                );
                _data['quantities'] = removeZeros(quantities);
                //console.log(_data['quantities'])

                for(var ikey in _data['quantities']){
                    purchaseAmount += parseInt(_data['quantities'][ikey]);
                }
                //console.log(purchaseAmount)

                if(purchaseAmount > 0) {

                    if(_letSend !== false)	{
                        _letSend.abort();
                    }

                    var postURL = config.api.index + '/updatetotalbox';
                    
                    if(purchaseAmount >= self.min_quantity){
                        //$('.cdg_total').css('display', 'block');
                        emitter.emit('hideTotalBox');
                    } 
                    emitter.emit('fireUpdateBar', purchaseAmount );

                    _letSend = axios.post(
                        postURL,
                        _data
                    )
                    .then(function (response) {
                        console.log('then');
                        if(purchaseAmount >= self.min_quantity){	
                            response.data.addcartenabled = true;
                        } else {
                            response.data.addcartenabled = false;
                        }
                        emitter.emit('firePopulateBox', response.data );

                        _letSend = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                        //console.log('woops');
                        self.loading = false;
                        self.matrix     = [];
                        self.quantities = {};
                        self.empty      = true;

                        _letSend = false;
                    });
                    
                    /* *
                    (function($){
                        _letSend = $.post( 
                            postURL,
                            _data
                        )
                        .done(function( response ) {
                            //$('#cdg_form .cdg_total .loading').stop(true).fadeOut(0);
                                //console.log('firePopulateBox');
                                if(purchaseAmount >= self.min_quantity){	
                                    //console.log('firePopulateBox', response);
                                    response.addcartenabled = true;
                                } else {
                                    //console.log('response.addcartenabled: false');
                                    response.addcartenabled = false;
                                }
                                emitter.emit('firePopulateBox', response );

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
                            //console.log('updateTotal POST finished');
                            _letSend = false;
                        });

                    })( jQuery );
                    /* */
                }
                
                
            },
            resetAll: function(){
                this.empty = false;
                this.buildMatrix();
            }
        }
    }
</script>

<style scoped>

</style>