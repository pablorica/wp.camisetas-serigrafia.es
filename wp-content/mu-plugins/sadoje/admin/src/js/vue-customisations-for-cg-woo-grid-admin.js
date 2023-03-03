/*
*
* ==== DEV notes ====
*
* ---- $refs
* this line in all mounted functions:
* this.serialized = this.$refs[this.refId].value 
* is reading an HTML attribute "ref" in input types. 
* That ref attribute is not showed by some browser inspectors (Chrome), but VUE understands it. 
* It was added to the input tag in the render_settings_field() function defined in plugins/cg-woo-grid/admin/class-cg-woo-grid-admin.php l.829
* 
*/

//import selectBox from './templates/selectBox.vue';

//import { emitter } from '../../src/js/vue-customisations-for-cg-woo-grid.js';

console.log('CG_WOOGRID_PATH:' + CG_WOOGRID_PATH);


const { createApp } = Vue


function gBuildTable(self) {

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
}
function gUpdateRows(self) {
    let getURL = self.apiurl+'/serialize';
    //console.log(self.rows);
    axios.post(getURL,{
            object: self.rows
        })
        .then(function (response) {
            //console.log(response);
            self.serialized = response.data
            self.$refs[self.refId].value = self.serialized
        })
        .catch(function (error) {
            console.log(error);
        });
}
function gAddRow(event, self) {
    event.preventDefault()
    let row = {}
    self.rows.push(row);
}
function gDeleteRow(event, self, row) {
    event.preventDefault()
    //alert('Delete ' + row)
    self.rows.splice(row, 1)
    self.updateRows()
}

if(document.getElementById("formBaseprice")) {
    createApp({
        data() {
            return {
                serialized: '',
                apiurl:'',
                rows: [{
                    unitpricemin:    0,
                    unitpricemax:    0,
                    packagepricemin: 0,
                    packagepricemax: 0,
                    boxpricemin:     0,
                }],
                refId: 'customisationsforcgwoogridbudgeteerbasepricetypes'
            }
        },
        mounted: function () {
            
            let element     = this.$el;
            this.apiurl     = element.parentElement.dataset.apiurl;
            this.serialized = this.$refs[this.refId].value
            
            this.buildTable(); 

        },
        methods: {
            buildTable: function () {
                gBuildTable(this);
            },
            updateRows: function () {
                gUpdateRows(this)
            }
        }
    }).mount('#formBaseprice')
}

