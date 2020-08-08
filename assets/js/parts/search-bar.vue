<template>
    <div class="input-group">
        <input
            v-model="searchTerm"
            class="form-control"
            placeholder="Buscar agentes..."
            type="search"
            @input="onInput"
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
export default {
    name: 'SearchBar',
    data() {
        return {
            searchTerm: '',
            searchTimeout: null,
        }
    },
    methods: {
        onInput() {
            if (this.searchTimeout) {
                clearTimeout(this.searchTimeout)
            }
            this.searchTimeout = setTimeout(() => {
                this.$emit('search-agents', {term: this.searchTerm})
                this.searchTimeout = null
            }, 200)
        },
        eraseSearchTerm() {
            this.searchTerm = ''
            this.$emit('search-agents', {term: this.searchTerm})
        },
    },
}
</script>
