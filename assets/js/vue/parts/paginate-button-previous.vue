<template>
    <button
        @click="onClick"
        class="btn btn-outline-secondary"
        :title="translate('paginate.previous.page')"
    >
        <span class="oi oi-arrow-thick-left"></span>
    </button>
</template>

<script>
import {translate} from '@/vue/services/translation-service'

export default {
    name: 'PaginateButtonPrevious',
    props: {
        link: {
            type: String,
            required: true,
        },
    },
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
        onClick() {
            this.$emit('paginate-previous', {term: this.link})
        },
    },
}
</script>
