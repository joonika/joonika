###date macro
create dynamic date with this macro

````twig
{{ import date of form : }}
//inputs of date macro
{{ forms.date([
        "title": "", // Ex: test
        "name": "", // Ex: test
        "value": 0, // Ex: 25
        "formGroupClass": false,
        "formGroup": true,
        "attr": {{"data-msg": "this field is required"}}
        "help": "", // Ex: test
        "direction": app.JK_DIRECTION,
        "labelClass": "", // Ex: test
        "forceVal": false,
        "format": 3, // Ex: 8
        "inLine": false,
        "position": button, // Ex: absolote
        "lang": 0, // Ex: 12
        "disabled": false
        "colSize": false
        "required":false 
        "disableBeforeToday": false
        "disableAfterToday": false
        "groupId": "", // Ex: test
        "rangeSelector": false
        "fromDate": false
        "toDate": false
        "targetDateSelector": false
   // how to use of date macro in twig page
{% include '@cp/layouts/components/formCreator.twig' ignore missing with {'form':response.form} %}
{% for item in response.form.fields %}
    {% set fieldType=item.fieldType %}
    {% if fieldType=='date' %}
        {{ forms.date(item) }}
    {% endif %}
 {% endfor %}
   
````