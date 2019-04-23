<template>
    <div class="py-4">
        <vue-dropzone
            class="w-full px-4 relative"
            ref="uploadZone"
            id="dropzone"
            :options="dropzoneOptions"
            :useCustomSlot="true"
            v-on:vdropzone-sending="addDiskParam"
            v-on:vdropzone-success="photoUploaded"
        >
            <div class="flex flex-wrap justify-center items-center">
                <h3 class="w-full text-success">{{ __('Drag and drop to upload images') }}</h3>
                <div class="mt-2">...{{ __('or click to select a file from your computer') }}</div>
            </div>
        </vue-dropzone>

        <div id="gallery" class="flex flex-wrap mt-4 mx-auto p-8 bg-white" v-if="photos.length > 0">
            <draggable
                class="w-full flex flex-wrap"
                v-model="photos"
                group="photos"
                @change="updateOrder"
            >
                <template v-for="(photo, index) in photos">
                    <div class="photo flex my-1 px-1 w-1/4  mb-4" :key="'photo_' + index">
                        <div
                            class="relative flex flex-wrap items-end overflow-hidden rounded-lg custom-shadow"
                        >
                            <div class="image relative">
                                <img class="photo-image block h-auto w-full" :src="photo.image" />

                                <div class="overlay flex flex-wrap justify-center items-center">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="cursor-pointer"
                                        @click="showLightbox(photo)"
                                        viewBox="0 0 24 24"
                                        width="48"
                                        height="48"
                                    >
                                        <path
                                            fill="#FFF"
                                            d="M16.32 14.9l5.39 5.4a1 1 0 0 1-1.42 1.4l-5.38-5.38a8 8 0 1 1 1.41-1.41zM10 16a6 6 0 1 0 0-12 6 6 0 0 0 0 12zm1-7h2a1 1 0 0 1 0 2h-2v2a1 1 0 0 1-2 0v-2H7a1 1 0 0 1 0-2h2V7a1 1 0 1 1 2 0v2z"
                                        />
                                    </svg>

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="cursor-pointer"
                                        @click="checkDeleteImage(photo)"
                                        viewBox="0 0 24 24"
                                        width="48"
                                        height="48"
                                    >
                                        <path
                                            fill="#FFF"
                                            d="M8 6V4c0-1.1.9-2 2-2h4a2 2 0 0 1 2 2v2h5a1 1 0 0 1 0 2h-1v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8H3a1 1 0 1 1 0-2h5zM6 8v12h12V8H6zm8-2V4h-4v2h4zm-4 4a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0v-6a1 1 0 0 1 1-1zm4 0a1 1 0 0 1 1 1v6a1 1 0 0 1-2 0v-6a1 1 0 0 1 1-1z"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <div class="w-full">
                                <div
                                    class="flex items-center justify-between leading-tight p-2 md:p-4"
                                >
                                    <h1 class="text-lg w-full">
                                        <vue-xeditable
                                            type="text"
                                            :name="photo.name"
                                            :object="photo"
                                            :value.sync="photo.name"
                                            :empty="__('Photo name')"
                                            @value-did-change="updateName"
                                        />
                                    </h1>
                                </div>

                                <div
                                    class="flex items-center justify-between leading-none p-2 md:p-4"
                                >
                                    <div class="w-full no-underline">
                                        <vue-xeditable
                                            type="textarea"
                                            :name="photo.name"
                                            :object="photo"
                                            :value.sync="photo.description"
                                            :empty="__('Photo Description')"
                                            @value-did-change="updateDescription"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </draggable>
        </div>

        <modal
            ref="modalItem"
            v-on:close="cancelDelete"
            v-if="modalDeletePhoto"
            :name="'modalItem'"
            :align="'flex justify-end'"
        >
            <div slot="container">
                <p>{{ __('Are you sure to delete the image?') }}</p>
            </div>
            <div slot="buttons">
                <button
                    type="button"
                    class="btn text-80 font-normal h-9 px-3 mr-3 btn-link"
                    @click="cancelDelete"
                >
                    {{ __('Cancel') }}
                </button>
                <button type="submit" class="btn btn-default btn-danger" @click="deletePhoto">
                    <span>{{ __('Yes, delete') }}</span>
                </button>
            </div>
        </modal>

        <lightbox
            id="albumLightbox"
            ref="lightbox"
            :images="images"
            :filter="filter"
            :timeoutDuration="5000"
        ></lightbox>
    </div>
</template>

<script>
import vue2Dropzone from 'vue2-dropzone';
import draggable from 'vuedraggable';
import Lightbox from 'vue-my-photos';

//Local
import lazyload from './LazyLoad.js';
import Xeditable from './XEditable';
import Modal from './Modal';

import 'vue2-dropzone/dist/vue2Dropzone.min.css';

