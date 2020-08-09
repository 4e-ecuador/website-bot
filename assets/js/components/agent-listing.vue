<template>
    <div>
        <div class="row">
            <div class="col-sm-3">
                <span v-if="totalItems === 1">
                    Un agente encontrado
                </span>
                <span v-else>
                    {{ totalItems }} agentes encontrados
                </span>
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
                <search-bar @search-agents="onSearchAgents"/>
            </div>
        </div>

        <agent-list
            :agents="agents"
            :loading="loading"
        />
    </div>
</template>

<script>
import AgentList from '@/components/agent-list'
import {fetchAgents} from '@/services/agents-service'
import SearchBar from '@/parts/search-bar'

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
        totalPages: 0,
        loading: false,
        searchTerm: null,
    }),
    created() {
        this.loadAgents()
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
