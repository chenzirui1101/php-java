<template>
  <button
    :class="[
      'inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:opacity-50 disabled:pointer-events-none ring-offset-background',
      variant === 'default' ? 'bg-primary text-primary-foreground hover:bg-primary/90' : '',
      variant === 'outline' ? 'border border-input bg-background hover:bg-accent hover:text-accent-foreground' : '',
      variant === 'secondary' ? 'bg-secondary text-secondary-foreground hover:bg-secondary/80' : '',
      variant === 'ghost' ? 'hover:bg-accent hover:text-accent-foreground' : '',
      variant === 'link' ? 'text-primary underline-offset-4 hover:underline' : '',
      size === 'default' ? 'h-10 px-4 py-2' : '',
      size === 'sm' ? 'h-9 rounded-md px-3' : '',
      size === 'lg' ? 'h-11 rounded-md px-8' : '',
      size === 'icon' ? 'h-10 w-10' : ''
    ]"
    :type="type"
    @click="$emit('click', $event)"
  >
    <slot></slot>
  </button>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'outline', 'secondary', 'ghost', 'link'].includes(value)
  },
  size: {
    type: String,
    default: 'default',
    validator: (value) => ['default', 'sm', 'lg', 'icon'].includes(value)
  },
  type: {
    type: String,
    default: 'button'
  }
})

defineEmits(['click'])
</script>