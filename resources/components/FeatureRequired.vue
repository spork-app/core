<template>
    <div>
        <div v-if="!actualFeature || (actualFeature.length > 0 && allowMoreThanOne !== undefined)">
            <button @click="$store.commit('setOpenResearch', true)" class="border border-blue-500 dark:border-blue-600 dark:bg-blue-600 dark:text-blue-100 py-1 px-2 text-sm hover:underline rounded text-blue-500">Add {{ feature }}</button>
        </div>
        <div v-if="$store.getters.openResearch" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-900 opacity-75 transition-opacity" aria-hidden="true"></div>

                <!-- This element is to trick the browser into centering the modal contents. -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
                <div class="inline-block align-bottom bg-white dark:bg-gray-600 rounded-lg px-4 pt-5 pb-4 text-left shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">

                    <div class="">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-slate-50" id="modal-title">
                            Name
                        </h3>
                        <div class="mt-2">
                            <spork-input v-model="form.name" placeholder="Subscriptions" type="text" ref="name" />
                        </div>
                    </div>
                    <div v-for="(setting, index) in extraFields" :key="'setting-'+ index" class="flex items-end gap-2 w-full bg-gray-600">
                        <SporkDynamicInput class="mt-2 w-full" v-model="extraFields[index]" :type="extraFields[index]?.type ?? 'text'" />
                        <button class="mb-2" @click="() => extraFields = extraFields.filter((v, i) => i !== index)"><TrashIcon class="w-5 h-5 text-red-500 fill-current"></TrashIcon></button>
                    </div>

                    <Menu as="div" class="relative inline-block text-left">
                        <div>
                          <MenuButton class="mt-4 inline-flex w-full justify-center gap-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-500 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-100">
                                <PlusCircleIcon class="w-5 h-5"></PlusCircleIcon>
                                <span>Add new field</span>
                                <ChevronDownIcon class="h-5 w-5" aria-hidden="true" />
                          </MenuButton>
                        </div>
                    
                        <transition enter-active-class="transition ease-out duration-100" enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100" leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100" leave-to-class="transform opacity-0 scale-95">
                          <MenuItems class="absolute right-0 z-10 mt-2 w-56 origin-top-right divide-y divide-gray-100 rounded-md bg-white dark:bg-gray-500 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                            <div class="py-1">
                                <MenuItem v-slot="{ active }" v-for="extraField in supportedFields" :key="extraField">
                                  <SporkDropDownItem :active="active" :icon="extraField.icon" :text="extraField.name" @click="extraField.onClick"/>
                                </MenuItem>
                            </div>
                          </MenuItems>
                        </transition>
                      </Menu>

                    <div v-for="(item, key) in $store.getters.featureErrors" :key="key">
                        <div v-for="error in item" :key="error" class="text-red-500">{{ error  }}</div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button
                            @click.prevent="saveForm"
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm"
                        >
                            Create {{ feature }}
                        </button>
                        <button 
                            @click=" $store.commit('setOpenResearch', false)"
                            type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import { PlusCircleIcon } from '@heroicons/vue/outline';
import {
  ArrowCircleRightIcon,
  ChevronDownIcon,
  CheckIcon,
  DocumentTextIcon,
CalendarIcon
} from '@heroicons/vue/solid'

import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import SporkDropDownItem from './SporkDropDownItem.vue';
import SporkDynamicInput from './SporkDynamicInput.vue';
export default {
    components: {
    PlusCircleIcon,
    Menu,
    MenuButton,
    MenuItem,
    MenuItems,
    ArrowCircleRightIcon,
    ChevronDownIcon,
    SporkDropDownItem,
    SporkDynamicInput

},
    props: ['feature', 'allowMoreThanOne', 'settings'],
    computed: {
        actualFeature() {
            return this.$store.getters.features[this.feature]
        },
    },
    watch: {
        '$store.getters.openResearch' (newV) {
            if (newV) {
                setTimeout(() => {
                   this.$refs.name.focus()
                }, 150)
            }
        }
    },
    methods: {
        async createFeature(form) {
            await this.$store.dispatch('createFeature', {
                ...form,
                feature: this.feature,
            })
            this.$store.commit('setOpenResearch', false)
            this.$store.dispatch('fetchFeatures');
        },
        saveForm() {
            this.form.settings = this.extraFields.reduce((fields,field) => ({
                ...fields,
                [field.name]: field.value
            }), {});

            this.createFeature(this.form);
        }
    },
    mounted() {
        this.extraFields = Object.keys(this.settings).map(setting => ({
            name: setting,
            value: this.settings[setting],
        }));
    },

    data() {
        return {
            name: null,
            form: {
                name: '',
                settings: {

                }
            },
            extraFields: [],
            supportedFields: [
                {
                    name: 'Text',
                    icon: DocumentTextIcon,
                    onClick: () => this.extraFields.push({
                        name: 'text',
                        value: '',
                        type: 'text'
                    })
                    
                },
                {
                    name: 'Toggle',
                    icon: CheckIcon,
                    onClick: () => this.extraFields.push({
                        name: 'toggle',
                        value: true,
                        type: 'checkbox'
                    })
                },
                {
                    name: 'Date',
                    icon: CalendarIcon,
                    onClick: () => this.extraFields.push({
                        name: 'date',
                        value: null,
                        type: 'date'
                    })
                },
            ], 
            console
        }
    },
}
</script>