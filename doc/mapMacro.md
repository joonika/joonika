###map macro
create dynamic map with this macro


````twig
{{ import map of form : }}
//inputs of map macro
{{ forms.map([
        "title": "", // Ex: test
        "fixMap":false,
        "search":false,
        "direction":'rtl',
        "value": value,
        "latLang":"", // Ex: test
        "mapName": "", // Ex: test
        "mapID": "", // Ex: test
        "points":"", // Ex: test
        "style":"", // Ex: 'height: 320px;width: 100%;position: relative;',
        "error":false,
        "afterReverseGeocodingSuccess":"", 
        "afterGeocodingSuccess":'',
        "whenReadyCallBack":true,
 ])  }}
 // how to use of map macro in twig page
{% include '@cp/layouts/components/formCreator.twig' ignore missing with {'form':response.form} %}
{% for item in response.form.fields %}
    {% set fieldType=item.fieldType %}
    {% if fieldType=='map' %}
        {{ forms.map(item) }}
    {% endif %}
 {% endfor %}

  
````