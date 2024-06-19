import { Controller } from '@hotwired/stimulus'

const TinyMDE = require('tiny-markdown-editor')
import '../styles/tiny-mde.css'

import { Modal } from 'bootstrap'

export default class extends Controller {
    static values = {
        previewUrl: String
    }

    static targets = ['preview']

    connect() {
        const tinyMDE = new TinyMDE.Editor({ element: this.element.querySelector('textarea') })
        const commandBar1 = new TinyMDE.CommandBar({
            element: 'tinymde_commandbar',
            editor: tinyMDE,
            commands: [
                {
                    name: 'preview',
                    title: 'Show a preview',
                    innerHTML: '<span class="oi oi-eye"></span>',
                    action: editor => this.preview(editor)
                },
                'bold', 'italic', 'strikethrough', '|', 'code', '|', 'h1', 'h2', '|', 'ul', 'ol',
                '|', 'blockquote', 'hr', '|', 'insertLink', 'insertImage']
        })
    }

    async preview(editor) {
        const formData = new FormData()
        formData.append('text', editor.getContent())

        const response = await fetch(this.previewUrlValue, {
            method: 'POST',
            body: formData,
        })

        const data = await response.json()
        this.previewTarget.innerHTML = data.data
        const modal = new Modal('#previewModal')
        modal.show()
        modal.handleUpdate()
    }
}
