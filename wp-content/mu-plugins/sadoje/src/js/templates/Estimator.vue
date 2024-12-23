<template>
    <table class="cdg_table">
        <tbody>
            <tr v-for="(row, rindex) in rows"
                v-bind:id="'row_'+rindex" 
                v-bind:class = "[(rindex % 2 == 1)?'':'alternate', (loading===true && rindex == rows.length - 1)?'rowloading':''] "
            >
                <td style="text-align:right">
                    <table>
                        <tr>
                            <td v-for="(column, cindex) in columns[rindex]">
                                {{column.title}}
                                <select v-model="row[cindex]"
                                    @change="updateSelects(rindex,cindex,column.slug)"
                                    >
                                    <option disabled 
                                        value="">
                                        Seleccione un valor
                                    </option>
                                    <option v-for="(option, vindex) in column.options" 
                                        :value="option"
                                    >{{option}}</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td :colspan="columns[rindex]?columns[rindex].length:4">
                                <label>Subir archivo
                                    <input type="file" 
                                        @change="handleFileUpload( $event, rindex )"
                                        accept="image/png, image/jpeg"
                                    />
                                </label>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="text-align:right">
                    <button class="button button-secondary button-delete" 
                        style="background:#DC3232;color:#FFF;border-color:#FFF;"
                        @click="deleteRow(rindex, $event)"
                    >Borrar</button>
                </td>
            </tr>
            <tr v-bind:class = "(loading===false)?'hide':''">
                <th colspan='2'>
                    <div class="loading">
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                </th>
            </tr>
            
        </tbody>
        <tfoot>
            <tr>
                <th colspan='2'>
                    <p style="text-align:right">
                        <button class="button button-secondary" 
                            @click="addRow($event)"
                        >Añadir personalización</button>
                    </p>
                </th>
            </tr>
        </tfoot>
    </table>
    <table class="cdg_table">
        <tr>
            <th>{{baggedtitle}} </th>
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
    </table>
</template>

<script>  
console.log('customisations-for-cg-woo-grid: VUE Estimator loaded')
import { emitter } from '../vue-customisations-for-cg-woo-grid.js';


