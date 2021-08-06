###switch macro
create dynamic switch with this macro


````twig
{{ import switch of form : }}
//inputs of switch macro
{{ forms.switch([
        "title": '' , // Ex: test,
        "value": 0 , // Ex: 1,
        "defValue": 0 , // Ex: 0,
        "type": text , // Ex: company,
        "class": '' , // Ex: test,
        "onChange": '' , // Ex: test,
        "disabled": false
        "formGroupClass": '' , // Ex: test,
        "formGroup": true
        "name": '' , // Ex: test,
        "attr":{{"data-msg": "this field is required"}},
        "colSize": false,
        "help": '' , // Ex: Twig is smart enough to not escape an already escaped value by the escape filter.,
        "placeholder": '' , // Ex: Fill in this field,
        "required": false,
        "direction": app.JK_DIRECTION,
        "new": false,
]) }}
// how to use of switch macro in twig page
{% include '@cp/layouts/components/formCreator.twig' ignore missing with {'form':response.form} %}
{% for item in response.form.fields %}
    {% set fieldType=item.fieldType %}
    {% if fieldType=='switch' %}
        {{ forms.switch(item) }}
    {% endif %}
 {% endfor %}
 ````