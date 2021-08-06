###checkbox macro
create dynamic checkbox with this macro

````twig
{{ import checkbox of form : }}
//inputs of checkbox macro
{{ forms.checkbox([
        "title": '' , // Ex: test,
        "id": 0 , // Ex: 12,
        "name": '' , // Ex: test,
        "value": 0 , // 0 or 1 Ex: 1,
        "formGroupClass": false,
        "formGroup": true,
        "attr": {{"data-msg": "this field is required"}},
        "help": '' , // Ex: test,
        "direction": app.JK_DIRECTION,
        "colSize": 0 , // 0 to 12 Ex: 6 ,
        "divId": '' , // Ex: test,
        "divExtraClass": '' , // Ex: test,
        "required":'' , // Ex: test,
        "checkType": filled-in,
       ]) }}
   // how to use of checkbox macro in twig page
   {% include '@cp/layouts/components/formCreator.twig' ignore missing with {'form':response.form} %}
   {% for item in response.form.fields %}
       {% set fieldType=item.fieldType %}
       {% if fieldType=='checkbox' %}
           {{ forms.checkbox(item) }}
       {% endif %}
    {% endfor %}
````