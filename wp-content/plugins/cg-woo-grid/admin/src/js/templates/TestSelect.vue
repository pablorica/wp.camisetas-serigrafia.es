<template>
    <h1>Vue Select</h1>

    <div class="cg-dyn-selected"
        v-on:click="toggleDropdown"
    >
        <ul class="cg-selected-list" >
            <li v-for="(option) in selected"
                :title="option.name"
                class="cg-selection" 
            >
                <span v-html="option.name"
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
                    :placeholder="options.placeholder"
                    @input="writeInField"
                >
            </li>
        </ul>
        
        <ul class="cg-value-list" :class="dropdownClass">
            <li v-for="(option) in options.prefill"
                :class="{ selected: isSelected(option.id) }"
                v-html="option.name"
                @click="(!isSelected(option.id)?selectOption(option):'')"
            ></li>
        </ul>
    </div>
</template>

<script>  

console.log('VUE admin Test Select loaded')

export default {
    name: 'dynselect',

    data: function() {
        return {
            dropdownClass:      '',
            filtering:          '',
            defaultOptions:     {},
            selected:           [],
            options:            {
                                    placeholder: 'Type a value',
                                    prefill: []
                                }
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
            //dropdown.classList.remove('open');
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
            //inputField.addEventListener('input', () => {
            //dropdown.classList.add('open');
            self.dropdownClass = 'open'
            //let inputValue = inputField.value.toLowerCase();
            //let valueSubstring;
            if (self.filtering.length > 0) {

                let filteredOptions = self.defaultOptions.filter(function (el) {
                    return el.name.toLowerCase().includes(self.filtering);
                });
                self.dropdownClass = 'open'
                console.log(filteredOptions);
                self.options.prefill = filteredOptions;
                self.options.placeholder = '';
            } else {
                self.dropdownClass = ''
                self.options.prefill = self.defaultOptions;
            }
            
        },
        selectOption: function(option) {
            var self = this;
            //console.log(option);
            self.selected.push(option);
            self.filtering = '';
            self.options.prefill     = self.defaultOptions;
            self.options.placeholder = '';
        },
        unselectOption: function(option) {
            var self = this;

            const indx = self.selected.findIndex(v => v.id === option.id);
            //console.log('Remove '+indx);
            self.selected.splice(indx, indx >= 0 ? 1 : 0);
            if (self.selected.length === 0) {
                self.options.placeholder = 'Type a value';
            }
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
    },
    created(){
        this.options.prefill = [
            {
                "id": 1,
                "name": "Alabama"
            },
            {
                "id": 2,
                "name": "Alaska"
            },
            {
                "id": 3,
                "name": "Arizona"
            },
            {
                "id": 4,
                "name": "Arkansas"
            },
            {
                "id": 5,
                "name": "California"
            },
            {
                "id": 6,
                "name": "Colorado"
            },
            {
                "id": 7,
                "name": "Connecticut"
            },
            {
                "id": 8,
                "name": "Delaware"
            },
            {
                "id": 9,
                "name": "Florida"
            },
            {
                "id": 10,
                "name": "Georgia"
            },
            {
                "id": 11,
                "name": "Hawaii"
            },
            {
                "id": 12,
                "name": "Idaho"
            },
            {
                "id": 13,
                "name": "Illinois"
            },
            {
                "id": 14,
                "name": "Indiana"
            },
            {
                "id": 15,
                "name": "Iowa"
            },
            {
                "id": 16,
                "name": "Kansas"
            },
            {
                "id": 17,
                "name": "Kentucky"
            },
            {
                "id": 18,
                "name": "Louisiana"
            },
            {
                "id": 19,
                "name": "Maine"
            },
            {
                "id": 20,
                "name": "Maryland"
            },
            {
                "id": 21,
                "name": "Massachusetts"
            },
            {
                "id": 22,
                "name": "Michigan"
            },
            {
                "id": 23,
                "name": "Minnesota"
            },
            {
                "id": 24,
                "name": "Mississippi"
            },
            {
                "id": 25,
                "name": "Missouri"
            },
            {
                "id": 26,
                "name": "Montana"
            },
            {
                "id": 27,
                "name": "Nebraska"
            },
            {
                "id": 28,
                "name": "Nevada"
            },
            {
                "id": 29,
                "name": "New Hampshire"
            },
            {
                "id": 30,
                "name": "New Jersey"
            },
            {
                "id": 31,
                "name": "New Mexico"
            },
            {
                "id": 32,
                "name": "New York"
            },
            {
                "id": 33,
                "name": "North Carolina"
            },
            {
                "id": 34,
                "name": "North Dakota"
            },
            {
                "id": 35,
                "name": "Ohio"
            },
            {
                "id": 36,
                "name": "Oklahoma"
            },
            {
                "id": 37,
                "name": "Oregon"
            },
            {
                "id": 38,
                "name": "Pennsylvania"
            },
            {
                "id": 39,
                "name": "Rhode Island"
            },
            {
                "id": 40,
                "name": "South Carolina"
            },
            {
                "id": 41,
                "name": "South Dakota"
            },
            {
                "id": 42,
                "name": "Tennessee"
            },
            {
                "id": 43,
                "name": "Texas"
            },
            {
                "id": 44,
                "name": "Utah"
            },
            {
                "id": 45,
                "name": "Vermont"
            },
            {
                "id": 46,
                "name": "Virginia"
            },
            {
                "id": 47,
                "name": "Washington"
            },
            {
                "id": 48,
                "name": "West Virginia"
            },
            {
                "id": 49,
                "name": "Wisconsin"
            },
            {
                "id": 50,
                "name": "Wyoming"
            }
        ]
        this.defaultOptions = this.options.prefill;

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

<style scoped>

</style>