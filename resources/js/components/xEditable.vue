<template>
    <div class="vue-xeditable">
        <slot name="before" v-if="isRemoteUpdating"></slot>

        <span
            class="vue-xeditable-value"
            :class="{ 'vue-xeditable-empty': $_VueXeditable_isValueEmpty }"
            v-show="!isEditing && !isRemoteUpdating"
            v-on:click="$_VueXeditable_maybeStartEditing(1, $event)"
            v-on:dblclick="$_VueXeditable_maybeStartEditing(2, $event)"
            v-html="$_VueXeditable_getHTMLValue()"
        >
        </span>

        <div v-show="isEditing && !isRemoteUpdating" class="vue-xeditable-control">
            <input
                v-if="type === 'text'"
                class="vue-xeditable-form-control"
                type="text"
                :value="rawValue"
                :placeholder="placeholder"
                @keydown="$_VueXeditable_onKeydown"
                @blur="$_VueXeditable_stopEditing"
                autofocus
            />

            <textarea
                v-else-if="type === 'textarea'"
                class="vue-xeditable-form-control"
                :placeholder="placeholder"
                @keydown="$_VueXeditable_onKeydown"
                @blur="$_VueXeditable_stopEditing"
                :value="rawValue"
            >
            </textarea>

            <input
                v-else-if="type === 'number'"
                class="vue-xeditable-form-control"
                type="number"
                :value="rawValue"
                @keydown="$_VueXeditable_onKeydown"
                @blur="$_VueXeditable_stopEditing"
            />

            <input
                v-else-if="type === 'boolean'"
                class="vue-xeditable-form-control"
                type="checkbox"
                :value="rawValue"
                @change="$_VueXeditable_valueDidChange"
            />
        </div>

        <slot name="after" v-if="isRemoteUpdating"></slot>
    </div>
</template>

<script>
export default {
    name: 'vue-xeditable',
    props: {
        value: {
            type: [String, Number, Array, Boolean, Date],
        },
        type: {
            type: String,
            required: false,
            default: 'text',
        },
        object: {
            type: Object,
            required: false,
        },
        name: {
            type: String,
            required: false,
            default: 'VueXeditableDefaultName',
        },
        empty: {
            type: String,
            required: false,
            default: 'Empty',
        },
        placeholder: {
            type: String,
            required: false,
            default: '',
        },
        options: {
            type: Array,
            default: function() {
                return [];
            },
        },
        editOnDoubleClick: {
            type: Boolean,
            required: false,
            default: false,
        },
        remote: {
            type: Object,
            required: false,
            default: function() {
                return {
                    url: null,
                    method: 'PUT',
                    key: null,
                    resource: null,
                    headers: null,
                };
            },
        },
    },
    data() {
        return {
            isEditing: false,
            isRemoteUpdating: false,
            rawValue: this.value,
        };
    },
    watch: {
        value(newValue) {
            this.rawValue = newValue;
        },
        options(newOptions) {
            this.rawValue = newOptions.find(o => {
                const v = Array.isArray(o) ? o[0] : o;
                return v === this.rawValue;
            });
        },
    },
    computed: {
        $_VueXeditable_isValueEmpty() {
            return this.rawValue === null || this.rawValue === undefined || this.rawValue === '';
        },
        $_VueXeditable_hasRemoteUpdate() {
            return (
                this.remote &&
                this.remote.url &&
                this.remote.url.length &&
                this.remote.key &&
                this.remote.key.length
            );
        },
        $_VueXeditable_hasValidRemote() {
            return (
                this.$_VueXeditable_hasRemoteUpdate &&
                ['PUT', 'POST'].includes(this.remote.method.toUpperCase())
            );
        },
    },
    methods: {
        $_VueXeditable_getHTMLValue() {
            if (this.$_VueXeditable_isValueEmpty) {
                return this.empty;
            } else if (this.type === 'select' && Array.isArray(this.rawValue)) {
                return this.rawValue[this.rawValue.length - 1];
            } else if (this.rawValue === undefined || this.rawValue === null) {
                return '?';
            } else if (this.type === 'date') {
                return this.rawValue.toLocaleString();
            }
            return this.rawValue;
        },
        $_VueXeditable_onKeydown(event) {
            if (!this.isEditing) {
                return;
            }
            if (event.keyCode === 13) {
                this.$_VueXeditable_stopEditing(event);
                this.$_VueXeditable_valueDidChange(event.target.value);
            } else if (event.keyCode === 27) {
                this.$_VueXeditable_stopEditing(event);
            }
        },
        $_VueXeditable_maybeStartEditing(value, event) {
            if (
                (value === 1 && !this.editOnDoubleClick) ||
                (value === 2 && this.editOnDoubleClick)
            ) {
                this.$_VueXeditable_startEditing(event);
            }
        },
        $_VueXeditable_startEditing(event) {
            this.isEditing = true;
            let that = this;
            that.$emit('start-editing', this.rawValue, this.name);
            setTimeout(function() {
                let inputs = Array.from(event.target.nextElementSibling.children);
                inputs.forEach(i => i.focus());
            }, 100);
        },
        $_VueXeditable_stopEditing(event) {
            this.isEditing = false;
            this.$emit('stop-editing', this.rawValue, this.name, event);
        },
        $_VueXeditable_valueDidChange(newValue) {
            if (this.type === 'select' || this.type === 'boolean' || this.type === 'date') {
                this.$_VueXeditable_stopEditing(); // Needed because no events can be associated with select / option?...
            }
            if (this.type === 'boolean') {
                newValue = !this.rawValue;
            }

            if (this.$_VueXeditable_hasValueChanged(newValue) || this.type === 'select') {
                this.$emit('value-will-change', this.rawValue, this.name);

                if (!this.$_VueXeditable_hasRemoteUpdate) {
                    this.$_VueXeditable_makeLocalUpdate(newValue);
                    this.$emit('value-did-change', newValue, this.name, this.object);
                }
            }
        },
        $_VueXeditable_hasValueChanged(newValue) {
            return newValue !== this.rawValue;
        },
        $_VueXeditable_makeLocalUpdate(newValue) {
            this.rawValue = newValue;
        },
    },
};
</script>

<style>
.vue-xeditable {
    color: #222;
    cursor: pointer;
    /*line-height: 2.0em;*/
}

.vue-xeditable:hover {
    color: #666;
}

.vue-xeditable-value {
    white-space: pre-wrap;
    user-select: none;
}

.vue-xeditable-empty {
    color: #ea0002;
    font-style: italic;
}

.vue-xeditable-control {
    width: 100%;
    display: inline-block;
}

.vue-xeditable-form-control {
    width: 100%;
    padding: 5px;
    box-sizing: content-box;
    color: #555;
    background-color: #fff;
    background-image: none;
    outline: none;
}
</style>
