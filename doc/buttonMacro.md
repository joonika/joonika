###button macro
create dynamic button with this macro

````twig
{{ import button of form : }}
//inputs of button macro
{{ forms.button([
        "text" : '' , // Ex: test,
        "name" : '' , // Ex: test,
        "id": 0 , // Ex: 12,
        "value" : submit,
        "btnClass" : '' , // Ex: test,
        "colSize": 0 , // 0 to 12 Ex: 12,
        "title": '' , // Ex: test,
        "disabled": false,
        "cancelText" : '' , // Ex: cancel,
        "cancelClass" : '' , // Ex: test,
        "cancelUrl" : '' , 
        "cancelFunction" : '' , 
        "icon": '' , // Ex: <i class: "fal fa_edit"/>,
     ]) }}
    // how to use of button macro in twig page
    {% include '@cp/layouts/components/formCreator.twig' ignore missing with {'form':response.form} %}
    {% for item in response.form.fields %}
        {% set fieldType=item.fieldType %}
        {% if fieldType=='button' %}
            {{ forms.button(item) }}
        {% endif %}
     {% endfor %}
 ````

