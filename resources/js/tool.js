Nova.booting((Vue, router) => {
    router.addRoutes([
        {
            name: 'nova-gallery',
            path: '/nova-gallery',
            component: require('./components/Tool'),
        },
    ]);

    Vue.component('album', require('./components/Album'));
});
