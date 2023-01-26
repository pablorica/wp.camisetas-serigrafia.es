<template>
    <div class="cg-dyn-selected"
        v-on:click="toggleDropdown"
    >
        <ul class="cg-selected-list" 
            ref="selectedoptions"
        >
            <li v-for="(option) in selected"
                :title="option.title"
                class="cg-selection" 
            >
                <span v-html="option.title"
                ></span>
                <span class="cg-selection__remove" 
                    role="presentation"
                    @click="unselectOption(option)"
                >x</span>
            </li>
            <li class="cg-chosen-value" >
                <input class="cg-chosen-value" 
                    type="text" 
                    v-model = "filtering"
                    :placeholder="placeholder"
                    @input="writeInField"
                >
            </li>
        </ul>
        <ul class="cg-value-list" 
            :class="dropdownClass"
            v-if="options && options.length"
        >
            <li v-for="(option) in options"
                :class="{ selected: isSelected(option.id) }"
                v-html="option.title"
                @click="(!isSelected(option.id)?selectOption(option):'')"
            ></li>
        </ul>
    </div>
</template>

<script>  

console.log('VUE admin Test Select loaded')

export default {
    name: 'cdgselect',
    props: ['selectgeturl','defrows'],
    watch: {
        // This would be called anytime the value of title changes
        defrows(newValue, oldValue) {
        // you can do anything here with the new value or old/previous value
            //console.log("this.defrows");
            //console.log(newValue);
            this.getDefaultSelected();
        }
    },
    data: function() {
        return {
            geturl:             '',
            dropdownClass:      '',
            filtering:          '',
            selected:           [],
            placeholder:        'Type a value',
            options:            null
        }
    },
    mounted: function () {

        this.geturl = this.selectgeturl

        let element = this.$el;
        if(element.parentElement.dataset.geturl){
            this.geturl = element.parentElement.dataset.geturl;
        }

        if(typeof this.defrows !== 'undefined' 
            && this.defrows.length
        ) {
            this.getDefaultSelected();
        }


        
        if(this.geturl) {
            this.getItemList();
        }        

    },
    
    methods: {
        toggleDropdown: function() {
            var self = this;
            //dropdown.classList.remove('open');
            if(self.dropdownClass === 'open') {
                self.dropdownClass = ''
            } else {
                self.dropdownClass = 'open'
            }
            
        },
        openDropdown: function() {
            var self = this;
            self.dropdownClass = 'open'
        },
        closeDropdown: function() {
            var self = this;
            self.dropdownClass = ''
            
            // setTimeout(() => {
            //     self.dropdownClass = ''
            // }, 200)
        },
        writeInField: function() {
            var self = this;
            self.dropdownClass = 'open'
            if (self.filtering.length > 0) {

                // let filteredOptions = self.defaultOptions.filter(function (el) {
                //     return el.name.toLowerCase().includes(self.filtering);
                // });
                // self.options  = filteredOptions;

                self.getItemList(self.filtering);

                self.dropdownClass = 'open'
                self.placeholder   = '';
            } else {
                //self.options = self.defaultOptions;
                self.getItemList();

                self.dropdownClass = ''
            }
            
        },
        selectOption: function(option) {
            var self = this;
            //console.log(option);
            self.selected.push(option);
            self.filtering   = '';
            self.$emit('changeSelectedOptions',self.selected );

            //self.options     = self.defaultOptions;
            self.placeholder = '';
        },
        unselectOption: function(option) {
            var self = this;

            const indx = self.selected.findIndex(v => v.id === option.id);
            //console.log('Remove '+indx);
            self.selected.splice(indx, indx >= 0 ? 1 : 0);
            if (self.selected.length === 0) {
                self.placeholder = 'Type a value';
            }
            self.$emit('changeSelectedOptions',self.selected );
        },
        isSelected: function(optionId) {
            var self = this;

            const indx = self.selected.findIndex(v => v.id === optionId);
            //console.log('optionId: ' + optionId + ';indx: '+indx);

            if(indx > -1) {
                return true
            } else {
                return false
            }
        },
        getItemList: async function(search = ''){
            var self = this;

            let _letSend = false,
            _data={},

             getURL = self.geturl + '&page=1&per_page=20&search='+search;

            if(_letSend !== false)	{
                _letSend.abort();
            }

            _letSend = await axios.get(
                getURL,
                _data
            )
            .then(function (response) {
                //console.log(response.data);
                self.options = response.data;
                //self.options = JSON.parse(JSON.stringify(self.options))
                _letSend = false
                //console.log(self.options);
                //console.log(JSON.parse(JSON.stringify(self.options)))
            })
            .catch(function (error) {
                console.log(error);
                //console.log('woops');
                _letSend = false;
            });
            
        },
        getDefaultSelected: function() {
            var self = this;

            //console.log('getDefaultSelected')
            //console.log(self.defrows)

            for (var i=0; i < self.defrows.length; ++i) {
                let row = self.defrows[i]
                //console.log('row')
                //console.log(row)


                if(typeof row.items[0] !== 'undefined'
                   && typeof row.items[0].id !== 'undefined'
                ) {
                    for (var j=0; j < row.items.length; ++j) {
                        self.selected[j] = row.items[j]
                    }
                } 
                
            }

        },
    },
    created(){
        const listener = (event) => {
            //console.log(event.target.closest('.cg-dyn-selected'));
            if(!event.target.closest('.cg-dyn-selected')) {
                console.log('clicked out');
                this.closeDropdown();
            }
        }
        window.addEventListener('click', listener);

    }
}
</script>
