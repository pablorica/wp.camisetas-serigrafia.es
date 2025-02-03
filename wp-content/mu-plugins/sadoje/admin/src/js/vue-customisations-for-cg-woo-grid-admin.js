/*
*
* ==== DEV notes ====
*
* ---- $refs
* this piece of code in all mounted functions: << this.serialized = this.$refs[this.refId].value >>
* is reading an HTML attribute "ref" in input types.
* That ref attribute is not showed by some browser inspectors (Chrome), but VUE understands it.
* It was added to the input tag in the render_settings_field() function defined in plugins/cg-woo-grid/admin/class-cg-woo-grid-admin.php l.829
*
*/

//import selectBox from './templates/selectBox.vue';

//import { emitter } from '../../../src/js/vue-customisations-for-cg-woo-grid.js';

//console.log('CG_WOOGRID_PATH:' + CG_WOOGRID_PATH);

const { createApp } = Vue


function phpUnserialize(serializedString) {
    const parsePhpArray = (input) => {
        const result = [];
        const regex = /([aisb]):(\d+):(?:\{([^}]*)\}|(?:"([^"]*)";)|(;))|b:(0|1);/g;
        let match;

        while ((match = regex.exec(input)) !== null) {
            const type = match[1] || (match[0].startsWith('b:') ? 'b' : null); // Handle "b" type
            const size = parseInt(match[2], 10);
            const nested = match[3];
            const stringValue = match[4];
            const emptyValue = match[5];
            const booleanValue = match[6];

            //console.log('type:', type);

            if (type === 'a') {
                // Array
                result.push(parsePhpArray(nested));
            } else if (type === 's') {
                // String
                result.push(stringValue);
            } else if (type === 'i') {
                // Integer
                result.push(size);
            } else if (type === 'b') {
                // Boolean
                result.push(booleanValue === '1');
            } else if (emptyValue !== undefined) {
                result.push(null);
            }
        }

        return result;
    };

    try {
        return parsePhpArray(serializedString);
    } catch (error) {
        console.error('Error deserializing data:', error);
        return [];
    }
}
function gBuildPriceTable(self) {
    let getURL = self.apiurl+'/encode';
    axios.post(getURL,{
        serialized: self.serialized
    })
        .then(function (response) {
            self.priceTable = response.data;
            self.priceTable.category.forEach((categoryGroup, i) => {
                self.selectedCategories.push([]);
                categoryGroup.categories.forEach((category) => {
                    self.selectedCategories[i].push({ id: category.id, name: category.name });
                });
            });
            self.priceTable.product.forEach((productGroup, i) => {
                self.selectedProducts.push([]);
                productGroup.products.forEach((product) => {
                    self.selectedProducts[i].push({id: product.id, title: product.title});
                });
            });
        })
        .catch(function (error) {
            console.log(error);
        });
}
async function gBuildProfitTable(self) {
    let getURL = self.apiurl+'/encode';
    if(self.serialized !== '') {

        await axios.post(getURL,{
            serialized: self.serialized
        })
            .then(function (response) {
                if (!Object.keys(response.data).includes('general')) {
                    self.profitTable.general = response.data;
                    gUpdateProfitRows(self);
                } else {
                    self.profitTable = response.data;
                    self.profitTable.category.forEach((categoryGroup, i) => {
                        self.selectedCategories.push([]);
                        categoryGroup.categories.forEach((category) => {
                            self.selectedCategories[i].push({ id: category.id, name: category.name });
                        });
                    });
                    self.profitTable.product.forEach((productGroup, i) => {
                        self.selectedProducts.push([]);
                        productGroup.products.forEach((product) => {
                            self.selectedProducts[i].push({id: product.id, title: product.title});
                        });
                    });
                }
            })
            .catch(function (error) {
                console.log(error);
            });
    }
}
async function gBuildTable(self) {
    let getURL = self.apiurl+'/encode';
    if(self.serialized !== ''){

        await axios.post(getURL,{
            serialized: self.serialized
        })
            .then(function (response) {
                function validateAndCleanRows(rows, columns) {
                    // Convert columns to a map for easy access
                    const columnMap = columns.reduce((map, column) => {
                        map[column.slug] = column;
                        return map;
                    }, {});

                    // Iterate over rows and clean up
                    return rows.map(row => {
                        const cleanedRow = { ...row }; // Copy the row

                        Object.keys(row).forEach(key => {
                            // Skip sticky values
                            if(cleanedRow[key] === 'all') {
                                return;
                            }
                            //console.log('Key:', key);
                            if (['pricecloth', 'priceclothval', 'pricescreen', 'pricescreenval'].includes(key)) {
                                return;
                            }

                            const column = columnMap[key];

                            // If column doesn't exist or is unpublished, handle accordingly
                            if (!column
                                || column.imprint_unpublish === 1
                            ) {
                                // Remove the invalid key
                                delete cleanedRow[key];
                                // console.log(`Delete `+ cleanedRow[key]);
                            } else if (
                                column
                                && column.values
                                && !column.values.includes(row[key])
                            ) {
                                // If the value is not in the allowed values for the column, clear it
                                cleanedRow[key] = '';
                                // console.log(`Empty `+ cleanedRow[key]);
                            }
                        });

                        return cleanedRow;
                    });
                }
                //check if self.columns exists
                if (typeof self.columns !== 'undefined' ) {

                    self.rows = validateAndCleanRows(response.data, self.columns);
                    //console.log('Rows :', self.rows);
                    //console.log('Columns :', self.columns);
                } else {
                    self.rows = response.data
                }
            })
            .catch(function (error) {
                console.log(error);
            });
    }
}
async function gUpdatePriceTableColumns(self) {
    let getURL = self.apiurl+'/serialize';
    await axios.post(getURL,{
        object: self.priceTable
    })
        .then(function (response) {
            self.serialized = response.data
            self.$refs[self.refId].value = self.serialized
        })
        .catch(function (error) {
            console.log(error);
        });
}
async function gUpdateProfitRows(self) {
    let getURL = self.apiurl+'/serialize';
    await axios.post(getURL,{
        object: self.profitTable
    })
        .then(function (response) {
            self.serialized = response.data
            self.$refs[self.refId].value = self.serialized
        })
        .catch(function (error) {
            console.log(error);
        });
}
function gUpdateRows(self) {
    let getURL = self.apiurl+'/serialize';
    //console.log('Rows :', self.rows);
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
function gAddRow(event, self, type,index) {
    event.preventDefault();
    switch (type) {
        case 'category':
            self.profitTable.category[index].rows.push({});
            break;
        case 'product':
            self.profitTable.product[index].rows.push({});
            break;
        case 'general':
            self.profitTable.general.push({});
            break;
        default:
            self.rows.push({});
            break;
    }
}
function gDeleteRow(event, self, row, type, index) {
    event.preventDefault();
    switch (type) {
        case 'category':
            self.profitTable.category[index].rows.splice(row, 1);
            break;
        case 'product':
            self.profitTable.product[index].rows.splice(row, 1);
            break;
        case 'general':
            self.profitTable.general.splice(row, 1);
            break;
        default:
            self.rows.splice(row, 1);
            break;
    }
    self.updateRows();
}
async function gSearchProducts(self) {
    clearTimeout(self.debounceTimeout);
    self.debounceTimeout = setTimeout(() => {
        let getURL = `/index.php/wp-json/wp/v2/search/?subtype=product&page=1&per_page=20&search=${self.searchProduct}`;
        axios.get(getURL).then((response) => {
            self.products = response.data;
        }).catch(function (error) {
            console.log(error);
        });
    }, 500);
}
async function gSearchCategories(self) {
    clearTimeout(self.debounceCategoryTimeout);
    self.debounceCategoryTimeout = setTimeout(() => {
        let getURL = `/index.php/wp-json/wp/v2/product_cat/?joint&page=1&per_page=50&name=${self.searchCategory}`;
        axios.get(getURL).then((response) => {
            self.categories = response.data;
        }).catch(function (error) {
            console.log(error);
        });
    }, 500);
}
async function selectItem(type, self, item, index) {
    switch (type) {
        case 'category':
            if (!self.selectedCategories[index].some(c => c.id === item.id)) {
                self.selectedCategories[index].push({ id: item.id, name: item.name });
            }
            break;
        case 'product':
            if (!self.selectedProducts[index].some(p => p.id === item.id)) {
                self.selectedProducts[index].push({ id: item.id, title: item.title });
            }
            break;
    }
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
                columns: [],
                refId: 'customisationsforcgwoogridbudgeteerpricestypes',
            }
        },
        mounted: function () {

            function transformToDesiredFormat(deserializedData) {
                return deserializedData
                    .filter((item) => {
                        if (Array.isArray(item)) {
                            const result = {};
                            for (let i = 0; i < item.length; i += 2) {
                                const key = item[i];
                                const value = item[i + 1];
                                result[key] = value;
                            }

                            // Exclude items with 'imprint_unpublish'
                            if ('imprint_unpublish' in result
                                && result.imprint_unpublish === true
                            ) {
                                return false; // Filter out this item
                            }

                        }
                        return true; // Keep the item
                    })
                    .map((item) => {
                        if (Array.isArray(item)) {
                            const result = {};
                            for (let i = 0; i < item.length; i += 2) {
                                const key = item[i];
                                const value = item[i + 1];
                                result[key] = value;
                            }

                            //console.log('result:', result);

                            return {
                                slug: result.imprint || '',
                                title: result.imprint_label || '',
                                values: result.imprint_values ? result.imprint_values.split('|').map((v) => v.trim()) : [],
                            };
                        }
                        return {
                            slug: '',
                            title: '',
                            values: [],
                        };
                    });
            }

            let element     = this.$el;
            this.apiurl     = element.parentElement.dataset.apiurl;
            this.serialized = this.$refs[this.refId].value;

            // Fetch the serialized data from the hidden input
            const columnsInput = document.getElementById('customisations-for-cg-woo-grid_budgeteer_imprint_types');
            if (columnsInput) {

                //console.log(columnsInput.value)

                /*
                // Example usage
                const serializedString = 'a:5:{i:0;a:3:{s:7:"imprint";s:11:"estampacion";s:13:"imprint_label";s:20:"Tipo de estampación";s:14:"imprint_values";s:43:"Serigrafía | Bordados | Digital todo color";}i:1;a:4:{s:7:"imprint";s:6:"tintas";s:13:"imprint_label";s:6:"Tintas";s:14:"imprint_values";s:40:"1 Tinta | 2 Tintas | 3 Tintas | 4 tintas";s:17:"imprint_unpublish";b:1;}i:2;a:3:{s:7:"imprint";s:6:"tamano";s:13:"imprint_label";s:7:"Tamaño";s:14:"imprint_values";s:17:"Pequeño | Grande";}i:3;a:3:{s:7:"imprint";s:9:"ubicacion";s:13:"imprint_label";s:10:"Ubicación";s:14:"imprint_values";s:49:"Pecho | Espalda | Manga derecha | Manga izquierda";}i:4;a:4:{s:7:"imprint";s:5:"color";s:13:"imprint_label";s:14:"Tipo de prenda";s:14:"imprint_values";s:34:"Prendas blancas | Prendas de color";s:12:"imprint_hide";b:1;}}';
                const deserializedData = phpUnserialize(serializedString);
                const formattedData = transformToDesiredFormat(deserializedData);

                console.log(formattedData);
                */

                try {
                    const deserializedData = phpUnserialize(columnsInput.value);
                    const formattedData = transformToDesiredFormat(deserializedData);
                    //console.log('Deserialized data:', deserializedData);
                    //console.log('FormattedData data:', formattedData);
                    this.columns = formattedData;
                    //console.log('this.columns:', this.columns);
                } catch (error) {
                    console.error('Error deserializing data:', error);
                }

            }

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

if (document.getElementById("formProfit")) {

    createApp({
        data() {
            return {
                serialized: '',
                apiurl: '',
                profitTable: {
                    general: [],
                    category: [],
                    product: []
                },
                searchCategory: '',
                categories: [],
                selectedCategories: [[]],
                searchProduct: '',
                products: [],
                selectedProducts: [[]],
                refId: 'customisationsforcgwoogridbudgeteerprofittypes',
                activeTab: 'general',
                isOpen: [],
                isAccordionOpen: [true],
                isUpdating: false,
            };
        },
        mounted: async function () {
            let element = this.$el;
            this.apiurl = element.parentElement.dataset.apiurl;
            this.serialized = this.$refs[this.refId].value;

            await this.searchCategories();
            await this.searchProducts();
            await this.buildTable();
        },
        computed: {
            filteredCategories() {
                return this.categories.filter(category =>
                  category.name.toLowerCase().includes(this.searchCategory.toLowerCase())
                )
            },
            filteredProducts() {
                return this.products.filter(product => product.title.toLowerCase().includes(this.searchProduct.toLowerCase()))
            }
        },
        methods: {
            buildTable: async function () {
                await gBuildProfitTable(this);
            },
            updateRows: async function () {
                this.isUpdating = true;
                await gUpdateProfitRows(this);
                this.isUpdating = false;
            },
            addProfitTable: function (type) {
                switch (type) {
                    case 'category':
                        this.profitTable.category.push({
                            categories: [],
                            rows: [{}]
                        });
                        this.selectedCategories.push([]);
                        break;
                    case 'product':
                        this.profitTable.product.push({
                            products: [],
                            rows: [{}]
                        });
                        this.selectedProducts.push([]);
                        break;
                }
            },
            removeProfitTable: function (type, index) {
                switch (type) {
                    case 'category':
                        this.profitTable.category.splice(index, 1);
                        this.selectedCategories.splice(index, 1);
                        break;
                    case 'product':
                        this.profitTable.product.splice(index, 1);
                        this.selectedProducts.splice(index, 1);
                        break;
                }
                gUpdateProfitRows(this);
            },
            addRow: function (event, type, index) {
                gAddRow(event, this, type, index);
            },
            deleteRow(row, event, type, index) {
                gDeleteRow(event, this, row, type, index);
            },
            currentProfitTab(tab) {
                this.activeTab = tab;
            },
            async searchCategories() {
                await gSearchCategories(this);
            },
            async searchProducts() {
                await gSearchProducts(this);
            },
            toggleAccordion(index) {
                this.isAccordionOpen[index] = !this.isAccordionOpen[index];
            },
            async selectCategory(category, index) {
                this.isUpdating = true;
                await selectItem('category', this, category, index);
                this.searchCategory = '';
                this.isOpen = this.isOpen.map(() => false);
                this.profitTable.category[index].categories = this.selectedCategories[index];
                await gUpdateProfitRows(this);
                this.isUpdating = false
            },
            async selectProduct(product, index) {
                this.isUpdating = true;
                await selectItem('product', this, product, index);
                this.searchProduct = ''
                this.isOpen = this.isOpen.map(() => false);
                this.profitTable.product[index].products = this.selectedProducts[index];
                await gUpdateProfitRows(this);
                this.isUpdating = false;
            },
            async removeCategory(category, groupIndex) {
                this.isUpdating = true;
                this.selectedCategories[groupIndex] = this.selectedCategories[groupIndex].filter(c => c.id !== category.id);
                this.profitTable.category[groupIndex].categories = this.selectedCategories[groupIndex];
                await gUpdateProfitRows(this);
                this.isUpdating = false;
            },
            async removeProduct(product, index) {
                this.isUpdating = true;
                this.selectedProducts[index] = this.selectedProducts[index].filter(p => p.id !== product.id);
                this.profitTable.product[index].products = this.selectedProducts[index];
                await gUpdateProfitRows(this);
                this.isUpdating = false
            },
            closeDropdown() {
                this.isOpen = this.isOpen.map(() => false);
            }
        }
    }).mount('#formProfit');
}

if (document.getElementById("formPriceTable")) {

    createApp({
        data() {
            return {
                serialized: '',
                apiurl: '',
                priceTable: {
                    general: [],
                    category: [],
                    product: []
                },
                searchCategory: '',
                categories: [],
                selectedCategories: [[]],
                searchProduct: '',
                products: [],
                selectedProducts: [[]],
                refId: 'customisationsforcgwoogridbudgeteertablepricestypes',
                activeTab: 'general',
                isOpen: [false],
                isAccordionOpen: [true],
                isUpdating: false,
            };
        },
        mounted: async function () {
            let element = this.$el;
            this.apiurl = element.parentElement.dataset.apiurl;
            this.serialized = this.$refs[this.refId].value;

            await this.searchCategories();
            await this.searchProducts();
            this.buildTable();
        },
        computed: {
            filteredCategories() {
                return this.categories.filter(category =>
                    category.name.toLowerCase().includes(this.searchCategory.toLowerCase())
                )
            },
            filteredProducts() {
                return this.products.filter(product => product.title.toLowerCase().includes(this.searchProduct.toLowerCase()))
            }
        },
        methods: {
            buildTable: async function () {
                await gBuildPriceTable(this);
            },
            updateColumns: async function () {
                this.isUpdating = true;
                await gUpdatePriceTableColumns(this);
                this.isUpdating = false;
            },
            addPriceTable: function (type) {
                switch (type) {
                    case 'category':
                        this.priceTable.category.push({
                            categories: [],
                            rows: []
                        });
                        this.selectedCategories.push([]);
                        break;
                    case 'product':
                        this.priceTable.product.push({
                            products: [],
                            rows: []
                        });
                        this.selectedProducts.push([]);
                        break;
                }
            },
            removePriceTable: function (type, index) {
                switch (type) {
                    case 'category':
                        this.priceTable.category.splice(index, 1);
                        this.selectedCategories.splice(index, 1);
                        break;
                    case 'product':
                        this.priceTable.product.splice(index, 1);
                        this.selectedProducts.splice(index, 1);
                        break;
                }
                gUpdatePriceTableColumns(this);
            },
            currentPriceTab(tab) {
                this.activeTab = tab;
            },
            async searchCategories() {
                await gSearchCategories(this);
            },
            async searchProducts() {
                await gSearchProducts(this);
            },
            toggleAccordion(index) {
                this.isAccordionOpen[index] = !this.isAccordionOpen[index];
            },
            async selectCategory(category, index) {
                this.isUpdating = true;
                await selectItem('category', this, category, index);
                this.searchCategory = '';
                this.isOpen = this.isOpen.map(() => false);
                this.priceTable.category[index].categories = this.selectedCategories[index];
                await gUpdatePriceTableColumns(this);
                this.isUpdating = false;
            },
            async selectProduct(product, index) {
                this.isUpdating = true;
                await selectItem('product', this, product, index);
                this.searchProduct = ''
                this.isOpen = this.isOpen.map(() => false);
                this.priceTable.product[index].products = this.selectedProducts[index];
                await gUpdatePriceTableColumns(this);
                this.isUpdating = false;
            },
            async removeCategory(category,  index) {
                this.isUpdating = true;
                this.selectedCategories[index] = this.selectedCategories[index].filter(c => c.id !== category.id)
                this.priceTable.category[index].categories = this.selectedCategories[index];
                await gUpdatePriceTableColumns(this);
                this.isUpdating = false;
            },
            async removeProduct(product, index) {
                this.isUpdating = true;
                this.selectedProducts[index] = this.selectedProducts[index].filter(p => p.id !== product.id)
                this.priceTable.product[index].products = this.selectedProducts[index];
                await gUpdatePriceTableColumns(this);
                this.isUpdating = false;
            },
            closeDropdown() {
                this.isOpen = this.isOpen.map(() => false);
            }
        }
    }).mount('#formPriceTable');
}

