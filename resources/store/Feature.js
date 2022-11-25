import {buildUrl} from "@kbco/query-builder";

export default {
    state: {
        features: Spork.getLocalStorage('feature_data', []),
        corePagination: Spork.getLocalStorage('feature_pagination', {}),
        availableFeatures: [
            'research',
            'finance',
            'planning',
            'maintenance',
            'shopping',
            'weather',
            'property',
            'calendar',
            'greenhouse',
            'contacts',
            'compendium',
            'rss',
        ],
        loading: true,
        errors: {},
        open: false,
        queryOptions: {},

        actions: [],
        loadWith: [],
        provides: [],
    },
    mutations: {
        setOpenResearch(state, value) {
            state.open = value;
        },
        setFeatureLists(state, { data, ...pagination }) {
            state.features = Spork.setLocalStorage('feature_data', data).map(feature => {
                // Here we can modify the features availble with JS objects if needed.
                if (feature.repeatable) {
                    feature.repeatable = feature.repeatable.map(e => new CalendarEvent(e))
                }
                return feature;
            });
            state.pagination = Spork.setLocalStorage('feature_pagination', pagination)
        },
        setRelationshipsToLoad(state, relations) {
            state.loadWith = relations;
        },
        setProvidedFeatures(state, features) {
            state.providedFeatures = features;
        },
        setAvailableActions(state, actions) {
            state.actions = actions;
        },
    },
    getters: {
        featuresLoading: state => state.loading,
        featuresPagination: state => state.pagination,
        openResearch: state => state.open,
        features: state => state.features.reduce((allFeatures, feature) => ({
            ...allFeatures,
            [feature.feature]: [ ...new Set([ ...(allFeatures[feature.feature] ? allFeatures[feature.feature]: [] ), feature])],
        }), {}),
        featureErrors: state => state.errors,
        actionsForFeature: state => Object.values(state.actions).flat(1).reduce((allActions, action) => ({
            ...action.tags.reduce((tags, tag) => ({
                ...tags,
                [tag]: [ ...new Set([ ...(tags[tag] ? tags[tag]: [] ), action])],
            }), allActions)
        }), {}),
        availableActions: (state) => state.actions,
        providedFeatures: state => state.provides,
        loadWith: state => state.loadWith
    },
    actions: {
        async getFeatureLists({ commit, state }, { filter, feature, ...options }) {
            state.loading = true;
            state.queryOptions = {
                filter, feature, ...options,
            }
            const { data } = await axios.get(buildUrl('/api/core/feature-list', {
                filter: {
                    ...filter,
                    ...(feature ? { feature } : { }),
                },
                ...options,
                action: 'simplePaginate:100'
            }));

            commit('setFeatureLists', data);
            setTimeout(()=> state.loading = false, 500);
        },

        fetchFeatures({ getters, dispatch}, options){
            dispatch('getFeatureLists', {
                include: getters.loadWith,
                ...(options ?? {})
            })
        },
        async createFeature({ commit, state, dispatch }, feature) {
            try {
                state.loading = true;
                const { data } = await axios.post('/api/core/feature-list', feature);
                state.features.push(data);
                commit('setOpenResearch', false);

                await dispatch('getFeatureLists', state.queryOptions);

                return data;
            } catch (error) {
                state.errors = error.response.data.errors;
            } finally {
                state.loading = false;
            }
        },
        async updateFeature({ commit, state, dispatch }, feature) {
            try {
                state.loading = true;
                const { data } = await axios.put('/api/core/feature-list/'+feature.id, feature);
                state.features = state.features.map(feature => {
                    if (feature.id === data.id) {
                        return {
                            ...feature,
                            ...data,
                        };
                    }

                    return feature;
                });

                dispatch('getFeatureLists', state.queryOptions);
            } catch (error) {
                state.errors = error.response.data.errors;
            }
        },
        async deleteFeature({ commit, state, dispatch }, feature) {
            try {
                state.loading = true;
                await axios.delete('/api/core/feature-list/'+feature.id);
                state.features = state.features.filter(feature => feature.id !== feature.id);

                dispatch('getFeatureLists', state.queryOptions);
            } catch (error) {
                if (error?.response?.data?.errors) {
                    state.errors = error.response.data.errors;
                } else {
                    state.errors = [error.message]
                }

                console.error(error, feature);
                state.loading = false;
            }
        },

        async shareFeature({ commit, state, dispatch }, { feature, email }) {
            try {
                state.loading = true;
                await axios.post('/api/core/share', { email, feature_list_id: feature.id });
                Spork.toast('I\'ve sent an invite to ')
            } catch (error) {
                state.errors = error.response.data.errors;
            } finally {
                state.loading = false;
            }
        },
        async executeAction({ state }, { url, data }) {
            // actionToRun,
            // selectedItems,
            await axios.post(url, data);
        }
    },

};
