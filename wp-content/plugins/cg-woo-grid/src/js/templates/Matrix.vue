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
            v-for="(attribute, keyY) in matrix"
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
                    <span
                      v-for="(valueX, keyX) in attribute.x"
                      :key="keyX"
                      @mouseover="showStockMessage = { keyX: keyX, keyY: keyY }"
                      @mouseleave="showStockMessage = { keyX: null, keyY: null }"
                    >
                        {{valueX.name}}
                        <br>
                        <input type="number"
                            placeholder="0"
                            min="0" 
                            :max="valueX.stock >= 0 ? valueX.stock : 0"
                            autocomplete="off"
                            v-model="quantities[valueX.slug]"
                            @change="checkifAllowedUpdates(quantities[valueX.slug],valueX.stock, valueX.slug, keyX, keyY)"
                            :class="{invalid:quantities[valueX.slug] > valueX.stock}"
                               :disabled="valueX.stock === 0 || (disableStock.keyX === keyX && disableStock.keyY === keyY)"
                        >
                        <div
                          class="invalid-feedback"
                          :class="{show:((quantities[valueX.slug] && quantities[valueX.slug] > valueX.stock) ||
                          (valueX.stock === 0 && showStockMessage.keyX === keyX && showStockMessage.keyY === keyY))}"
                        >
                            <div class="arrow"></div>
                            <span v-html="valueX.messageStock"
                                :class="{show:quantities[valueX.slug] > valueX.stock && valueX.stock !== 0}"
                            ></span>
                            <span
                              v-html="messagezero"
                              :class="{show:showStockMessage}"
                            ></span>
                        </div>
                        <br/>
                        <i v-if="valueX.showStock">
                          <small v-if="valueX.stock > 0" v-html="valueX.stock"></small>
                          <small v-else>Fuera de stcok</small>
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
                loading: 	        false,
                noresults:          config.noresults,
                messagezero:        config.stock_zero_message,
                empty: 		        false,
                disableAll:         false,
                product_id:         null,
                taxonomy_y:         config.taxonomy_y,
                taxonomy_x:         config.taxonomy_x,
                min_quantity:       0,
                matrix: 	        [],
                quantities: 	    {},
                cookieName:         "cgWooGrid",
                showStockMessage: {
                keyX: null,
                keyY: null
                },
                disableStock: {
                  keyX: null,
                  keyY: null
                }
            }
        },
        mounted: function () {

            let element = this.$el;
            this.product_id = element.parentElement.dataset.currentproduct;
            //console.log(this.product_id);
            if (typeof element.parentElement.dataset.customminimum !== 'undefined') {
                this.min_quantity = element.parentElement.dataset.customminimum
            }
            
            emitter.on('fireReset', () => {
                this.resetAll();
            });  
            emitter.on('fireUpdateAll', () => {
                this.updateTotal();
            });  

            //Remove the cookie (if exists)
            this.removeCookie();
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
            checkifAllowedUpdates: function(
                quantity, 
                stock,
                slug,
                keyX,
                keyY
            ){
                var self = this;
                //console.log('checking');

                if(quantity > stock) {
                    //console.log('no allowed');
                    // self.disableAll = true
                  if (stock > 0) {
                    self.quantities[slug] = stock
                    self.updateTotal();
                  } else {
                    self.quantities[slug] = null
                    self.disableStock = { keyX, keyY }
                  }
                } else {
                    //console.log('allowed');
                    self.disableAll = false,
                    self.updateTotal();
                }
            },
            updateTotal: function(){
                var self = this;

                if(!self.disableAll) {

                    let purchaseAmount = 0, 
                    _letSend = false,
                    _data={}

                    _data['product_id'] = self.product_id;
                    _data['taxonomy_y'] = self.taxonomy_y;
                    _data['taxonomy_x'] = self.taxonomy_x;
                    let quantities = Object.assign({}, self.quantities);

                    const removeZeros = item => (
                        Object
                        .keys(item)
                        .filter(key => item[key] > 0 )
                        .reduce((newObj, key) => Object.assign(newObj, {[key]: item[key]}), {})
                    );
                    _data['quantities'] = removeZeros(quantities);

                    for(var ikey in _data['quantities']){
                        purchaseAmount += parseInt(_data['quantities'][ikey]);
                    }
                    console.log('Plugin: total products '+purchaseAmount)

                    self.updateCookie({  totalProducts: purchaseAmount }) 

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
                            //console.log('then');
                            if(purchaseAmount >= self.min_quantity){	
                                response.data.addcartenabled = true;
                            } else {
                                response.data.addcartenabled = false;
                            }
                            emitter.emit('firePopulateBox', response.data );

                            self.updateCookie({ newdescription: response.data.description }) 

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
                    else if(purchaseAmount === 0) {
                        self.setToZero() 
                    }
                }

                
            },
            updateCookie: function(cookieObj) {
                let self = this
                //$cookies.set(keyName, value[, expires[, path[, domain[, secure[, sameSite]]]]])
                //console.log('Is cookie? '+window.$cookies.isKey(self.cookieName))
                if(window.$cookies.isKey(self.cookieName)) {
                    let oldCookieObj = window.$cookies.get(self.cookieName);
                    //console.log(oldCookieObj)

                    if(typeof cookieObj.newdescription !== 'undefined') {

                        if(typeof oldCookieObj.description !== 'undefined') {
                            oldCookieObj.description[0] = cookieObj.newdescription
                        } else {
                            cookieObj.description = [cookieObj.newdescription]
                        }

                        delete cookieObj.newdescription
                    }


                    cookieObj = {...oldCookieObj,...cookieObj};
                } 

                const cookieJSON = JSON.stringify(cookieObj);
                window.$cookies.set(self.cookieName,cookieJSON);
                console.log('Plugin cookie')
                console.log(window.$cookies.get(self.cookieName))
            },
            removeCookie: function() {
                let self = this
                console.log('Remove cookie')
                window.$cookies.remove(self.cookieName);
            },
            setToZero: function(){
                this.empty = false;
                this.removeCookie();
                //this.buildMatrix();
                emitter.emit('fireResetBar');
                emitter.emit('fireResetTotalBox');
            },
            resetAll: function(){
                this.empty = false;
                this.removeCookie();
                this.buildMatrix();
                emitter.emit('fireResetBar');
                emitter.emit('fireResetTotalBox');
            }
        }
    }
</script>

<style scoped>

</style>
