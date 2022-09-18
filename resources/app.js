
import SporkApp from './SporkApp';

import Toaster from "@meforma/vue-toaster";

import { createApp } from 'vue';
const app = createApp({});
app.use(Toaster);

window.Spork = new SporkApp(app);

// require('./fabricator-components')

Spork.component('toggle-input', require('./components/ToggleInput').default);
Spork.component('crud-view', require('./components/CrudView').default);
Spork.component('feature-required', require('./components/FeatureRequired').default);
Spork.component('dual-menu-panel', require('./components/DualMenuPanel').default);
Spork.component('spork-input', require('./components/SporkInput').default);
Spork.component('loading-ascii', require('./components/LoadingAscii').default);
Spork.component('modal', require('./components/Modal').default);

Spork.setupStore({
    Core: require("./store").default,
    Navigation: require('./store/Navigation').default,
    Authentication: require('./store/Authentication').default,
    Feature: require('./store/Feature').default,
});

Spork.routesFor('core', [
  // Spork.authenticatedRoute('/core', require('./core/core').default,
  // Spork.unauthenticatedRoute('/core', require('./core/core').default,
]);
