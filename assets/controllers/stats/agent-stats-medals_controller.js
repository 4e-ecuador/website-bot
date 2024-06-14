import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'

import '../../css/stats/agent-stats.css'

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    showModal(e) {
        this._updateModal('medalModal', e)

        const modal = document.getElementById('medalModal')
        modal.querySelector('.medal-value').innerText = e.params.value

        //const level = e.params.level
        for (let i = 1; i < 6; i++) {
            let img = '<span class="medal24 medal-' + e.params.badgenames[i - 1] + '"></span>'

            // @todo find a way to gray out superior medals

            // if (i > level) {
            //     let img = '<span class="medal24 medal-'+$(this).data('badge-name-' + i)+'" style="background: #5C97FF;">a</span>'
            //     // img = '<img src="/build/images/badges/' + $(this).data('badge-name-' + i) + '" style="height: 24px; opacity: 0.3;">'
            // }
            modal.querySelector('.medal-value-' + i).innerHTML = img + e.params.values[i - 1]
        }

        new Modal('#medalModal').show()
    }

    showCustomModal(e) {
        const modal = document.getElementById('medalModal2')

        modal.querySelector('.modal-title').innerText = e.params.name
        modal.querySelector('.modal-header-desc').innerText = e.params.desc
        modal.querySelector('.modal-body').innerHTML = e.target.outerHTML

        this._updateModal('medalModal2', e)

        new Modal('#medalModal2').show()
    }

    _updateModal(name, element) {
        const modal = document.getElementById(name)

        modal.querySelector('.modal-title').innerText = element.params.name
        modal.querySelector('.modal-header-desc').innerText = element.params.desc
        modal.querySelector('.modal-body').innerHTML = element.target.outerHTML
    }
}