import axios from "axios";
import { createRouter, createWebHistory } from "vue-router";
import { createStore } from "vuex";
import ChunkIcon from '@icons/Chunk';

const route = (path, pathToComponent, extraOptions = {}) => {
    let routeComponent = null
    if (typeof pathToComponent === "string") {
        routeComponent = require(pathToComponent+ "").default;
    } else {
        routeComponent = pathToComponent;
    }

    return Object.assign({
        path,
        component: routeComponent,
        props: true,
    }, Object.assign(extraOptions, {
        meta: Object.assign({}, (extraOptions.meta || {}))
    }));
}

export default class SporkApp {
    constructor(app) { 
        this.app = app;
        this.routes = [];
        this.stores = [];
        this.store = null;
        this.router = null;
        this.callbacks = [];
        this.fabrications = [];
    }

    fabricate(thing) {
        this.fabrications.push(thing);
    }

    async init(routes) {
        const store = createStore({
            modules: this.stores,
            getters: {
                appName(getters, rootGetters) {
                    return 'Spork App';
                }
            }
        });

        this.store = store;
        console.log('store', store);

        try {
            Object.values(JSON.parse(document.getElementById('body').getAttribute('data-features'))).map(({ name, icon, path}) => {
                try {
                    let { [icon]: iconComponent } = require('@heroicons/vue/outline'); 
        
                    if (!iconComponent && icon === 'ChunkIcon') {
                        iconComponent = ChunkIcon
                    }
        
                    Spork.component(icon, iconComponent);
                } catch (error) {
                    console.warn('failed to load icon', error, icon, name )
                }
            });
            store.commit('setAvailableActions', JSON.parse(document.getElementById('body').getAttribute('data-actions')));
            store.commit('setRelationshipsToLoad', JSON.parse(document.getElementById('body').getAttribute('data-load-with')))
            store.commit('setProvidedFeatures', JSON.parse(document.getElementById('body').getAttribute('data-provides')))
            console.log('[-] store loaded data tags');
        } catch (e) {
            console.log('failed to load env data', e);
        }
        
        this.router = createRouter({
            history: createWebHistory(),
            routes
        });

        // `Setup` the router
        this.app.use(this.router)

        // Setup the store
        this.app.use(store);

        await this.bootCallbacks();
    
        this.app.mount('#app');
    }

    async bootCallbacks() {
        // Wait for the callbacks to finish (essentially data core to the app, authentication, users, features, etc)
        // Callbacks should be booted on page load, and after authentication.
        for (let callback of this.callbacks) {
            await callback({
                app: this.app,
                store: this.store,
                router: this.router,
            });
        }

    }

    component(tag, component) {
        this.app.component(tag, component);
    }

    setupStore(store) {
        this.stores = {
            ...this.stores,
            ...store,
        }
    }

    routesFor(feature, routes) {
        this.routes.push(...routes);
    }

    build(callback) {
        this.callbacks.push(callback);
    }

    // All routes are added under the base route provided by the app, and are provided
    // a base route 404.
    unauthenticatedRoute(path, pathToComponent, extraOptions = {}) {
        return route(path, pathToComponent, Object.assign({
            meta: {
                forceAuth: false,
            }
        }, extraOptions))
    }

    authenticatedRoute (path, pathToComponent, extraOptions = {}) {
        return route(path, pathToComponent, Object.assign({
            meta: {
                forceAuth: true,
            }
        }, extraOptions))
    }

    toast(message, type = 'success') {
        if (type === 'success') { 
            this.sound('finished')
        }

        if (type === 'error') { 
            this.sound('error')
        }
        this.app.$toast[type](message);
    }

    getLocalStorage(key, defaultValue) {
        return JSON.parse(localStorage.getItem(key)) || defaultValue;
    }

    setLocalStorage(key, value){
        localStorage.setItem(key, JSON.stringify(value));

        return value;
    }

    setBasePath(basePath) {
        this.base_path = basePath;
    }

    basePath(relativePath) {
        return this.base_path + '/' + relativePath;
    }

    sound(name) {
        // glitch-sound
        // error-sound
        // success-sound
        // notification-sound
        const v = document.getElementById(name+'-sound');
        v.volume = 0.15;
        v.play();
    }

    async track(key, value, context) {
        try {
            await axios.post('/wiretap/track', {
                event: key,
                value,
                context,
            })
        } catch (e) {
            console.log('Hmmm... We failed to track an event... This is likely due to an ad blocker, or a software issue, or really anything... Heres some more context', e, {
                key, value
            })
        }
    }
}