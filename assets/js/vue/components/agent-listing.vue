<template>
    <div>
        <div class="row">
            <div class="col-sm-3">
                {{searchResultCount}}
            </div>
            <div class="col-2 btn-group">
                <button
                    v-if="pagination['hydra:previous']"
                    @click="onPaginateMinus"
                    class="btn btn-outline-secondary"
                >
                    <span class="oi oi-arrow-thick-left"></span>
                </button>
            </div>
            <div class="col-2 btn-group">
                <button
                    v-if="pagination['hydra:next']"
                    @click="onPaginatePlus"
                    class="btn btn-outline-secondary"
                >
                    <span class="oi oi-arrow-thick-right"></span>
                </button>
            </div>
            <div class="col">
                <search-bar @search="onSearchAgents"/>
            </div>
        </div>

        <agent-list
            :agents="agents"
            :loading="loading"
        />
    </div>
</template>

<script>
import AgentList from '@/vue/components/agent-list'
import SearchBar from '@/vue/parts/search-bar'
import {fetchAgents} from '@/vue/services/agents-service'
import {translate,translatePlural} from '@/vue/services/translation-service'

export default {
    name: 'AgentsListing',
    components: {
        AgentList,
        SearchBar,
    },
    data: () => ({
        agents: [],
        totalItems: 0,
        pagination: {},
        pageNum: 1,
        loading: false,
        searchTerm: null,
    }),
    created() {
        this.loadAgents()
    },
    computed: {
        searchResultCount() {
            return translatePlural('search.result', this.totalItems)
                .replace('{count}', this.totalItems)
        },
    },
    methods: {
        onSearchAgents({term}) {
            this.searchTerm = term
            this.pageNum = 1
            this.loadAgents()
        },
        onPaginatePlus() {
            this.pageNum++
            this.loadAgents()
        },
        onPaginateMinus() {
            this.pageNum--
            this.loadAgents()
        },
        async loadAgents() {
            this.loading = true
            let response
            try {
                response = await fetchAgents(this.searchTerm, this.pageNum)
                this.loading = false
            } catch (e) {
                this.loading = false
                return
            }
            this.agents = response.data['hydra:member']
            this.totalItems = response.data['hydra:totalItems']
            this.pagination = response.data['hydra:view']
            // TODO ugly maxpages...
            this.totalPages = this.pagination['hydra:last'] ? this.pagination['hydra:last'].replace(/^\D+/g, '') : 0
        },
    },
}
</script>
