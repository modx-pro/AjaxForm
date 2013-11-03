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