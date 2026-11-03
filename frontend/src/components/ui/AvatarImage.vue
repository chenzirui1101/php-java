<template>
  <img
    class="h-full w-full object-cover"
    :src="src"
    :alt="alt"
    :loading="loading"
  />
</template>

<script setup>
defineProps({
  src: {
    type: String,
    required: true
  },
  alt: {
    type: String,
    default: ''
  },
  loading: {
    type: String,
    default: 'lazy'
  }
})
</script>