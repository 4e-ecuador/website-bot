<template>
    <div>
        <loading v-show="loading"/>
        <h5
            v-show="!loading && agents.length === 0"
            class="text-warning"
        >
            No se encontraron agentes con el criterio de busqueda!
        </h5>

        <table class="table table-sm table-hover">
            <thead>
            <tr>
                <th>Nickname</th>
                <th>Name</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <agent-row
                v-show="!loading"
                v-for="agent in agents"
                :key="agent['@id']"
                :agent="agent"
            />
            </tbody>
        </table>
    </div>
</template>

<script>
import AgentRow from '@/components/agent-list/agent-row'
import Loading from '@/parts/loading'

export default {
    name: 'AgentList',
    components: {
        AgentRow,
        Loading,
    },
    props: {
        loading: {
            type: Boolean,
            required: true,
        },
        agents: {
            type: Array,
            required: true,
        },
    },
}
</script>
