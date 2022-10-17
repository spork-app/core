<template>
    <button @click="(e) => $emit('click', e)" :class="[active ? 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-50' : 'text-gray-700 dark:text-gray-50', 'group flex items-center px-4 py-2 text-sm w-full']">
        <Component :is="icon" class="mr-3 h-5 w-5 text-gray-400 dark:text-gray-50 group-hover:text-gray-500" aria-hidden="true" />
        {{text}}
    </button>
</template>

<script>
export default {
    props: ['active', 'icon', 'text'],
    emits: ['click']
}
</script>