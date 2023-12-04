import './bootstrap';

import './onloads/lazyload.js';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function route(name) {
    if (window.app.debug === true) {
        console.log('Route', name, window.app.routes[name]);
    }
    return window.app.routes[name];
}

export { route };
