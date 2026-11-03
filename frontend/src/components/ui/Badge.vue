<template>
  <span
    class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
    :class="[
      variant === 'default' ? 'bg-primary text-primary-foreground hover:bg-primary/80' : '',
      variant === 'secondary' ? 'bg-secondary text-secondary-foreground hover:bg-secondary/80' : '',
      variant === 'outline' ? 'text-foreground' : ''
    ]"
  >
    <slot></slot>
  </span>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'secondary', 'outline'].includes(value)
  }
})
</script>