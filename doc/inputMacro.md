### macroInput
create dynamic input with this macro


````twig
{{ import input of form : }}
//inputs of input macro
{{ forms.input([
        "title": '' , // Ex: test,
        "value": value,
        "type": test , // Ex: test,
        "labelClass": '' , // Ex: test,
        "inputClass": '' , // Ex: test,
        "onclick": '' , // Ex: test,
        "onkeyup": false,
        "class": '' , // Ex: test,
        "disabled": false,
        "formGroupClass": false,
        "formGroup": true,
        "label": true,
        "name": '' , // Ex: test,
        "attr": {{"data-msg": "this field is required"}}
        "addon": '' , // Ex: test,
        "addon-dir": 'right' , // Ex: 'left',
        "colSize": false , // 0 to 12 ,Ex: 6
        "help": '' , // Ex: test,
        "placeholder": '' , // Ex: Fill in this field,
        "required": false,
        "direction":  app.JK_DIRECTION,
        "history": '' , // Ex: test,
        "historyClick": '' , // Ex: test,
        "removeText": '' , // Ex: test,
        "maxLength":'' , // Ex: 12,
    ]) }}
// how to use of input macro in twig page
{% include '@cp/layouts/components/formCreator.twig' ignore missing with {'form':response.form} %}
{% for item in response.form.fields %}
    {% set fieldType=item.fieldType %}
    {% if fieldType=='input' %}
        {{ forms.input(item) }}
    {% endif %}
 {% endfor %}

    
 ````