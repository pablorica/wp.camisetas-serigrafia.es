import Vue from 'vue'

//import Test from './templates/Test.vue';
import selectBox from './templates/selectBox.vue';
import minimumBarTable from './templates/minimumBarTable.vue';



console.log('VUE Admin loaded')

/**
 * Build the table
 * 
 * @param {Object} self 
 */
function _buildTable(self) {

    let getURL = self.apiurl+'/encode';

    if(self.serialized !== ''){

     axios.post(getURL,{
            serialized: self.serialized
        })
        .then(function (response) {
            //console.log(response);
            self.rows = response.data
            //console.log('self.rows');
            //console.log(self.rows);
        })
        .catch(function (error) {
            console.log(error);
        });
    }
}


/**
 * Updarte all rows in table
 * 
 * @param {Object} self 
 */
function _updateRows(self) {
    let getURL = self.apiurl+'/serialize';
    //console.log('_updateRows');
    //console.log(typeof self.frows);
    //console.log(self.frows);
    //console.log(self.rows);
    
    if(self.frows.length) {
        axios.post(getURL,{
            object: self.frows
        })
        .then(function (response) {
            //console.log(response);
            self.serialized = response.data
            document.getElementById(self.hiddenboxid).value = self.serialized
            self.disabletable = false;
        })
        .catch(function (error) {
            console.log(error);
        });
    } else {
        self.serialized = ''
        document.getElementById(self.hiddenboxid).value = '';
        self.disabletable = false;
    }
}

/**
 * Add new row in table
 * 
 * @param {document#event:mousedown} event
 * @param {Object} self 
 */
function _addRow(
    event, 
    self
) {
    event.preventDefault()
    //console.log('_addRow')
    //console.log(self.rows)
    //console.log('adding derow')
    //console.log(self.derow)
    self.rows.push(self.derow);
    //console.log('derow added')
    //console.log(self.rows)
}

/**
 * Delete row in table
 * 
 * @param {document#event:mousedown} event
 * @param {Object} self 
 * @param {number} rowIndex 
 */
function _deleteRow(
    event, 
    self, 
    rowIndex
) {
    event.preventDefault()
    //console.log('Delete ' + rowIndex)
    self.rows.splice(rowIndex, 1)
    self.updateRows()
}

export const config = {
    buildTable: _buildTable,
    updateRows: _updateRows,
    addRow:     _addRow,
    deleteRow:  _deleteRow,
};
//export var vueselectbox = new selectBox;


window.onload = function(event) {
    if(document.getElementById("appAdminTest")) {
        //const appTest = Vue.createApp(Test).mount('#appAdminTest')
        /*
        createApp({
            data() {
                return {
                    // Initialized to zero to begin
                    count: 0,
                }
            },
            mounted: function () {
                
                let element = this.$el;
                console.log(element)
                let refs = this.$refs;
                console.log(refs )
            },
            methods: {
                increment() {
                this.count += 1;
                },
                decrement() {
                this.count -= 1;
                }
            }
        }).mount('#appAdminTest')
        */
    }
    if(document.getElementById("appAdminSelectTest")) {
        const appTest = Vue.createApp(selectBox).mount('#appAdminSelectTest')
    }
    if(document.getElementById("tableMinimumBar")) {
        const appTest = Vue.createApp(minimumBarTable).mount('#tableMinimumBar')
    }
    if(document.getElementById("tableCustomisedStock")) {
        const appTest = Vue.createApp(minimumBarTable).mount('#tableCustomisedStock')
    }
};
