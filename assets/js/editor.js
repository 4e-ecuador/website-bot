const $ = require('jquery')

require('../css/editor.css')

$('a[data-toggle="tab"]').on('click', function (e) {
    let url = $('#js-preview-url').data('url')
    if ('#previewId' === $(e.target).attr('href')) {
        preview('#help_text', '#previewId', url);
    }
});

function preview(text, preview, previewUrl) {
    let out = $(preview);

    out.empty().addClass('loading');

    $.post(
        previewUrl,
        {text: $(text).val()},
        function (r) { out.html(r.data).removeClass('loading'); }
    );
}

