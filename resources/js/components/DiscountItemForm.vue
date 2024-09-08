<template>
  <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
    <h2 class="text-2xl font-bold mb-4">{{ isEditing ? 'Edit' : 'Create' }} Discount Item</h2>
    <form @submit.prevent="submitForm">
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="date">
          Date
        </label>
        <input
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          id="date"
          type="date"
          v-model="formData.date"
          required
        >
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="supermarket">
          Supermarket
        </label>
        <input
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          id="supermarket"
          type="text"
          v-model="formData.supermarket"
          required
        >
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="timeslot">
          Timeslot
        </label>
        <input
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          id="timeslot"
          type="text"
          v-model="formData.timeslot"
          required
        >
      </div>
      <div v-if="isEditing" class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="item">
          Item
        </label>
        <input
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          id="item"
          type="text"
          v-model="formData.item"
          required
        >
      </div>
      <div v-if="isEditing" class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="original_price">
          Original Price
        </label>
        <input
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          id="original_price"
          type="number"
          step="0.01"
          v-model.number="formData.original_price"
          required
        >
      </div>
      <div v-if="isEditing" class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="discount_percentage">
          Discount Percentage
        </label>
        <input
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          id="discount_percentage"
          type="number"
          step="0.01"
          v-model.number="formData.discount_percentage"
          required
        >
      </div>
      <div v-if="isEditing" class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="discounted_price">
          Discounted Price
        </label>
        <input
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          id="discounted_price"
          type="number"
          step="0.01"
          v-model.number="formData.discounted_price"
          required
        >
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="photo">
          Photo
        </label>
        <input
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          id="photo"
          type="file"
          @change="handleFileUpload"
          :required="!isEditing"
        >
      </div>
      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="notes">
          Notes
        </label>
        <textarea
          class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
          id="notes"
          v-model="formData.notes"
        ></textarea>
      </div>
      <div v-if="isEditing" class="mb-4">
        <label class="flex items-center">
          <input type="checkbox" v-model="formData.sold_out" class="form-checkbox h-5 w-5 text-blue-600">
          <span class="ml-2 text-gray-700">Sold Out</span>
        </label>
      </div>
      <div class="flex items-center justify-between">
        <button
          class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
          type="submit"
        >
          {{ isEditing ? 'Update' : 'Create' }} Item
        </button>
        <button
          class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
          type="button"
          @click="$emit('close')"
        >
          Cancel
        </button>
      </div>
    </form>
  </div>
</template>

<script>
export default {
  props: {
    item: {
      type: Object,
      default: () => ({})
    },
    isEditing: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      formData: {
        date: '',
        supermarket: '',
        timeslot: '',
        item: '',
        original_price: '',
        discount_percentage: '',
        discounted_price: '',
        photo: null,
        notes: '',
        sold_out: false
      }
    };
  },
  created() {
    this.initializeForm();
  },
  methods: {
    initializeForm() {
      if (this.isEditing && this.item) {
        this.formData = { ...this.item };
        // Ensure the date is in the correct format for the date input (YYYY-MM-DD)
        if (this.formData.date) {
          this.formData.date = new Date(this.formData.date).toISOString().split('T')[0];
        }
      } else {
        // Reset form for creating new item
        Object.keys(this.formData).forEach(key => {
          this.formData[key] = '';
        });
        this.formData.photo = null;
        this.formData.sold_out = false;
        // Set the default date to today
        this.formData.date = new Date().toISOString().split('T')[0];
      }
    },
    handleFileUpload(event) {
      this.formData.photo = event.target.files[0];
    },
    async submitForm() {
      console.log('Form data before submission:', this.formData);
      
      const formData = new FormData();
      const fieldsToSend = this.isEditing 
        ? ['date', 'supermarket', 'timeslot', 'item', 'original_price', 'discount_percentage', 'discounted_price', 'notes', 'sold_out']
        : ['date', 'supermarket', 'timeslot', 'notes'];

      fieldsToSend.forEach(key => {
        if (this.formData[key] !== null && this.formData[key] !== undefined) {
          if (key === 'sold_out') {
            formData.append(key, this.formData[key] ? '1' : '0');
          } else {
            formData.append(key, this.formData[key]);
          }
        }
      });

      if (this.formData.photo instanceof File) {
        formData.append('photo', this.formData.photo);
      }

      try {
        let response;
        if (this.isEditing) {
          response = await axios.post(`/api/discount-items/${this.item.id}`, formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
              'X-HTTP-Method-Override': 'PUT'
            }
          });
        } else {
          response = await axios.post('/api/discount-items', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          });
        }

        console.log('Response:', response.data);
        this.$emit('submit-success', response.data);
      } catch (error) {
        console.error('Error submitting form:', error);
        this.$emit('submit-error', error);
      }
    }
  }
};
</script>