if(document.getElementById("formStamping")) {
    createApp({
        data() {
            return {
                serialized: '',
                apiurl:'',
                rows: [],
                refId: 'customisationsforcgwoogridbudgeteerstampingtypes',
                wording: {},
                serwording: '',
                refWording: 'customisationsforcgwoogridbudgeteerstampingwording'
            }
        },
        mounted: function () {
            
            let element     = this.$el;
            this.apiurl     = element.parentElement.dataset.apiurl;
            this.serialized = this.$refs[this.refId].value
        
            
            this.buildTable(); 

            this.serwording = this.$refs[this.refWording].value
            this.buildTableWording();

        },
        methods: {
            buildTable: function () {
                gBuildTable(this);
            },
            updateRows: function () {
                gUpdateRows(this)
            },
            addRow: function(event) {
                gAddRow(event, this)
            },
            deleteRow(row, event) {
                gDeleteRow(event, this, row)
            },
            buildTableWording: function () {
                let self   = this
                let getURL = self.apiurl+'/encode';
            
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
            updateWording: function() {
                let self   = this
                let getURL = self.apiurl+'/serialize';
                //console.log(self.rows);
                axios.post(getURL,{
                        object: self.wording
                    })
                    .then(function (response) {
                        //console.log(response);
                        self.serwording = response.data
                        self.$refs[self.refWording].value = self.serwording
                    })
                    .catch(function (error) {
                        console.log(error);
                    });
            }
        }
    }).mount('#formStamping')
}

if(document.getElementById("formImprint")) {
    createApp({
        data() {
            return {
                serialized: '',
                apiurl:'',
                rows: [],
                refId: 'customisationsforcgwoogridbudgeteerimprinttypes',
            }
        },
        mounted: function () {
            
            let element     = this.$el;
            this.apiurl     = element.parentElement.dataset.apiurl;
            this.serialized = this.$refs[this.refId].value
            
            this.buildTable(); 

        },
        methods: {
            buildTable: function () {
                gBuildTable(this);
            },
            
            sanitize: function(index) {

                let ele = this.rows[index].imprint_values;
                //console.log(ele)

                const replacements = {
                    ":": "|"
                }
                Object.keys(replacements).forEach(k => {
                    ele = ele.replace(k, replacements[k])
                })

                ele = ele.replace(/(?:\r\n|\r|\n)/g, '<br>');

                this.rows[index].imprint_values = ele;

                //console.log(ele)

                gUpdateRows(this)
            },
            updateRows: function () {
                  
                gUpdateRows(this)
            },
            addRow: function(event) {
                gAddRow(event, this)
            },
            deleteRow(row, event) {
                gDeleteRow(event, this, row)
            }
        }
    }).mount('#formImprint')
}

if(document.getElementById("formPrices")) {
    createApp({
        data() {

            return {
                serialized: '',
                apiurl:'',
                rows: [],
                //TODO:  THis must be dinamically populated. Take a look at the function addRow() in mu-plugins/sadoje/src/js/templates/Estimator.vue, it¡s loading this content via AJAX
                columns: [
                    {
                        slug:'estampacion',
                        title:'Tipo de estampación',
                        values:['Serigrafía','Bordados', 'Digital todo color']
                    },
                    {
                        slug:'tamano',
                        title:'Tamaño',
                        values:['Pequeño','Grande']
                    },
                    {
                        slug:'tintas',
                        title:'Tintas',
                        values:['1 Tinta','2 Tintas','3 Tintas','4 tintas']
                    },
                    {
                        slug:'ubicacion',
                        title:'Ubicación',
                        values:['Pecho','Espalda','Manga derecha','Manga izquierda']
                    },
                    {
                        slug:'color',
                        title:'Tipo de prenda',
                        values:['Prendas blancas','Prendas de color']
                    },
                ],
                refId: 'customisationsforcgwoogridbudgeteerpricestypes',
            }
        },
        mounted: function () {
            
            let element     = this.$el;
            this.apiurl     = element.parentElement.dataset.apiurl;
            this.serialized = this.$refs[this.refId].value 
            
            this.buildTable(); 

        },
        methods: {
            buildTable: function () {
                gBuildTable(this);
            },
            
            sanitize: function(index) {

                let ele = this.rows[index].imprint_values;
                //console.log(ele)

                const replacements = {
                    ":": "|"
                }
                Object.keys(replacements).forEach(k => {
                    ele = ele.replace(k, replacements[k])
                })

                ele = ele.replace(/(?:\r\n|\r|\n)/g, '<br>');

                this.rows[index].imprint_values = ele;

                //console.log(ele)

                gUpdateRows(this)
            },
            updateRows: function () {
                  
                gUpdateRows(this)
            },
            addRow: function(event) {
                gAddRow(event, this)
            },
            deleteRow(row, event) {
                gDeleteRow(event, this, row)
            }
        }
    }).mount('#formPrices')
}

if(document.getElementById("formProfit")) {
    createApp({
        data() {
            return {
                serialized: '',
                apiurl:'',
                rows: [],
                refId: 'customisationsforcgwoogridbudgeteerprofittypes'
            }
        },
        mounted: function () {
            
            let element     = this.$el;
            this.apiurl     = element.parentElement.dataset.apiurl;
            this.serialized = this.$refs[this.refId].value

            //console.log(this.$refs)
            //console.log(this.refId)
            //console.log(this.$refs[this.refId])

            
            this.buildTable(); 

        },
        methods: {
            buildTable: function () {
                gBuildTable(this);
            },
            updateRows: function () {
                gUpdateRows(this)
            },
            addRow: function(event) {
                gAddRow(event, this)
            },
            deleteRow(row, event) {
                gDeleteRow(event, this, row)
            }
        }
    }).mount('#formProfit')
}
