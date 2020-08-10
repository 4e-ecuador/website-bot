<template>
    <div>
        <div class="row">
            <div class="col-sm-3">
                {{searchResultCount}}
            </div>
            <div class="col-2 btn-group">
                <button
                    v-if="pagination['hydra:previous']"
                    @click="onPaginatePrevious"
                    class="btn btn-outline-secondary"
                >
                    <span class="oi oi-arrow-thick-left"></span>
                </button>
            </div>
            <div class="col-2 btn-group">
                <button
                    v-if="pagination['hydra:next']"
                    @click="onPaginateNext"
                    class="btn btn-outline-secondary"
                >
                    <span class="oi oi-arrow-thick-right"></span>
                </button>
            </div>
            <div class="col">
                <search-bar @search="onSearchAgents"/>
            </div>
        </div>

        <users-list
            :users="users"
            :loading="loading"
        />
    </div>
</template>

<script>
import UsersList from '@/vue/components/users-list'
import SearchBar from '@/vue/parts/search-bar'
import {fetchUsers} from '@/vue/services/users-service'
import {translate,translatePlural} from '@/vue/services/translation-service'

export default {
    name: 'UsersListing',
    components: {
        UsersList,
        SearchBar,
    },
    data: () => ({
        users: [],
        totalItems: 0,
        pagination: {},
        pageNum: 1,
        loading: false,
        searchTerm: null,
    }),
    created() {
        this.loadItems()
    },
    computed: {
        searchResultCount() {
            return translatePlural('search.result', this.totalItems)
                .replace('{count}', this.totalItems)
        },
    },
    methods: {
        translate(key) {
            return translate(key)
        },
        onSearchAgents({term}) {
            this.searchTerm = term
            // Reset pageNum
            this.pageNum = 1
            this.loadItems()
        },
        onPaginateNext() {
            this.pageNum++
            this.loadItems()
        },
        onPaginatePrevious() {
            this.pageNum--
            this.loadItems()
        },
        async loadItems() {
            this.loading = true
            let response
            try {
                response = await fetchUsers(this.searchTerm, this.pageNum)
                this.loading = false
            } catch (e) {
                this.loading = false
                return
            }
            this.users = response.data['hydra:member']
            this.totalItems = response.data['hydra:totalItems']
            this.pagination = response.data['hydra:view']
        },
    },
}
</script>
