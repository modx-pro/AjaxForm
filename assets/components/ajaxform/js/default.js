import AjaxForm from "./ajaxform.class.js";
const AjaxFormConfigs = document.querySelectorAll('input[name="af_config"]');
if(AjaxFormConfigs.length){
    AjaxFormConfigs.forEach(el => {
        let config =  JSON.parse(el.value);
        new AjaxForm('.'+config.formSelector, config);
    });
}