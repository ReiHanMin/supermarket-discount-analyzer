<template>
  <div id="app">
    <h1>Supermarket Discount Analyzer</h1>
    <DiscountItems />
  
  </div>
</template>

<script>
import DiscountItems from './components/DiscountItems.vue'

export default {
  name: 'App',
  components: {
    DiscountItems,
 
  }
}
</script>

<style>
/* Add any global styles here */
</style>
