


const { createApp } = Vue

createApp({
    data() {
        return {
            message: 'Hello Vue!',
            count: 0,
        }
    },
    methods: {
        increment() {
            this.count += 1;
        },
        decrement() {
            this.count -= 1;
        }
    }
}).mount('#test')

createApp({
    data() {
        return {
            rows: [
                {
                    class: '',
                    stamping: 'Serigrafía 1 color Tamaño Pequeño 10x15 cm aprox.',
                    white_price: '0.4',
                    color_price: '0.5',
                    screen_price: '30'
                },
                {
                    class: 'alternate',
                    stamping: 'Serigrafía 2 colores Tamaño Pequeño 10x15 cm aprox.',
                    white_price: '0.6',
                    color_price: '0.7',
                    screen_price: '31'
                }
            ],
            items: [{ message: 'Foo' }, { message: 'Bar' }]
        }
    },
    mounted: function () {
 
        this.buildTable(); 
    },
    methods: {
        buildTable: function () {

            var self = this;
            // self.rows.push(row);
            //console.log(self.rows)
            //console.log(self.rows[0])

        },
        addRow: function(event) {
            event.preventDefault()
            var self = this;
            let classname = ''
            if(self.rows.length % 2 === 1) {
                classname = 'alternate'
            }
            let row = {
                class: classname,
                stamping: '',
                white_price: '',
                color_price: '',
                screen_price: ''
            }
            self.rows.push(row);
        },
        deleteRow(row, event) {
            event.preventDefault()
            var self = this;
            //alert('Delete ' + row)
            self.rows.splice(row, 1)
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