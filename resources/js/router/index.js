import { createRouter, createWebHistory } from 'vue-router'
import DiscountItems from '../components/DiscountItems.vue'

const routes = [
  {
    path: '/',
    name: 'DiscountItems',
    component: DiscountItems
  },
  // Add more routes as needed
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
