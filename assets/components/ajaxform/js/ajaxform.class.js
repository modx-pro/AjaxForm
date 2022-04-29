export default class AjaxForm {
    constructor(selector, config) {

        this.form = document.querySelector(selector);
        if (!this.form) {
            console.error('Form with selector '+selector+' not found. Check the correctness of the selector.');
            return false;
        }

        this.defaults = {
            clearFieldsOnSuccess: true,
            actionUrl: 'assets/components/ajaxform/action.php',
            pageId: 1,
            fileUplodedProgressMsg: '',
            fileUplodedSuccessMsg: '',
            fileUplodedErrorMsg: '',
            ajaxErrorMsg: '',
            showUplodedProgress: false
        }

        this.config = Object.assign({}, this.defaults, config);

        // adding the necessary handlers
        this.addHandlers(['submit', 'reset'], 'Form');
    }


    addHandlers(handlers, postfix) {
        handlers.forEach(handler => {
            this.form.addEventListener(handler, this['on' + handler + postfix].bind(this));
        });
    }

    onsubmitForm(e) {
        e.preventDefault();
        if (this.beforeSubmit(e.target)) {
            this.beforeSerialize(e.target, e.submitter);
            const params = new FormData(e.target);
            params.append('pageId', this.config.pageId);
            this.sendAjax(this.config.actionUrl, params, this.responseHandler.bind(this), e.target);
        }
    }

    onresetForm(e) {
        if (SweetAlert2.Message !== undefined) {
            SweetAlert2.Message.close();
        }
        const currentErrors = e.target.querySelectorAll('.error');
        if (currentErrors.length) {
            currentErrors.forEach(this.resetErrors);
        }
    }

    resetErrors(e) {
        const elem = e.target || e,
            form = elem.closest('form');
        elem.classList.remove('error');
        if (elem.name && form.length) {
            form.querySelector('.error_' + elem.name).innerHTML = '';
        }
    }

    // function rewritten in pure js from the original file
    // i don't know what this function is for
    beforeSerialize(form, submitter) {
        if(submitter !== undefined && submitter.name){
            let submitVarInput = form.querySelector('input[type="hidden"][name="' + submitter.name + '"]');
            if (!submitVarInput) {
                submitVarInput = document.createElement('input');
                submitVarInput.setAttribute('type', 'hidden');
                submitVarInput.setAttribute('name', submitter.name);
                submitVarInput.setAttribute('value', submitter.value);
                form.appendChild(submitVarInput);
            }
        }
    }

    beforeSubmit(form) {
        const currentErrors = form.querySelectorAll('.error');
        if (currentErrors.length) currentErrors.forEach(this.resetErrors);
        return true;
    }

    // Submitting a form
    sendAjax(path, params, callback, form) {
        const request = new XMLHttpRequest();
        const url = path || document.location.href;
        const $this = this;

        request.open('POST', url, true);
        request.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        request.responseType = 'json';

        if (form.querySelector('input[type="file"]') && this.config.showUplodedProgress) {
            request.upload.onprogress = function (e) {
                $this.onUploadProgress(e, form)
            };
            request.upload.onload = function (e) {
                $this.onUploadFinished(e, form)
            };
            request.upload.onerror = function (e) {
                $this.onUploadError(e, form)
            };
        }

        request.addEventListener('readystatechange', function () {
            form.querySelectorAll('input,textarea,select,button').forEach(el => el.disabled = true);
            if (request.readyState === 4 && request.status === 200) {
                callback(request.response, request.response.success, request, form);
            } else if(request.readyState === 4 && request.status !== 200) {
                if (SweetAlert2.Message !== undefined) {
                    SweetAlert2.Message.error($this.config.ajaxErrorMsg);
                }
            }
        });
        request.send(params);
    }

    // handler server response
    responseHandler(response, status, xhr, form) {
        const event = new CustomEvent('af_complete', {
            cancelable: true,
            bubbles: true,
            detail: {response: response, status: status, xhr: xhr, form: form},
        });
        const cancelled = document.dispatchEvent(event);

        form.querySelectorAll('input,textarea,select,button').forEach(el => el.disabled = false);

        if (cancelled) {
            if (!status) {
                this.onError(response, status, xhr, form);
            } else {
                this.onSuccess(response, status, xhr, form);
            }
        }else{
            return false;
        }
    }

    // handler server success response
    onSuccess(response, status, xhr, form) {
        if (SweetAlert2.Message !== undefined) {
            SweetAlert2.Message.success(response.message);
        }

        form.querySelectorAll('.error').forEach(el => {
            if (el.name) {
                el.removeEventListener('keydown', this.resetErrors);
            }
        });
        if (this.config.clearFieldsOnSuccess) {
            form.reset();
        }
        //noinspection JSUnresolvedVariable
        if (typeof grecaptcha !== 'undefined') {
            //noinspection JSUnresolvedVariable
            grecaptcha.reset();
        }
    }

    // handler server error response
    onError(response, status, xhr, form) {
        if (SweetAlert2.Message !== undefined) {
            SweetAlert2.Message.error(response.message);
        }

        if (response.data) {
            let key, value, focused;
            for (key in response.data) {
                let span = form.querySelector('.error_' + key);
                if (response.data.hasOwnProperty(key)) {
                    if (!focused) {
                        form.querySelector('[name="' + key + '"]').focus();
                        focused = true;
                    }
                    value = response.data[key];
                    if (span) {
                        span.innerHTML = value;
                        span.classList.add('error');
                    }

                    form.querySelector('[name="' + key + '"]').classList.add('error');
                }
            }

            form.querySelectorAll('.error').forEach(el => {
                if (el.name) {
                    el.addEventListener('keydown', this.resetErrors);
                }
            });
        }
    }

    // File upload processing methods
    onUploadProgress(e, form) {
        if (SweetAlert2.Message !== undefined) {
            SweetAlert2.Message.info(this.config.fileUplodedProgressMsg + Math.ceil(e.loaded / e.total * 100) + '%');
        }
    }

    onUploadFinished(e, form) {
        if (SweetAlert2.Message !== undefined) {
            SweetAlert2.Message.success(this.config.fileUplodedSuccessMsg);
        }
    }

    onUploadError(e, form) {
        if (SweetAlert2.Message !== undefined) {
            SweetAlert2.Message.error(this.config.fileUplodedErrorMsg);
        }
    }
}