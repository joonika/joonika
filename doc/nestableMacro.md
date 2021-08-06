###nestable macro

````twig
{{ import nestable of form : }}
//inputs of nestable macro
{{ forms.nestable([
    "rootClass": '' , // Ex: test,
    "itemId": '' , // Ex: test,
    "templateAddress" : '' , // Ex: 'templateAddress',
    "sortFunction":'' , // Ex: 'console.log',
    "depth" : '' , // Ex: 1,
    "type": '' , // Ex: test,
    "withId": false,
    ]) }}
     // how to use of nestable macro in twig page
     {% import "@cp/layouts/components/nestableMacro.twig" as list %}
````