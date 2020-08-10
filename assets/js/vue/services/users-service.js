import axios from 'axios';

/**
 * @param {string|null} searchTerm
 * @param {int|null} pageNum
 * @returns {Promise}
 */
export function fetchUsers(searchTerm, pageNum) {
    const params = {};
    if (searchTerm) {
        params.email = searchTerm;
    }

    if (pageNum) {
        params.page = pageNum;
    }

    // TODO fixed faction
    params.faction = 1

    return axios.get('/api/users', {
        params,
    });
}
