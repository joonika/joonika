###radio macro
create dynamic radio with this macro


````twig
{{ import radio of form : }}
//inputs of radio macro
{{ forms.radio([
        "id":  0 , // Ex: 12,
        "name":  '' , // Ex: test,
        "title":  '' , // Ex: test,
        "value": value,
        "type": radio,
        "class":  '' , // Ex: test,
        "onChange":  '' , // Ex: test,
        "disabled": false,
        "formGroupClass": false,
        "formGroup": true,
        "attr": {{"data-msg": "this field is required"}},
        "colSize": false,
        "help": '' , // Ex: test,
        "direction": app.JK_DIRECTION,
        "checkType": filled-in,
        "classBody": '' , // Ex: test,
        "cardStyle": '' , // Ex: bg: primary,
        "labelStyle": '' , // Ex: bg: primary,
        "labelClass": '' , // Ex: test,
        "inputClass": '' , // Ex: test,
        "flexRow": '' , // Ex: flex-direction: row,
        "extraVal": '' , // Ex: ,
        "icon": '' , // Ex: <i class:'fal fa chack' />,
        "iconSm": true,
        "labelOnclick": false,
        "checkedDefault":false,
        "onclick": '' , // Ex: test,
       ]) }}
// how to use of radio macro in twig page
{% include '@cp/layouts/components/formCreator.twig' ignore missing with {'form':response.form} %}
{% for item in response.form.fields %}
    {% set fieldType=item.fieldType %}
    {% if fieldType=='radio' %}
        {{ forms.radio(item) }}
    {% endif %}
 {% endfor %}
    
````