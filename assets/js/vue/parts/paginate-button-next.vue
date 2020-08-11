<template>
    <button
        @click="onClick"
        class="btn btn-outline-secondary"
        :title="translate('paginate.next.page')"
    >
        <span class="oi oi-arrow-thick-right"></span>
    </button>
</template>

<script>
import {translate} from '@/vue/services/translation-service'

export default {
    name: 'PaginateButtonNext',
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
            this.$emit('paginate-next', {term: this.link})
        },
    },
}
</script>
