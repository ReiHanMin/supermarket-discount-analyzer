<template>
  <div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6 bg-gray-50 border-b border-gray-200">
      <h2 class="text-2xl font-semibold text-gray-800">Discount Items</h2>
      <button @click="showCreateForm = true" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">
        <i class="fas fa-plus mr-2"></i> Add New Item
      </button>
    </div>
    
    <div class="overflow-x-auto">
      <div v-if="loading" class="text-center py-6 text-gray-500">Loading...</div>
      <div v-else-if="error" class="text-center py-6 text-red-500">{{ error }}</div>
      <table v-else-if="items && items.length" class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supermarket</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Original Price</th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Discounted Price</th>
            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50 transition duration-150 ease-in-out">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(item.date) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.supermarket }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.item }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ formatPrice(item.original_price) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ formatPrice(item.discounted_price) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <button @click="editItem(item)" class="text-indigo-600 hover:text-indigo-900 mr-3 transition duration-150 ease-in-out">
                <i class="fas fa-edit"></i> Edit
              </button>
              <button @click="deleteItem(item.id)" class="text-red-600 hover:text-red-900 transition duration-150 ease-in-out">
                <i class="fas fa-trash"></i> Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <p v-else class="text-center py-6 text-gray-500">No discount items found.</p>
    </div>

    <DiscountItemForm 
      v-if="showCreateForm" 
      @close="closeCreateForm"
      @submit-success="handleCreateSuccess"
      @submit-error="handleSubmitError"
      class="mt-6"
    />
    <DiscountItemForm 
      v-if="editingItem" 
      :item="editingItem"
      @close="closeEditForm"
      @submit-success="handleUpdateSuccess"
      @submit-error="handleSubmitError"
      class="mt-6"
    />
  </div>
</template>

<script>
import { format } from 'date-fns';
import DiscountItemForm from './DiscountItemForm.vue';

export default {
  components: {
    DiscountItemForm
  },
  data() {
    return {
      items: [],
      showCreateForm: false,
      editingItem: null,
      loading: true,
      error: null
    };
  },
  mounted() {
    console.log('Component mounted');
    this.fetchItems();
  },
  methods: {
    async fetchItems() {
      try {
        console.log('Fetching items...');
        this.loading = true;
        this.error = null;
        const response = await axios.get('/api/discount-items');
        console.log('API response:', response.data);
        this.items = response.data;
        this.loading = false;
      } catch (error) {
        console.error('Error fetching items:', error);
        this.error = 'An error occurred while fetching items.';
        this.loading = false;
      }
    },
    editItem(item) {
      this.editingItem = { ...item };
    },
    closeCreateForm() {
      this.showCreateForm = false;
    },
    closeEditForm() {
      this.editingItem = null;
    },
    handleCreateSuccess(data) {
      console.log('Item created successfully:', data);
      this.closeCreateForm();
      this.fetchItems();
    },
    handleUpdateSuccess(data) {
      console.log('Item updated successfully:', data);
      this.closeEditForm();
      this.fetchItems();
    },
    handleSubmitError(error) {
      console.error('Error submitting form:', error);
      // You can add logic here to display the error to the user
    },
    async deleteItem(id) {
      if (confirm('Are you sure you want to delete this item?')) {
        try {
          await axios.delete(`/api/discount-items/${id}`);
          this.fetchItems();
        } catch (error) {
          console.error('Error deleting item:', error);
          // You can add logic here to display the error to the user
        }
      }
    },
    formatDate(dateString) {
      return format(new Date(dateString), 'MMM dd, yyyy');
    },
    formatPrice(price) {
      return new Intl.NumberFormat('ja-JP', { 
        style: 'currency', 
        currency: 'JPY',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
      }).format(price);
    }
  }
};
</script>