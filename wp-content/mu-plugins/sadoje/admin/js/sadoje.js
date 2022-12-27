


const { createApp } = Vue

createApp({
    data() {
        return {
            serialized: '',
            apiurl:'',
            rows: [
                {
                    stamping:     '',
                    white_price:  '',
                    color_price:  '',
                    screen_price: ''
                }
            ],
            items: [{ message: 'Foo' }, { message: 'Bar' }]
        }
    },
    mounted: function () {
        
        let element     = this.$el;
        this.apiurl     = element.parentElement.dataset.apiurl;
        this.serialized = this.$refs.customisationsforcgwoogridbudgeteerstampingtypes.value
        
        this.buildTable(); 

    },
    methods: {
        buildTable: async function () {
            let self   = this;
            let getURL = self.apiurl+'/encode';

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
        },
        updateStampings: function () {
            let self   = this;
            let getURL = self.apiurl+'/serialize';
            console.log(self.rows);

            axios.post(getURL,{
                object: self.rows
            })
            .then(function (response) {
                console.log(response);
                //self.rows = response.data
                self.serialized = response.data
                self.$refs.customisationsforcgwoogridbudgeteerstampingtypes.value = self.serialized
            })
            .catch(function (error) {
                console.log(error);
            });
        },
        addRow: function(event) {
            event.preventDefault()
            var self = this;

            let row = {
                stamping:     '',
                white_price:  '',
                color_price:  '',
                screen_price: ''
            }
            self.rows.push(row);
        },
        deleteRow(row, event) {
            event.preventDefault()
            var self = this;
            //alert('Delete ' + row)
            self.rows.splice(row, 1)
            this.updateStampings()
        }
    }
}).mount('#formStamping')





function changeSelectValue(fieldID) {

    console.log('changeSelectValue')
    console.log(fieldID)

    let newValue = document.getElementById(fieldID +'_title').value;
    let sel = document.getElementById(fieldID);
    let str = sel.options[sel.selectedIndex].value;

    var regex = /("(.*?)")/g;
    var matches = str.match(regex);

    if (matches) {
        var lastMatch = matches[matches.length - 1];
        str = str.replace( lastMatch,'"'+newValue+'"');
        console.log(str)
        sel.options[sel.selectedIndex].value = str
    }
}

function changeTextValue(fieldID) {
    let sel = document.getElementById(fieldID);
    document.getElementById(fieldID +'_title').value = sel.options[sel.selectedIndex].text;
}