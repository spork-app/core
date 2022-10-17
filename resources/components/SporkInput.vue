<template>
    <input 
        class="mt-1 py-2 px-4 border focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 dark:border-gray-500 dark:bg-gray-500 dark:placeholder-gray-300 rounded-md" 
        :value="modelValue" 
        @input="$emit('update:modelValue', $event.target.value)" 
        ref="input"
        :type="type"
    />

</template>

<script>
export default {
    props: [
        'modelValue',
        'type'
    ],
    emits: ['update:modelValue'],
    methods: {
        focus() {
            this.$refs.input.focus()
        }
    }
}
</script>

