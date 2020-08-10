<template>
    <div class="input-group">
        <input
            :value="searchTerm"
            class="form-control"
            :placeholder="translate('searchbar.placeholder')"
            type="search"
            @input="searchTerm = $event.target.value;onInput()"
        >
        <div
            class="input-group-append"
            v-show="searchTerm !== ''"
        >
            <button
                class="btn btn-outline-success"
                @click="eraseSearchTerm"
            >
                X
            </button>
        </div>
    </div>
</template>

<script>
import {translate} from '@/vue/services/translation-service'

export default {
    name: 'SearchBar',
    data() {
        return {
            searchTerm: '',
            searchTimeout: null,
        }
    },
    methods: {
        translate(key) {
            return translate(key)
        },
        onInput() {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout)
            }
            this.searchTimeout = setTimeout(() => {
                this.$emit('search', {term: this.searchTerm})
                this.searchTimeout = null
            }, 200)
        },
        eraseSearchTerm() {
            this.searchTerm = ''
            this.$emit('search', {term: this.searchTerm})
        },
    },
}
</script>
