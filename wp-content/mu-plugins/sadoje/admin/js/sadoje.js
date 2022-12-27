


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

createApp({
    data() {
        return {
            serialized: '',
            apiurl:'',
            rows: [],
            refId: 'customisationsforcgwoogridbudgeteerstampingtypes'
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
        },
        addRow: function(event) {
            gAddRow(event, this)
        },
        deleteRow(row, event) {
            gDeleteRow(event, this, row)
        }
    }
}).mount('#formStamping')


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