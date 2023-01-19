<template>
    <h1>Vue Select</h1>



  
    <select
      v-if="knownValue || form.fav === ''" 
      class="SelectInput"
      v-model="form.fav"
    >
      <option value=""
              hidden
              v-html="options.placeholder"
      ></option>
      <option
        value="other"
      >Other</option> 
      <option
        v-for="(option, name) in options.prefill"
        :value="option.name"
        :key="option.id"
        v-html="option.name"
      ></option>
    </select>


    <div
      v-if="!knownValue && form.fav !== ''"
    >
      
      <input
        type="text"
        v-model.lazy="form.fav"
        list="fav-list"
        placeholder="Type any value"
        autofocus
      />
  
      <datalist id="fav-list">
        <option
          v-for="(item, index) in options.prefill"
          :key="index"
          :value="item.name"
        ></option>
      </datalist>
  </div>

</template>

<script>  

console.log('VUE admin Test Select loaded')

export default {
    name: 'dynselect',

    data: function() {
        return {
            form: {
                fav: ''
            },
            options: {
                placeholder: 'Pick a value',
                prefill: []
            }
        }
    },

    computed: {
        knownValue() {
            return this.options.prefill.some( prefill => prefill.name === this.form.fav )
        }
    },
    created(){
        this.options.prefill = [
            {
                "id": 1,
                "name": "Dogs"
            },
            {
                "id": 2,
                "name": "Cats"
            },
            {
                "id": 3,
                "name": "Fish"
            },
            {
                "id": 4,
                "name": "Whales"
            },
            {
                "id":5,
                "name": "Horses"
            }
        ]
    }
}
</script>

<style scoped>

</style>