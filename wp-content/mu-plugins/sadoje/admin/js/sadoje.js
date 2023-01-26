


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
