<template>
    <div class="w-full flex flex-col">
        <input 
            :value="modelValue.name" 
            @input="$emit('update:modelValue', {
                ...modelValue,
                name: $event.target.value
            })" 
            class="bg-transparent w-full rounded-t-md text-xs leading-loose tracking-wide font-bold py-0 px-4" placeholder="text" type="text" 
        />

        <label
            class="flex dark:bg-gray-500 dark:placeholder-gray-300 rounded-b-md h-10" 
            :class="[type === 'checkbox' ? 'pl-4 pt-4': 'p-0']"
        >
            <input 
                class="py-2 px-3 block shadow-sm sm:text-sm dark:bg-gray-500 dark:placeholder-gray-300 rounded-b-md" 

                :class="[type !== 'checkbox' ? 'w-full': 'bg-gray-50 border-gray-50 dark:border-gray-400 border p-1']"
                :value="modelValue.value" 
                @input="$emit('update:modelValue', {
                    ...modelValue,
                    value: $event.target.value
                })" 
                :type="type"
            />

        </label>
    </div>
</template>

<script>
export default {
    props: ['modelValue', 'type', 'autofocus'],
    emits: ['update:modelValue'],
}
</script>

