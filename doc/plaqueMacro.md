###plaqueInput macro
create dynamic plaqueInput with this macro

````twig
{{ import plaqueInput of form : }}
//inputs of plaqueInput macro
{{ forms.plaqueInput([
        "onChange" : '' ,// Ex: test,
        "colSize" : 0 ,// 0 to 12 Ex: 6, 
 )] }}
 // how to use of plaque macro in twig page
{% import "@cp/layouts/components/plaqueMacro.twig" as plaqueMacro %}
   
````