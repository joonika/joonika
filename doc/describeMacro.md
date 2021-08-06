###describe macro
create dynamic describe with this macro

````twig
{{ import description of form : }}
//inputs of description macro
{{ forms.description([
        "id": '' , // Ex: test,
        "class": '' , // Ex: test,
        "colSize": false , // 0 to 12 Ex: 4,
        "title": '' , // Ex: test,
        "icon": '' , // Ex: <i class:"fal fa edit"/>,
        ]) }}
// how to use of description macro in twig page
{% include '@cp/layouts/components/formCreator.twig' ignore missing with {'form':response.form} %}
{% for item in response.form.fields %}
    {% set fieldType=item.fieldType %}
    {% if fieldType=='description' %}
        {{ forms.description(item) }}
    {% endif %}
 {% endfor %}
````