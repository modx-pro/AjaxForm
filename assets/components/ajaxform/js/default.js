var AjaxForm = {
    initialize: function (afConfig) {
        var script;
        if (!jQuery().ajaxForm) {
            script = document.createElement('script');
            script.src = afConfig['assetsUrl'] + 'js/lib/jquery.form.min.js';
            document.body.appendChild(script);
        }

        var jGrowlSetup = function () {
            $.jGrowl.defaults.closerTemplate = '<div>[ ' + afConfig['closeMessage'] + ' ]</div>';
        };
        if (!jQuery().jGrowl) {
            script = document.createElement('script');
            script.src = afConfig['assetsUrl'] + 'js/lib/jquery.jgrowl.min.js';
            script.onload = jGrowlSetup;
            document.body.appendChild(script);
        } else {
            $(document).ready(function () {
                jGrowlSetup();
            });
        }

        $(document).off('submit', afConfig['formSelector']).on('submit', afConfig['formSelector'], function (e) {
            var $submitter = undefined;

            $(this).ajaxSubmit({
                dataType: 'json',
                data: {pageId: afConfig['pageId']},
                url: afConfig['actionUrl'],
                beforeSerialize: function (form) {
                    if (e.originalEvent.submitter) {
                        $submitter = $(e.originalEvent.submitter);
                        $submitter.each(function () {
                            var $submit = $(this);
                            if (!$submit.attr('name')) {
                                return;
                            }
                            if (!form.find('input[type="hidden"][name="' + $submit.attr('name') + '"]').length) {
                                $(form).append(
                                    $('<input type="hidden">').attr({
                                        name: $submit.attr('name'),
                                        value: $submit.attr('value'),
                                    })
                                );
                            }
                        });
                    }
                },
                beforeSubmit: function (fields, form) {
                    //noinspection JSUnresolvedVariable
                    if (typeof(afValidated) != 'undefined' && afValidated == false) {
                        return false;
                    }
                    form.find('.error').html('');
                    form.find('.error').removeClass('error');
                    form.find('input,textarea,select,button').attr('disabled', true);
                    return true;
                },
                success: function (response, status, xhr, form) {
                    form.find('input,textarea,select,button').attr('disabled', false);

                    response.form = form;
                    $(document).trigger('af_complete', response);

                    if ($submitter && $submitter.length) {
                        $submitter.each(function () {
                            var $submit = $(this);
                            if (!$submit.attr('name')) {
                                return;
                            }
                            let $hidden = form.find('input[type="hidden"][name="' + $submit.attr('name') + '"]');
                            $hidden.length && $hidden.remove();
                        });
                        $submitter = undefined;
                    }

                    if (!response.success) {
                        AjaxForm.Message.error(response.message);
                        if (response.data) {
                            var key, value, focused;
                            for (key in response.data) {
                                if (response.data.hasOwnProperty(key)) {
                                    if (!focused) {
                                        form.find('[name="' + key + '"]').focus();
                                        focused = true;
                                    }
                                    value = response.data[key];
                                    form.find('.error_' + key).html(value).addClass('error');
                                    form.find('[name="' + key + '"]').addClass('error');
                                }
                            }
                        }
                    }
                    else {
                        AjaxForm.Message.success(response.message);
                        form.find('.error').removeClass('error');
                        if (!!afConfig.clearFieldsOnSuccess) {
                            form[0].reset();
                        }
                        //noinspection JSUnresolvedVariable
                        if (typeof(grecaptcha) != 'undefined') {
                            //noinspection JSUnresolvedVariable
                            grecaptcha.reset();
                        }
                    }
                }
            });
            e.preventDefault();
            return false;
        });

        $(document).on('keypress change', '.error', function () {
            var key = $(this).attr('name');
            $(this).removeClass('error');
            $('.error_' + key).html('').removeClass('error');
        });

        $(document).on('reset', afConfig['formSelector'], function () {
            $(this).find('.error').html('');
            AjaxForm.Message.close();
        });
    }

};


//noinspection JSUnusedGlobalSymbols
AjaxForm.Message = {
    success: function (message, sticky) {
        if (message) {
            if (!sticky) {
                sticky = false;
            }
            $.jGrowl(message, {theme: 'af-message-success', sticky: sticky});
        }
    },
    error: function (message, sticky) {
        if (message) {
            if (!sticky) {
                sticky = false;
            }
            $.jGrowl(message, {theme: 'af-message-error', sticky: sticky});
        }
    },
    info: function (message, sticky) {
        if (message) {
            if (!sticky) {
                sticky = false;
            }
            $.jGrowl(message, {theme: 'af-message-info', sticky: sticky});
        }
    },
    close: function () {
        $.jGrowl('close');
    },
};
