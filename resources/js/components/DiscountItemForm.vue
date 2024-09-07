<template>
  <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex justify-center items-center">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
      <h2 class="text-2xl font-bold mb-6 text-gray-800">{{ item ? 'Edit' : 'Create' }} Discount Item</h2>
      
      <form @submit.prevent="submitForm" enctype="multipart/form-data" class="space-y-4">
        <div>
          <label for="date" class="block text-sm font-medium text-gray-700">Date:</label>
          <input type="date" id="date" v-model="formData.date" required
                 class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div>
          <label for="supermarket" class="block text-sm font-medium text-gray-700">Supermarket:</label>
          <input type="text" id="supermarket" v-model="formData.supermarket" required
                 class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div>
          <label for="timeslot" class="block text-sm font-medium text-gray-700">Timeslot:</label>
          <input type="text" id="timeslot" v-model="formData.timeslot" required
                 class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>

        <div>
          <label for="notes" class="block text-sm font-medium text-gray-700">Notes:</label>
          <textarea id="notes" v-model="formData.notes" rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"></textarea>
        </div>

            <div>
        <label for="photo" class="block text-sm font-medium text-gray-700">Upload Photo:</label>
        <input type="file" id="photo" @change="handleFileUpload" accept="image/*"
              class="mt-1 block w-full text-sm text-gray-500
                      file:mr-4 file:py-2 file:px-4
                      file:rounded-full file:border-0
                      file:text-sm file:font-semibold
                      file:bg-blue-50 file:text-blue-700
                      hover:file:bg-blue-100">
      </div>
        
        <div class="flex justify-end space-x-3 mt-6">
          <button type="button" @click="$emit('close')"
                  class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
            Cancel
          </button>
           <button type="submit" :disabled="isSubmitting"
                  class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50 disabled:opacity-50">
            {{ isSubmitting ? 'Submitting...' : (item ? 'Update' : 'Create') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    item: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      formData: {
        date: this.item?.date || new Date().toISOString().split('T')[0],
        supermarket: this.item?.supermarket || '',
        timeslot: this.item?.timeslot || '',
        notes: this.item?.notes || '',
        photo: null
      },
      isSubmitting: false
    }
  },
  methods: {
    handleFileUpload(event) {
      this.formData.photo = event.target.files[0];
      console.log('File selected:', this.formData.photo);
    },
    async submitForm() {
      if (this.isSubmitting) return;
      this.isSubmitting = true;

      const formData = new FormData();
      formData.append('date', this.formData.date);
      formData.append('supermarket', this.formData.supermarket);
      formData.append('timeslot', this.formData.timeslot);
      formData.append('notes', this.formData.notes);
      if (this.formData.photo) {
        formData.append('photo', this.formData.photo);
      }

      console.log('Submitting form data:', Object.fromEntries(formData));

      try {
        const response = await axios.post('/api/discount-items', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        console.log('Form submitted successfully:', response.data);
        this.$emit('submit-success', response.data);
        this.resetForm();
      } catch (error) {
        console.error('Error submitting form:', error.response?.data || error.message);
        if (error.response?.data?.errors) {
          console.error('Validation errors:', error.response.data.errors);
          // Display these errors to the user
        }
        this.$emit('submit-error', error.response?.data || error.message);
      } finally {
        this.isSubmitting = false;
      }
    },
    resetForm() {
      this.formData = {
        date: new Date().toISOString().split('T')[0],
        supermarket: '',
        timeslot: '',
        notes: '',
        photo: null
      };
    }
  }
};
</script>