


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
        buildTable: function () {
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
                    //console.log(response);
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


createApp({
    data() {
        return {
            serialized: '',
            apiurl:'',
            rows: [
                {
                    min:      '',
                    max:      '',
                    quantity: ''
                }
            ],
        }
    },
    mounted: function () {
        
        let element     = this.$el;
        this.apiurl     = element.parentElement.dataset.apiurl;
        this.serialized = this.$refs.customisationsforcgwoogridbudgeteerprofittypes.value
        
        this.buildTable(); 

    },
    methods: {
        buildTable: function () {
            let self   = this;
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
        },
        updateRows: function () {
            let self   = this;
            let getURL = self.apiurl+'/serialize';
            //console.log(self.rows);

            axios.post(getURL,{
                    object: self.rows
                })
                .then(function (response) {
                    //console.log(response);
                    self.serialized = response.data
                    self.$refs.customisationsforcgwoogridbudgeteerprofittypes.value = self.serialized
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
            this.updateRows()
        }
    }
}).mount('#formProfit')