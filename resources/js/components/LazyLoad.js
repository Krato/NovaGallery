import LazyLoad from 'vanilla-lazyload';
var myLazyLoad;

let lazyload = {
    init() {
        myLazyLoad = new LazyLoad({
            elements_selector: '.photo-image',
            threshold: 0,
        });
    },

    update() {
        let list = document.querySelectorAll('.photo-image:not([class="loaded"])');
        list.forEach(item => {
            myLazyLoad.load(item);
        });
    },
};

export default lazyload;
