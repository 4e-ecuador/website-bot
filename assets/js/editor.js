const $ = require('jquery')

require('../css/editor.css')

const jsData = $('#js-data')

const previewUrl = jsData.data('preview-url')
const editorField = jsData.data('editor-field')
const previewField = jsData.data('preview-field')

$('a[data-toggle="tab"]').on('click', function (e) {
    if (previewField === $(e.target).attr('href')) {
        let out = $(previewField);
        out.empty().addClass('loading');
        $.post(
            previewUrl,
            {text: $(editorField).val()},
            function (r) { out.html(r.data).removeClass('loading'); }
        );
    }
});

