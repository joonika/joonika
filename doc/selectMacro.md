###select macro
create dynamic select with this macro

````twig
 {{ import select of form : }}
//inputs of select macro
{{ forms.select([
        "title": : '' , // Ex: test,
        "function": : '' , // Ex: test,
        "select2Attr": : '' , // Ex: 
        "array": : '' , // Ex: [id,title,name],
        "w100": : true , // Ex: true,
        "arrayAttr": : '' , // Ex: test,
        "select2": : '' , // Ex: 
        "templateResult": : '' , // Ex: ,
        "select2Parent":: '' , // Ex: ,
        "formGroup": : true,
        "formControl": true,
        "formGroupClass": : '' , // Ex: test,
        "selectClass": : '' , // Ex: ,
        "placeholder": : '' , // Ex: please choose,
        "name": '' , // Ex: test,
        "attr": '' , // Ex: test,
        "colSize": false,
        "help": '' , // Ex: test,
        "value": value , // Ex: 0,
        "required": false,
        "multiple": false,
        "first": false,
        "firstTitle": 'please select',
        "firstValue": '' , // Ex: test,
        "direction": app.JK_DIRECTION,
        "onChange": false,
        "disabled": false,
        "dataComponent":false,
        "dataComponentVars": {}
        "allowClear": false,
        "style": '' , // Ex: test,
    ]) }}
// how to use of select macro in twig page
{% include '@cp/layouts/components/formCreator.twig' ignore missing with {'form':response.form} %}
{% for item in response.form.fields %}
    {% set fieldType=item.fieldType %}
    {% if fieldType=='select' %}
        {{ forms.select(item) }}
    {% endif %}
 {% endfor %}
````