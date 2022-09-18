function convertDataTypeBasedOnImage(data) {
    if (data.photo) {
        let formData = new FormData();
        formData.append("photo", data.photo);
        formData.append("name", data.name);
        formData.append("email", data.email);
        formData.append("_method", "put");
        return formData;
    } else {
        return {
            name: data.name,
            email: data.email,
            _method: 'put',
        }
    }
}

export default {
    state: {
        user: null,
        authenticated: false,
        notifications: [],
        errors: null,
        loading: false,
        markingAsRead: [],
    },
    getters: {
        user: state => state.user,
        notifications: state => state.user.unread_notifications ?? [],
        isAuthenticated: state => state.authenticated && state.user,
        readingNotifications: state => state.markingAsRead,
    },
    mutations: {
        setAuthenticated(state, isAuthenticated) {
            state.authenticated = isAuthenticated;
        },
        setUser(state, user) {
            state.user = user;
        },
        clearAuth(state) {
            state.authenticated = null;
            state.user = null;
        },
        setErrors(state, errors) {
            state.errors = errors;
        },
        setNotifications(state, notifications) {
            state.notifications = notifications;
        }
    },
    actions: {
        async login({ commit, dispatch, state }, { email, password }) {
            state.loading = true;
            try {
                await axios.post('/login', { email, password })
                commit('setAuthenticated', true);
                await dispatch('fetchUser');
                Spork.bootCallbacks();
                commit('setErrors', null)
            } catch (error) {
                if (error.response.status === 422) {
                     commit('setErrors', error.response.data);
                } else if (error.response.status === 429) {
                    commit('setErrors', {
                        email: ['Too many login attempts. Please try again in ' + dayjs(Number(error.response.headers['x-ratelimit-reset']) * 1000).diff(dayjs(), 'second') + ' seconds.'],
                    });
                }
            } finally {
                setTimeout(() => state.loading = false, 400);
            }
        },
        async register({ commit, dispatch, state }, { name, email, password, password_confirmation }) {
            state.loading = true;
            try {
                await axios.post('/register', { name, email, password, password_confirmation })
                commit('setAuthenticated', true);
                await dispatch('fetchUser');
                Spork.bootCallbacks();
                commit('setErrors', null)
            } catch (error) {
                if (error.response.status === 422) {
                     commit('setErrors', error.response.data);
                } else if (error.response.status === 429) {
                    commit('setErrors', {
                        email: ['Too many login attempts. Please try again in ' + dayjs(Number(error.response.headers['x-ratelimit-reset']) * 1000).diff(dayjs(), 'second') + ' seconds.'],
                    });
                }
            } finally {
                setTimeout(() => state.loading = false, 400);
            }
        },
        async fetchUser({ commit }) {
            try {
                // use axios to get the user from the api
                const { data } = await axios.get('/api/user');
                commit('setUser', data);
                commit('setAuthenticated', true);
            } catch (error) {
                if (error.response.status === 401) {
                    commit('clearAuth');
                }
            }
        },

        async updateProfile ({ commit, state }, data) {
            try {
                state.loading = true;
                await axios.post('/user/profile-information', convertDataTypeBasedOnImage(data));
                commit('setUser', {
                    ...state.user,
                    name: data.name,
                    email: data.email,
                });
                commit('setErrors', null);
            } catch (error) {
                if (error.response?.data) {
                    commit('setErrors', error.response?.data);
                } else {
                    commit('setErrors', error.message)
                }
                console.error(error)
            } finally {
                state.loading = false;
            }
        },
        
        async logout({ commit }) {
            // use axios to logout
            await axios.post('/logout');
            commit('clearAuth');
        },

        async markAsRead({ commit, state, dispatch }, notificationId) {
            try {
                state.markingAsRead.push(notificationId);
                await axios.post('/api/notifications/' + notificationId + '/mark-as-read');
                await dispatch('fetchUser');
                state.markingAsRead = state.markingAsRead.filter(id => id !== notificationId);
            } catch (error) {
                console.error(error);
            }
        }
    },
};
