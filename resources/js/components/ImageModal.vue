<template>
  <div class="image-modal" v-if="isOpen">
    <div class="modal-content">
      <img :src="imageSrc" :alt="imageAlt">
      <button @click="closeModal">Close</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'ImageModal',
  props: {
    imageSrc: {
      type: String,
      required: true
    },
    imageAlt: {
      type: String,
      default: 'Image'
    },
    isOpen: {
      type: Boolean,
      default: false
    }
  },
  methods: {
    closeModal() {
      this.$emit('close')
    }
  }
}
</script>

<style scoped>
.image-modal {
  /* Modal styling */
}
.modal-content {
  /* Content styling */
}
</style>
