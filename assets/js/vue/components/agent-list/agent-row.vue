<template>
    <tr>
        <td>
            <a :href="'/agent/'+agent.id">{{ agent.nickname }}</a>
        </td>
        <td>{{ agent.realName }}</td>
        <td>
            <a :href="'/stats/agent/'+agent.id"><span class="oi oi-graph"></span></a>
        </td>
    </tr>
</template>

<script>
export default {
    name: 'AgentRow',
    props: {
        agent: {
            type: Object,
            required: true,
        },
    },
}
</script>