export default {
    name: 'Album',

    props: ['resourceName', 'resourceId', 'field'],
    components: {
        vueDropzone: vue2Dropzone,
        'vue-xeditable': Xeditable,
        draggable: draggable,
        modal: Modal,
        lightbox: Lightbox,
    },
    data: () => ({
        photos: [],
        dropzoneOptions: {
            url: '/nova-vendor/nova-gallery/upload-files',
            thumbnailWidth: 200,
            maxFilesize: 50,
            headers: {
                'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
            },
        },
        modalDeletePhoto: false,
        photoToDeleteIndex: null,
        photoToDelete: null,
        lazyLoadImages: null,
        filter: '',
    }),

    methods: {
        getPhotos() {
            Nova.request()
                .get('/nova-vendor/nova-gallery/' + this.resourceId + '/photos')
                .then(response => {
                    if (response.data) {
                        this.photos = response.data;
                    }
                    this.$nextTick(() => {
                        lazyload.update();
                    });
                })
                .catch(() => {
                    this.$toasted.show(this.__('Error loading photos!'), { type: 'error' });
                });
        },

        getbackgroundImage(photo) {
            return 'background-image: url("' + photo.image + '")';
        },

        addDiskParam(file, xhr, formData) {
            formData.append('album', this.resourceId);
        },

        photoUploaded(file, response) {
            if (response.success) {
                let photo = response.photo;
                this.photos.push(photo);
                this.$refs.uploadZone.removeFile(file);
                this.$nextTick(() => {
                    lazyload.update();
                });
            }
        },

        updateName(value, name, photo) {
            Nova.request()
                .put('/nova-vendor/nova-gallery/photo/' + photo.id + '/name', {
                    data: value,
                })
                .then(response => {
                    if (response.data && response.data.success) {
                        photo.name = value;
                        this.$toasted.show(this.__('Name updated!'), { type: 'success' });
                    } else {
                        this.$toasted.show(this.__('Error updating!'), { type: 'error' });
                    }
                })
                .catch(() => {
                    this.$toasted.show(this.__('Error updating!'), { type: 'error' });
                });
        },

        updateDescription(value, name, photo) {
            Nova.request()
                .put('/nova-vendor/nova-gallery/photo/' + photo.id + '/description', {
                    data: value,
                })
                .then(response => {
                    if (response.data && response.data.success) {
                        photo.description = value;
                        this.$toasted.show(this.__('Description updated!'), { type: 'success' });
                    } else {
                        this.$toasted.show(this.__('Error updating!'), { type: 'error' });
                    }
                })
                .catch(() => {
                    this.$toasted.show(this.__('Error updating!'), { type: 'error' });
                });
        },

        updateOrder() {
            let order = 1;
            let newPhotosOrder = [];
            this.photos.forEach(photo => {
                newPhotosOrder.push({
                    id: photo.id,
                    position: order++,
                });
            });
            Nova.request()
                .post('/nova-vendor/nova-gallery/photo/order', {
                    data: newPhotosOrder,
                })
                .then(response => {
                    if (response.data && response.data.success) {
                        this.$toasted.show(this.__('Order updated!'), { type: 'success' });
                        return true;
                    } else {
                        this.$toasted.show(this.__('Error ordering!'), { type: 'error' });
                        return false;
                    }
                })
                .catch(() => {
                    this.$toasted.show(this.__('Error ordering!'), { type: 'error' });
                    return false;
                });
        },

        showLightbox: function(photo) {
            this.filter = 'image_' + photo.id;
            this.$refs.lightbox.show('image_' + photo.id);
        },

        checkDeleteImage(photo, index) {
            this.photoToDelete = photo;
            this.photoToDeleteIndex = index;
            this.modalDeletePhoto = true;
        },

        deletePhoto() {
            Nova.request()
                .delete('/nova-vendor/nova-gallery/photo/' + this.photoToDelete.id)
                .then(response => {
                    if (response.data && response.data.success) {
                        this.$toasted.show(this.__('Image deleted!'), { type: 'success' });
                        this.removePhotoFromPhotos(this.photoToDelete);
                        this.photoToDelete = null;
                    } else {
                        this.$toasted.show(this.__('Error deleting the photo!'), { type: 'error' });
                    }
                    this.cancelDelete();
                })
                .catch(() => {
                    this.$toasted.show(this.__('Error deleting the photo!'), { type: 'error' });
                    this.cancelDelete();
                });
        },

        cancelDelete() {
            this.modalDeletePhoto = null;
            this.photoToDeleteIndex = null;
            this.photoToDelete = null;
        },

        removePhotoFromPhotos(photoToDelete) {
            this.photos.splice(this.photos.findIndex(({ id }) => id === photoToDelete.id), 1);
        },
    },

    computed: {
        images: function() {
            if (this.photos.length > 0) {
                let images = [];
                this.photos.forEach(photo => {
                    images.push({
                        name: photo.image,
                        id: 'image_' + photo.id,
                        filter: 'image_' + photo.id,
                    });
                });

                return images;
            }

            return [];
        },
    },

    mounted() {
        lazyload.init();
        this.getPhotos();
    },
};
</script>

<style>
.custom-shadow {
    -webkit-box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.2);
    box-shadow: 0 2px 4px 0 rgba(0, 0, 0, 0.2);
}

.overlay {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100%;
    width: 100%;
    opacity: 0;
    transition: 0.3s ease;
    background-color: rgb(0, 0, 0);
    background-color: rgba(0, 0, 0, 0.3);
    filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000);
    -ms-filter: 'progid:DXImageTransform.Microsoft.gradient(startColorstr=#99000000, endColorstr=#99000000)';
}

.photo:hover .overlay {
    opacity: 1;
}

.photo .image {
    overflow: hidden;
    max-height: 250px;
}

.photo-image {
    visibility: hidden;
    opacity: 0;
    transition: opacity 1s;
}

.loaded {
    opacity: 1;
    visibility: visible;
}
</style>
