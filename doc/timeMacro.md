###time macro
create dynamic time with this macro

````twig
{{ import time of form : }}
//inputs of time macro
{{ forms.time([
        "title": '' , // Ex: test,
        "name": '' , // Ex: test,
        "value": 00:00 , // Ex: 12:00,
        "attr": {{"data-msg": "this field is required"}} ,
        "help": '' , // Ex: {{ var|e }}{# shortcut to escape a variable #},
        "direction": app.JK_DIRECTION , 
        "labelClass": '' , // Ex: test,
        "disabled": false,
        "colSize": false,
        "required": false,
])  }}
// how to use of time macro in twig page
{% include '@cp/layouts/components/formCreator.twig' ignore missing with {'form':response.form} %}
{% for item in response.form.fields %}
    {% set fieldType=item.fieldType %}
    {% if fieldType=='time' %}
        {{ forms.time(item) }}
    {% endif %}
 {% endfor %}
   
````