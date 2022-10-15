<template>
    <div class="w-full mt-4 ">
        <div class="mx-4">
            <div class="container mx-auto">
                <div class="flex flex-wrap shadow">
                    <div class="text-4xl font-medium text-blue-900 dark:text-slate-200">
                        {{ title }}
                    </div>

                    <div class="w-full mt-4 flex flex-wrap items-center justify-between">
                        <div class="relative flex flex-1 max-w-2xl text-slate-700 dark:text-slate-300 items-center">
                            <SporkInput type="text" placeholder="Search..." class="pl-10" />
                            <div class="absolute top-0 left-0 ml-3 mt-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                        <div v-if="typeof save === 'function'" class="text-right ml-4">
                            <SporkButton @click="createOpen = true" primary large>
                                Create {{singular}}
                            </SporkButton>
                        </div>
                    </div>

                    <div class="w-full bg-white dark:bg-slate-700  rounded-lg mt-4 flex flex-wrap items-center justify-between">
                        <div class="bg-slate-600 dark:bg-slate-800 relative border-b border-slate-300 w-full p-4 flex flex-wrap justify-between items-center rounded-t-lg">
                            <input @change="selectAll" type="checkbox">
                            <button @click="filtersOpen= !filtersOpen" class="focus:outline-none flex flex-wrap items-center p-2 rounded-lg" :class="{'bg-slate-300 dark:bg-slate-700': filtersOpen, 'bg-slate-100 dark:bg-slate-900': !filtersOpen}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div v-if="filtersOpen" class="absolute z-10 bg-white dark:bg-slate-700 shadow-lg top-0 right-0 mt-14 mr-4 border border-slate-200 dark:border-slate-500 rounded-lg" style="width: 250px;">
                                <div class="bg-slate-100 dark:bg-slate-800 uppercase py-2 px-2 font-bold text-slate-500 dark:text-slate-400 text-sm rounded-t-lg">
                                    filters
                                </div>
                                <div class="flex flex-wrap items-center p-2">
                                    <select v-model="itemsPerPage" class="border border-slate-300 rounded-lg w-full p-1 dark:border-slate-600 dark:bg-slate-600">
                                        <option value="15">15 items per page</option>
                                        <option value="30">30 items per page</option>
                                        <option value="100">100 items per page</option>
                                    </select>
                                </div>
                                <div v-if="actions?.length > 0" class="uppercase py-2 px-2 font-bold text-slate-500 text-sm">
                                    actions
                                </div>
                                <div class="flex flex-wrap items-center p-2 gap-2" v-if="actions?.length > 0">
                                    <select v-model="actionToRun" class="border border-slate-300 rounded-lg flex-grow p-1 dark:border-slate-600 dark:bg-slate-600">
                                        <option v-for="action in actions" :key="action" :value="action">{{ action.name }} ({{ selectedItems.length }})</option>
                                    </select>

                                    <button type="button" @click.prevent="() => {
                                        Spork.toast('Running action... Please wait a moment.', 'info');
                                        $emit('execute', { selectedItems, actionToRun })
                                        selectedItems = [];
                                    }">
                                        <play-icon class="w-6 h-6 stroke-current" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="data.length > 0" class="w-full flex flex-wrap rounded-b ">
                            <div v-for="(datum, $i) in data" :key="'crud-view'+$i" class="w-full py-4 px-2 flex flex-wrap items-center border-b">
                                <div class="w-6 mx-2">
                                    <input type="checkbox" v-model="selectedItems" :value="datum">
                                </div>
                                <div class="flex-1">
                                    <slot v-if="datum" class="flex-1" :data="datum" name="data">
                                        <pre class="flex-1">fallback: {{ datum}}</pre>
                                    </slot>
                                </div>
                                <div v-if="typeof destroy === 'function'" class="flex items-center w-8">
                                    <button type="button" @click.prevent="$emit('destroy', datum)">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div v-else class="w-full p-4 italic text-center">
                            No {{ singular.toLowerCase() }} data
                        </div>

                    </div>

                    <div class="w-full flex justify-between flex-wrap bg-slate-100 dark:bg-slate-800 px-4 py-2">
                        <SporkButton :disabled="hasPreviousPage" :plain="true" :xlarge="true" :class="[!hasPreviousPage ? 'opacity-50 cursor-not-allowed': '']">Previous</SporkButton>
                        <div class="py-2">
                            {{ total }} total items, {{paginator?.per_page}} on page {{ currentPage }} 
                        </div>
                        <SporkButton :disabled="hasNextPage" plain xlarge :class="[!hasNextPage ? 'opacity-50 cursor-not-allowed': '']">Next</SporkButton>
                    </div>
                </div>
            </div>
            <div v-if="createOpen" class="fixed z-0 inset-0 flex items-center outline-none w-screen h-screen overflow-y-scroll">
                <div class="relative z-10 w-full md:w-1/2 mx-auto max-h-screen overflow-y-auto">
                    <div class="w-full rounded p-4 bg-white dark:bg-slate-600 shadow-lg text-left">
                        <div class="text-xl flex justify-between">
                            <slot name="modal-title">Create Modal</slot>
                            <button @click="createOpen = false" class="focus:outline-none">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="flex flex-col border-t border-slate-200 mt-2 pt-4">
                            <slot name="form"></slot>
                            <div class="mt-4 flex justify-end">
                                <SporkButton @click.prevent="async () => {
                                        $emit('save', form);
                                        createOpen = false;
                                    }"
                                    primary
                                    medium
                                >
                                    Save
                                </SporkButton>
                            </div>
                        </div>
                    </div>
                </div>
                <div @click="createOpen = false" v-if="createOpen" class="fixed z-0 cursor-pointer inset-0 flex items-center outline-none" style="background: rgba(0,0,0,0.4);"></div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import { PlayIcon } from "@heroicons/vue/outline";
