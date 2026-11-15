<template>
    <div>
      {{ count }}
      <button @click="decrement">-</button>
      <button @click="increment">+</button>
    </div>
  </template>

<script>  
console.log('VUE Test loaded')

export default {
  name: 'counter',
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