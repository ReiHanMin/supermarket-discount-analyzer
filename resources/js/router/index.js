import { createRouter, createWebHistory } from 'vue-router'
import DiscountItems from '../components/DiscountItems.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: DiscountItems
  },
  // Add other routes as needed
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