export default {
    name: 'estimator',
    data() {
        return {
            apiurl:             '',
            loading:            false,
            serwording:         '',
            wording:            {},
            prices:             '',
            selectoptions:      [],
            selectoptionsinrow: [],
            rows:               [],
            columns:            [],
            indexFront:         '',
            areaFront:          '',
            indexRear:          '',
            areaRear:           '',
            bagged:             0,
            baggedtitle:        '',
            baggedwording:      '',
            cookieName:         "cgWooGrid",
            totalProducts:      0
        }
    },
    mounted: function () {

        let element        = this.$el;
        this.apiurl        = element.parentElement.dataset.apiurl;
        this.imprint       = element.parentElement.dataset.imprint;
        this.prices        = element.parentElement.dataset.prices;
        

        this.serwording    = element.parentElement.dataset.wording;

        this.baggedprice   = element.parentElement.dataset.baggedprice;
        this.baggedtitle   = element.parentElement.dataset.baggedtitle;
        this.baggedwording = element.parentElement.dataset.baggedwording;
        
        this.buildEstimator(); 

        let self = this
        document.getElementById("cdg_add_products").addEventListener(
            'click',
            function() {
                self.submitFile()
                
            }
        )

        
    },
    methods: {

        buildEstimator: function() {
            //console.log('buildEstimator');
            let self   = this
            let getURL = self.apiurl+'/encode';

            if(self.prices !== ''){

                //console.log('prices');
                //console.log(self.prices);

                axios.post(getURL,{
                        serialized: self.prices
                    })
                    .then(function (response) {
                        //console.log(response);
                        self.selectoptions = response.data
                        //console.log(response.data);
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
        updateSelects: function(
            rowindex,
            colindex,
            colslug
        ) {
            let self   = this
            //console.log("updateSelects row:"+rowindex+" col:"+colindex+" "+colslug);
            //console.log("selected "+colslug+": "+self.rows[rowindex][colindex]);
            let nextColumnIndex = colindex + 1;
            if(nextColumnIndex < self.columns[rowindex].length) {
                //delete values of selects on the right of the current element, if any
                for( let i = nextColumnIndex; i < self.columns[rowindex].length; i++) {
                    console.log("deleting currentColumns id "+rowindex+" options "+i);
                    self.columns[rowindex][i].options = []
                    console.log("deleting self.rows id "+rowindex+"  values "+i);
                    self.rows[rowindex][i] = null;
                }
                
                //Set the values of the current select
                console.log('next column:');
                console.log(nextColumnIndex);
                let nextColumn = self.columns[rowindex][nextColumnIndex];
                console.log('next column slug');
                console.log(nextColumn.slug);

                if(self.columns[rowindex][colindex].all) {
                    // The current select can have any value, 
                    // we must use one of the previous select to filter the values
                    colslug  = self.columns[rowindex][colindex].all.slug
                    colindex = self.columns[rowindex][colindex].all.index
                }

                console.log('options to filter');
                console.log(self.selectoptionsinrow);
                console.log('filter by');
                console.log(colslug);
                
                let selected = self.selectoptionsinrow.filter(function (el) {
                    return el[colslug] == self.rows[rowindex][colindex];
                });
                console.log('filtered otions');
                console.log(selected);
                if(!selected.length) {
                    
                    // Filter again but this time using the original options value, 
                    // becasue the current row options was returning false
                    console.log('default options to filter');
                    console.log(self.selectoptions);


                    selected = self.selectoptions.filter(function (el) {
                        return el[colslug] == self.rows[rowindex][colindex];
                    });
                }
                self.selectoptionsinrow = selected;

                //Add prices by color to row
                self.rows[rowindex].pricecolors = [];
                self.selectoptionsinrow.forEach(function(selrow){
                    self.rows[rowindex].pricecolors.push({
                        color: selrow.color,
                        pricecloth: selrow.pricecloth,
                        priceclothval: selrow.priceclothval,
                        pricescreen: selrow.pricescreen,
                        pricescreenval: selrow.pricescreenval
                    }); 
                });
                
                let nextuniqueoptions = [...new Set(selected.map(item => item[nextColumn.slug]))];
                
                console.log('next column values');
                console.log(nextuniqueoptions);
                if(nextuniqueoptions[0] === "all") {
                    nextColumn.options = self.columns[rowindex][nextColumnIndex].default
                    nextColumn.all    = {'index':colindex, 'slug':colslug}
                } else {
                    nextColumn.options = nextuniqueoptions;
                }
                self.columns[rowindex][nextColumnIndex] = nextColumn;
            }
            //console.log("Current saved columns");
            //console.log(self.columns);
            self.changeCookie();
        },
        addRow: function(event) {
            event.preventDefault()
            let self = this
            self.loading = true;

            let getURL = self.apiurl+'/encode';

            if(self.imprint !== ''){

                console.log('imprint');
                console.log(self.imprint);

                axios.post(getURL,{
                        serialized: self.imprint
                    })
                    .then(function (response) {
                        //console.log(response);
                        //self.columns.push(response.data)
                        //console.log(response.data);
                        let defaultSelects = []; 
                        for (let i = 0; i < response.data.length; i++) {
                            const item = response.data[i];

                            const defaultValues = item.imprint_values
                                                        .split('|')
                                                        .map(function(ele) {
                                                            return ele.trim();
                                                        });
                            if(item.imprint_hide) {
                                continue;
                            }

                            if(item.imprint_unpublish) {
                                continue;
                            }


                            let imprintObject = {
                                slug:       item.imprint,
                                title:      item.imprint_label,
                                default:    defaultValues,   
                                options:    [],
                                all:        false
                            }
                            if(i === 0) {
                                imprintObject.options = defaultValues 
                            }

                            defaultSelects.push(imprintObject);
                        }

                        //console.log('defaultSelects', defaultSelects);
                        self.columns.push(defaultSelects);
                        //console.log('self.columns', self.columns);
                        self.loading = false;
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }

            self.selectoptionsinrow = self.selectoptions

            //Default columns
            /*
            self.columns.push([
                {
                    slug:'estampacion',
                    title:'Tipo de estampación',
                    default:['Serigrafía','Bordados', 'Digital todo color'],
                    options:['Serigrafía','Bordados', 'Digital todo color'],
                    all:null
                },
                {
                    slug:'tamano',
                    title:'Tamaño',
                    default:['Pequeño','Grande'],
                    options:[],
                    all:false
                },
                {
                    slug:'tintas',
                    title:'Tintas',
                    default:['1 Tinta','2 Tintas','3 Tintas','4 tintas'],
                    options:[],
                    all:false
                },
                {
                    slug:'ubicacion',
                    title:'Ubicación',
                    default:['Pecho','Espalda','Manga derecha','Manga izquierda'],
                    options:[],
                    all:false
                }
            ]);
            */
            self.rows.push({});

            console.log("Current created columns");
            console.log(self.columns);
        },
        deleteRow: function(rowid, event) {
            event.preventDefault()
            let self = this
            //alert('Delete ' + row)
            self.rows.splice(rowid, 1)
            self.columns.splice(rowid, 1)
            //self.updateRows()
            console.log("Current saved rows");
            console.log(self.rows);
            self.changeCookie();
        },
        resetEstimator: function() {
            this.rows = []
            this.columns= []
            this.changeCookie();
        },
        changeCookie: function() {
            let self      = this
            let cookieObj = {}

            console.log("Current saved rows");
            console.log(self.rows);
            if(typeof self.columns[0] !== 'undefined') {
                let slugs = self.columns[0].map(a => a.slug);
                console.log('addOn new changeCookie')
                //console.log('slugs:')
                //console.log(slugs);
                cookieObj.stamping = [];
                self.rows.forEach(function(row){
                    let rowkeys = Object.keys(row);
                    let rowvalues = Object.values(row);
                    console.log('rowkeys:')
                    console.log(rowkeys)
                    console.log('rowvalues:')
                    console.log(rowvalues)
                    let stamping = {};
                    rowkeys.forEach(function(key){
                        //console.log('slug: '+ slugs[key])
                        //console.log('value: '+ rowvalues[key])
                        if( typeof slugs[key] !== 'undefined'){
                            stamping[slugs[key]] = rowvalues[key]
                        }
                    })

                    stamping.pricecolors = row.pricecolors

                    if(typeof row.imageid !== 'undefined' ) {
                        stamping.imageid = row.imageid
                    }

                    cookieObj.stamping.push(stamping); 
                    
                });
            }
            if(self.bagged > 0 ) {
                cookieObj.bagged = {
                    baggedtitle: self.baggedtitle,
                    baggedprice: self.baggedprice
                }
            } else {
                 cookieObj.bagged = null
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

            console.log('current cookie object');
            console.log(cookieObj);

            //console.log('Is cookie? '+window.$cookies.isKey(self.cookieName))
            /*
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
            */
           if(window.$cookies.isKey(self.cookieName)) {
                let oldCookieObj = window.$cookies.get(self.cookieName);
                cookieObj = {...oldCookieObj,...cookieObj};
           }

            const cookieJSON = JSON.stringify(cookieObj);
            window.$cookies.set(self.cookieName,cookieJSON);
            console.log('AddOn cookie')
            console.log(window.$cookies.get(self.cookieName))

            /*  TODO this works and trigers the updateTotal function, 
            but it would be better to find a way to triger that function 
            using VUE elements , or tiny-emitter */
            document.getElementById("updateTotal").click();

            /*{
  "stamping": [
    {
      "estampacion": "Serigrafía",
      "tintas": "1 Tinta",
      "tamano": "Pequeño",
      "ubicacion": "Pecho",
      "pricecolors": [
        {
          "color": "Prendas blancas",
          "pricecloth": true,
          "priceclothval": "0.5",
          "pricescreen": true,
          "pricescreenval": "30"
        },
        {
          "color": "Prendas de color",
          "pricecloth": true,
          "priceclothval": "0.6",
          "pricescreen": true,
          "pricescreenval": "30"
        }
      ]
    }
  ],
  "totalProducts": 40
}
            */
            


            
            /*
            {\"totalProducts\":40,
            \"bagged\":0,
            \"front\":{\"screenPrice\":30,\"stamping\":\"Serigrafía 1 color\",\"area\":\"Tamaño Pequeño 10x15 cm aprox.\",\"whitePrice\":0.5,\"colorPrice\":0.6},
            \"description\":[[\"\",\"\\nEstampación en pecho (Serigrafía 1 color undefined) \"],\"\\nEstampación en pecho (Serigrafía 1 color Tamaño Pequeño 10x15 cm aprox.) \"]}
            */
        },
        handleFileUpload( event, rindex ){
            this.rows[rindex].file = event.target.files[0];
        },
        /**
         * @see https://serversideup.net/uploading-files-vuejs-axios/
         * @see https://github.com/serversideup/uploading-files-vuejs-axios/blob/main/src/components/SingleFile.vue
         */
        submitFile(){
            
            let self = this;
            let getURL = this.apiurl+'/upload-image';
            
            this.rows.forEach(function(row){
                if(row.file) {
                    
                    let formData = new FormData();
                    
                    formData.append('file', row.file);
                    formData.append( 'title', 'testitle');
                    formData.append( 'caption', 'testcaption');
                    
                    axios.post( getURL,
                        formData,
                        {
                            headers: {
                                    'Content-Type': 'multipart/form-data'
                            }
                        }
                    ).then(function(response){
                        row.imageid = response.data
                        console.log('Image uploaded');
                        console.log(response.data);
                        self.changeCookie()
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
                }
            });
            //TODO:  Reset the estimator once the cookie is updated with the images IDs
            // Also, be sure that the image ID (and image URL) is added to the Invoice and cart descripption
            //this.resetEstimator()
            
        },
        changeCookieDEPRECATED: function() {
            let self      = this
            let cookieObj = {}
            console.log('addOn changeCookie')
            cookieObj.descStamping = '';
            cookieObj.baseprices   = self.baseprices;

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

            /*  TODO this works and trigers the updateTotal function, 
            but it would be better to find a way to triger that function 
            using VUE elements , or tiny-emitter */
            document.getElementById("updateTotal").click();
        }
    }
}
</script>

<style scoped>

</style>