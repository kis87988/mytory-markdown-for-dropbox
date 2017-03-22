// trigger append event when append.
(function ($) {
    var origAppend = $.fn.append;

    $.fn.append = function () {
        return origAppend.apply(this, arguments).trigger("append");
    };
})(jQuery);

jQuery(function ($) {
    var dropbox;

    function initDropbox() {
        var accessToken = $('#mm4d-access-token').val();
        if (accessToken) {
            dropbox = new Dropbox({accessToken: 'qMVnmVlROZwAAAAAAADO6yhOibLJue8t8XawDB8tEr7cCY94xJO8R99rjsJ6Dgbe'});
        }
    }

    function drawList(path, dropbox) {
        var $content = $('#TB_ajaxContent');
        var $title = $('#TB_ajaxWindowTitle');
        var template = _.template($('#template-mm4d-li').html());
        dropbox.filesListFolder({path: path})
            .then(function (response) {
                console.log(response);
                if (path) {
                    $title.text(path);
                }
                $content.html('');
                var $ul = $('<ul>');
                _.forEach(response.entries, function (entry) {
                    var extension = entry.name.substr(entry.name.lastIndexOf('.') + 1);
                    if (entry['.tag'] == 'file' && ['txt', 'md', 'markdown', 'mdown'].indexOf(extension) === -1) {
                        return true;
                    }
                    $ul.append(template({
                        name: entry.name,
                        tag: entry['.tag'],
                        id: entry.id,
                        path: entry.path_display
                    }));
                });
                $ul.appendTo($content);
            })
            .catch(function (error) {
                console.log(error);
            });
    }

    function initFirstOpen() {
        $('body').one('append', '#TB_window', function (e) {
            drawList('', dropbox);
            initSelect();
        });
    }

    function initSelect() {
        $('#TB_window').on('click', '.js-mm4d-select', function (e) {
            selectFile(this);
        });
    }

    function selectFile(el) {
        var id = $(el).data('id');
        $('#mm4d-path').val(id);
        $.post(ajaxurl, {
            action: 'mm4d_get_converted_content',
            id: id
        }, function (response) {
            if (typeof response['is_error'] != 'undefined' && response['is_error'] == true) {
                alert(response.msg);
            } else {
                console.log(response);
            }
        }, 'json');
    }

    initDropbox();
    initFirstOpen();
});