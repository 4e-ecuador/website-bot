<template>
    <div>
        <loading v-show="loading"/>
        <h5
            v-show="!loading && agents.length === 0"
            class="text-warning"
        >
            {{translate('no.search.result.criteria')}}
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
import AgentRow from '@/vue/components/agent-list/agent-row'
import Loading from '@/vue/parts/loading'
import {translate} from '@/vue/services/translation-service'

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
    methods: {
        translate(key) {
            return translate(key)
        },
    },
}
</script>
