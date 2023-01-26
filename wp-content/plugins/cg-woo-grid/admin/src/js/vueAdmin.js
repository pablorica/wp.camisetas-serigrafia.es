import Vue from 'vue'

import Test from './templates/Test.vue';
import selectBox from './templates/selectBox.vue';
import minimumBarTable from './templates/minimumBarTable.vue';



console.log('VUE Admin loaded')


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
            self.defrows = self.rows
        })
        .catch(function (error) {
            console.log(error);
        });
    }
}

function _updateRows(self) {
    let getURL = self.apiurl+'/serialize';
    //console.log(self.frows);
    if(self.frows.length) {
        axios.post(getURL,{
            object: self.frows
        })
        .then(function (response) {
            //console.log(response);
            self.serialized = response.data
            document.getElementById(self.hiddenboxid).value = self.serialized
        })
        .catch(function (error) {
            console.log(error);
        });
    }
}

function _addRow(event, self) {
    console.log('_addRow')
    event.preventDefault()
    let row = {}
    self.rows.push(row);
}
function _deleteRow(event, self, row) {
    event.preventDefault()
    //alert('Delete ' + row)
    self.rows.splice(row, 1)
    self.updateRows()
}

export const config = {
    buildTable: _buildTable,
    updateRows: _updateRows,
    addRow:     _addRow,
    deleteRow:  _deleteRow,
};


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


    
};
