<template>
  <transition name="fade">
    <div v-if="show" class="fixed inset-0 flex items-center justify-center z-50" @click.self="close">
      <div class="absolute inset-0 bg-black opacity-50 backdrop-blur-sm"></div>
      <div class="relative z-10 max-w-3xl max-h-full">
        <img :src="imageSrc" :alt="imageAlt" class="max-w-full max-h-[90vh] object-contain rounded-lg shadow-xl">
        <button @click="close" class="absolute top-4 right-4 text-white text-2xl hover:text-gray-300">&times;</button>
      </div>
    </div>
  </transition>
</template>

<script>
export default {
  name: 'ImageModal',
  props: {
    show: Boolean,
    imageSrc: String,
    imageAlt: String,
  },
  methods: {
    close() {
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>