import SporkInput from './SporkInput.vue';
import SporkButton from './SporkButton.vue';

export default {
    components: {
    PlayIcon,
    SporkInput,
    SporkButton
},
    props: {
        form: {
            type: Object,
            default: null,
        },
        title: {
            type: String,
            default: 'Title',
        },
        singular: {
            type: String,
            default: 'singular',
        },

        // store
        save: {
            type: Function,
            default: null,
        },
        destroy: {
            type: Function,
            default: null,
        },
        index: {
            type: Function,
            default: (params) => {},
        },

        // getters
        data: {
            type: Array,
            default: () => [],
        },
        paginator: {
            type: Object,
            default: () => ({}),
        }
    },
    setup() {
        return {
            createOpen: ref(false),
            filtersOpen: ref(false),
            selectedItems: ref([]),
            itemsPerPage: ref(15),
            actionToRun: ref(null),
            Spork
        }
    },
    computed: {
        actions() {
            const key = this.title.toLowerCase().replace(' ', '-');

            return this.$store.getters.actionsForFeature[key] ?? []
        },
        hasPreviousPage() {
            return this.paginator.prev_page_url !== null;
        },
        hasNextPage() {
            return this.paginator.next_page_url !== null;
        },
        total() {
            return this.paginator.total;
        },
        currentPage() {
            return this.paginator.current_page;
        },
        lastPage() {
            return Math.max(this.paginator.total / this.paginator.per_page, 1);
        },
    },
    methods: {
        hasErrors(error) {
            if (!this.form.errors) {
                return '';
            }

            return this.form.errors[error];
        },
        getData(page = 1, limit = 15) {
            this.$emit('index', { page, limit });
        },
        selectAll(event) {
            if (event.target.checked) {
                this.selectedItems = this.data;
            } else {
                this.selectedItems = [];
            }
        },
    },
    mounted() {
        this.getData()
    }
}
</script>

<style scoped>

</style>
