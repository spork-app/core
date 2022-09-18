<template>
  <Switch
    v-model="value"
    :class="modelValue ? 'bg-blue-900 dark:bg-blue-400' : 'bg-slate-500 dark:bg-slate-400'"
    class="relative inline-flex h-6 w-11 items-center rounded-full"
  >
    <span
      :class="modelValue ? 'translate-x-6' : 'translate-x-1'"
      class="inline-block h-4 w-4 transform transition rounded-full bg-white dark:bg-slate-600"
    />
  </Switch>
</template>

<script>
import { Switch } from '@headlessui/vue'

export default {
    props: ['modelValue'],
    emits: ['update:modelValue'],
    components: {
        Switch
    },
    data() {
      return {
        value: false,
      }
    },
    methods: {
        focus() {
            this.$refs.input.focus()
        }
    },
    watch: {
      value(to, from) {
        this.$emit('update:modelValue', to);
      }
    },
    mounted() {
      this.value = this.modelValue;
    }
}
</script>
