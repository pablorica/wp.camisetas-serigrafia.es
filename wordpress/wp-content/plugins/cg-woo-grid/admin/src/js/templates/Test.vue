<template>
    <div>
      {{ count }}
      <button @click="decrement">-</button>
      <button @click="increment">+</button>
    </div>
  </template>

<script>  
console.log('VUE admin Test loaded')

export default {
  name: 'cdg-test',
  data() {
    return {
      // Initialized to zero to begin
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
}
</script>

<style scoped>

</style>