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

    <div id="matrixGrid"
        v-bind:class = "(loading===false)?'loaded':''"
        class="loading"
    >
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
                            v-bind:name="valueX.slug" 
                            value="0" 
                            min="0" 
                            max="500"
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
                loading_gif:    config.loadingif,
            	noresults:      config.noresults,
            	empty: 		    false,
                product_id:     null,
                taxonomy_y:     null,
                taxonomy_x:     null,
                matrix: 	    []
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
                    if( $("#cdg_form").length
                        && self.product_id === null
                    ){
                        self.product_id = $("#cdg_form").data('currentproduct')
                        //console.log('self.product_id: ' +self.product_id);
                    }
                    
                    
                    if(self.product_id !== null) {

                        self.empty = false;

                        if(self.taxonomy_y === null) {
                            self.taxonomy_y = 'pa_color'
                        }
                        if(self.taxonomy_x === null) {
                            self.taxonomy_x = 'pa_tamano'
                        }
                        
                        var getURL = config.api.index + '/attmatrix/product_id='+self.product_id+'/taxonomy_y='+self.taxonomy_y+'/taxonomy_x='+self.taxonomy_x;

                        self.loading = true;
                        console.log('loading '+ getURL);
                    
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