<template>
    <div>
        <loading v-show="loading"/>
        <h5
            v-show="!loading && users.length === 0"
            class="text-warning"
        >
            {{translate('no.search.result.criteria')}}
        </h5>

        <table class="table table-sm table-hover">
            <thead>
            <tr>
                <th>e-mail</th>
                <th>Agent</th>
                <th>Roles</th>
            </tr>
            </thead>
            <tbody>
            <user-row
                v-show="!loading"
                v-for="user in users"
                :key="user['@id']"
                :user="user"
            />
            </tbody>
        </table>
    </div>
</template>

<script>
import UserRow from '@/vue/components/users-list/user-row'
import Loading from '@/vue/parts/loading'
import {translate} from '@/vue/services/translation-service'

export default {
    name: 'UsersList',
    components: {
        UserRow,
        Loading,
    },
    props: {
        loading: {
            type: Boolean,
            required: true,
        },
        users: {
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
