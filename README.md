| :warning: On June 13, 2023, [MODX RSC](https://github.com/modx-pro/) will end support for AjaxForm. It will still be available on [modstore.pro](https://modstore.pro/packages/utilities/ajaxform) and [extras.modx.com](https://extras.modx.com/package/ajaxform) marketplaces, but we recommend using [FetchIt](https://github.com/GulomovCreative/FetchIt). |
| ---

| :warning: 13 июня 2023 года команда [MODX RSC](https://github.com/modx-pro/) прекратила поддержку AjaxForm. Он будет продолжать быть доступным на маркетплейсах [modstore.pro](https://modstore.pro/packages/utilities/ajaxform) и [extras.modx.com](https://extras.modx.com/package/ajaxform), но мы рекомендуем использовать вместо него компонент [FetchIt](https://github.com/GulomovCreative/FetchIt). |
| ---

## AjaxForm

Simple component for MODX Revolution, that allows you to send any form through ajax.

## Quick start
1. Create new chunk with name "myForm".
2. Add form with class="ajax_form" into that chunk.
3. Call AjaxForm at any page 
```
[[!AjaxForm?form=`myForm`&snippet=`FormIt`]]
```

You can specify any parameters for end snippet:
```
[[!AjaxForm?
	&form=`myForm`
	&snippet=`FormIt`
	&hooks=`email`
	&emailTo=`info@mysite.com`
	&etc=`...`
]]
```
