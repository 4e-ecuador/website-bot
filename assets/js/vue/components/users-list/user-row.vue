<template>
    <tr>
        <td>
            <a :href="'/user/'+user.id" title="Show User">
                {{ user.email }}
            </a>
            &nbsp;
            <a :href="'/user/'+user.id+'/edit'" title="Edit User">
                <span class="oi oi-pencil text-warning"></span>
            </a>
        </td>
        <td>
            <span v-if="user.agent">
                <a :href="'/agent/'+user.agent.id" title="Edit Agent">
                    {{ user.agent.nickname }}
                </a>
                &nbsp;
                <a :href="'/agent/'+user.agent.id+'/edit'" title="Edit Agent">
                    <span class="oi oi-pencil text-warning"></span>
                </a>
            </span>
            <span v-else>
            ---
            </span>
        </td>
        <td :class="roleClass">
            {{ roles }}
        </td>
    </tr>
</template>

<script>
export default {
    name: 'UserRow',
    props: {
        user: {
            type: Object,
            required: true,
        },
    },
    computed: {
        roleClass() {
            switch (this.roles) {
                case 'Admin':
                    return 'badge badge-danger'
                case 'Editor':
                    return 'badge badge-warning'
                case 'Agent':
                    return 'badge badge-success'
                default:
                    return 'badge badge-info'
            }
        },
        roles() {
            if (!this.user.roles.length) {
                return 'XXX'
            }
            let roles = []
            this.user.roles.forEach(function (item) {
                switch (item) {
                    case 'ROLE_USER':
                        break
                    case 'ROLE_AGENT':
                        roles.push('Agent')
                        break
                    case 'ROLE_EDITOR':
                        roles.push('Editor')
                        break
                    case 'ROLE_ADMIN':
                        roles.push('Admin')
                        break
                    default:
                        roles.push(item)
                }
            })

            return roles.join(', ')
        }
    },
}
</script>
