<template>
  <div class="bg-white shadow-md rounded-lg overflow-hidden">
    <div class="p-6 bg-gray-50 border-b border-gray-200">
      <h2 class="text-2xl font-semibold text-gray-800">Discount Items</h2>
      <button @click="openCreateForm" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">
        <i class="fas fa-plus mr-2"></i> Add New Item
      </button>
    </div>
    
    <div class="overflow-x-auto">
      <div v-if="loading" class="text-center py-6 text-gray-500">Loading...</div>
      <div v-else-if="error" class="text-center py-6 text-red-500">{{ error }}</div>
      <table v-else-if="items && items.length" class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supermarket</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Original Price</th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Discount %</th>
            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Discounted Price</th>
            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50 transition duration-150 ease-in-out">
            <td class="px-6 py-4 whitespace-nowrap">
              <img v-if="item.photo" 
                   :src="'/storage/' + item.photo" 
                   :alt="item.item" 
                   class="h-20 w-20 object-cover rounded-md cursor-pointer"
                   @click="openModal(item)">
              <span v-else class="text-gray-400">No photo</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(item.date) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.supermarket }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.item }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ formatPrice(item.original_price) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">{{ formatPercentage(item.discount_percentage) }}</td>
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

    <!-- Modal for Create/Edit Form -->
    <div v-if="showForm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
      <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full m-4">
        <div class="absolute top-0 right-0 pt-4 pr-4">
          <button @click="closeForm" class="text-gray-400 hover:text-gray-500">
            <span class="sr-only">Close</span>
            <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <DiscountItemForm 
          :item="editingItem"
          :is-editing="!!editingItem"
          @close="closeForm"
          @submit-success="handleFormSuccess"
          @submit-error="handleSubmitError"
        />
      </div>
    </div>

    <ImageModal 
      :show="showImageModal"
      :image-src="selectedImage.src"
      :image-alt="selectedImage.alt"
      @close="closeModal"
    />
  </div>
</template>

<script>
import { format } from 'date-fns';
import DiscountItemForm from './DiscountItemForm.vue';
import ImageModal from './ImageModal.vue';

export default {
  components: {
    DiscountItemForm,
    ImageModal,
  },
  data() {
    return {
      items: [],
      showForm: false,
      editingItem: null,
      loading: true,
      error: null,
      showImageModal: false,
      selectedImage: { src: '', alt: '' },
    };
  },
  mounted() {
    console.log('Component mounted');
    this.fetchItems();
  },
  methods: {
    getPhotoUrl(photoFilename) {
      return photoFilename ? `/storage/${photoFilename}` : null;
    },

    async fetchItems() {
      try {
        console.log('Fetching items...');
        this.loading = true;
        this.error = null;
        const response = await axios.get('/api/discount-items');
        console.log('API response:', response.data);
        this.items = response.data.map(item => ({
          ...item,
          photoUrl: this.getPhotoUrl(item.photo)
        }));
        this.loading = false;
      } catch (error) {
        console.error('Error fetching items:', error);
        this.error = 'An error occurred while fetching items.';
        this.loading = false;
      }
    },

    openCreateForm() {
      this.editingItem = null;
      this.showForm = true;
    },

    editItem(item) {
      console.log('Editing item:', item);
      this.editingItem = { ...item };
      this.showForm = true;
    },

    closeForm() {
      this.showForm = false;
      this.editingItem = null;
    },

    handleFormSuccess(data) {
      console.log('Item created/updated successfully:', data);
      this.closeForm();
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
    },

    formatPercentage(percentage) {
      return percentage ? `${percentage.toFixed(1)}%` : '0%';
    },

    openModal(item) {
      this.selectedImage = {
        src: '/storage/' + item.photo,
        alt: item.item,
      };
      this.showImageModal = true;
    },

    closeModal() {
      this.showImageModal = false;
    },
  }
};
</script>