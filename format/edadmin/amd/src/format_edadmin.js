/**
 * Javascript containing utils function of the format edadmin plugin
 */

define([
    'jquery',
    'core/str'
], function ($, str) {
    var format_edadmin = {
        /**
         * Send an ajax request
         * @param {object} params
         */
        ajax_call: function (params) {
            var that = this;

            //check optional params
            var datatype = params.datatype ? params.datatype : 'html';
            var method = params.method ? params.method : 'GET';
            var url = params.url ? params.url : M.cfg.wwwroot + '/course/format/edadmin/ajax/ajax.php';
            var callback = params.callback ? params.callback : null;
            var callbackerror = params.error ? params.error : null;

            //delete params to not send them in the request
            delete params.datatype;
            delete params.method;
            delete params.url;
            delete params.callback;

            $.ajax({
                method: method,
                url: url,
                data: params,
                dataType: datatype,
                error: function (jqXHR, error, errorThrown) {
                    if (typeof callbackerror === 'function') {
                        callbackerror(jqXHR, error, errorThrown);
                    } else if ((jqXHR.responseText.length > 0) && (jqXHR.responseText.indexOf('pagelayout-login') !== -1)) {
                        that.redirect_login();
                    } else {
                        that.error_popup();
                    }
                }
            }).done(function (response) {
                if ((response.length > 0) && (response.indexOf('pagelayout-login') !== -1)) {
                    that.redirect_login();
                }

                if (typeof callback === 'function') {
                    callback(response);
                }
            });
        },
        redirect_login: function () {
            window.location.href = M.cfg.wwwroot + '/login/index.php';
        },
        error_popup: function () {
            var params = {
                title: M.util.get_string('error', 'moodle'),
                modalid: 'error-popup-modal',
                contentid: 'error-popup',
                content: M.util.get_string('pleaserefresh', 'format_edadmin'),
                close: function () {
                    window.location.reload();
                },
                buttons: [
                    {
                        text: 'OK',
                        onclick: function () {
                            window.location.reload();
                        }
                    }
                ]
            };
            this.open_modal(params);
        },
        /**
         * Open content in a modal div
         * @param {object} params with attributes : content, title, modalid, contentid
         */
        open_modal: function (params) {
            $('body').css('overflow', 'hidden');
            var modalid = params.modalid ? 'id="' + params.modalid + '"' : '';
            var contentid = params.contentid ? 'id="' + params.contentid + '"' : '';
            var overlay = '<div class="overlay"></div>';
            var close = '<div class="close"></div>';
            var $edadminmodal = $('<div ' + modalid + ' class="edadmin-modal">' + overlay + '</div>');
            var $modalcontent = $('<div ' + contentid + ' class="modal-content"><div class="header">' + params.title + '' + close + '</div>');
            var $maincontent = $('<div class="modal-main-content">' + params.content + '</div>');
            $edadminmodal.append($modalcontent);
            $modalcontent.append($maincontent);
            $('body').append($edadminmodal);
            if (params.buttons) {
                var $buttons = $('<div class="buttons"></div>');
                $maincontent.append($buttons);
                for (var buttonindex in params.buttons) {
                    var button = params.buttons[buttonindex];
                    var text = encodeURIComponent(button.text);
                    var $button = document.createElement('button');
                    $button.classList.add('edadmin-button');
                    $button.textContent = decodeURIComponent(text);
                    $button.data = button;
                    $button.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.currentTarget.data.onclick();
                    });
                    $buttons.append($button);
                }
            }
            $('#' + params.modalid + ' .close').on('click', function () {
                if (params.close != null) {
                    params.close();
                } else {
                    $('.edadmin-modal').hide();
                }
                $('body').css('overflow', 'auto');
            });
            // resize
            var modaleminheight = $('#' + params.modalid + ' .modal-content').height();
            var modaleminwidth = $('#' + params.modalid + ' .modal-content').width();
            $(window).resize(function () {
                //height
                if (modaleminheight > $(window).height()) {
                    $('#' + params.modalid + ' .modal-content').css('height', 'calc(100% - 20px)');
                } else {
                    $('#' + params.modalid + ' .modal-content').css('height', '');
                }

                // width
                if (modaleminwidth > $(window).width()) {
                    $('#' + params.modalid + ' .modal-content').css('width', 'calc(100% - 20px)');
                    $('#' + params.modalid + ' .modal-content').css('min-width', '0');
                } else {
                    $('#' + params.modalid + ' .modal-content').css('width', '');
                    $('#' + params.modalid + ' .modal-content').css('min-width', '');
                }
            });
            $(window).trigger('resize');
        },
        /**
         * Error Modal
         *
         * @param {string} message
         */
        error_modal: function (message) {

            $("<div></div>").dialog({
                width: 600,
                title: 'Error',
                open: function () {
                    var markup = '<div class="modal-error-message">' + message + '</div>';
                    $(this).html(markup);
                },
                buttons: [//Modal buttons
                    {
                        text: 'ok',
                        click: function () {//Just close the modal
                            $(this).dialog("close");
                        }
                    },
                ]
            });

        },
        /**
         * Success Modal
         *
         * @param {string} message
         */
        success_modal: function (message) {
            $("<div></div>").dialog({
                width: 600,
                title: 'Modification réussie',
                modal:true,
                open: function () {
                    var markup = '<div class="modal-success-message">' + message + '</div>';
                    $(this).html(markup);
                },
                buttons: [//Modal buttons
                    {
                        text: 'Ok',
                        click: function () {//Just close the modal
                            $(this).dialog("close");
                        }
                    },
                ]
            });
        },
        /**
         * Remove an element {value} in an array {arr}
         *
         * @param {Array} arr
         * @param {*} value
         * @returns {Array} arr
         */
        arrayRemove: function (arr, value) {
            return arr.filter(
                function (ele) {
                    return ele != value;
                });
        },
        selectEntity: function () {
            $('#entity-select').change(function (e) {
                window.location.replace($(this).val());
            });
        }
    };


    //add object to window to be called outside require
    window.format_edadmin = format_edadmin;
    return format_edadmin;
});
