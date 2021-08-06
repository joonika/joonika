### pagination component
````twig
{{ import pagination of form : }}
//inputs of pagination macro
{{ forms.pagination([
      "page": 1, // Ex: 12
      "lastPage": 1, // Ex: 12
      "dataLength": 1, // Ex: 12
      "appName": "", // Ex: test
      "requestLink": "", // Ex: test
      "successResponse": "", // Ex: #test
      "type": "", // Ex: test
      "formID":  false,
      "sendMethod": "", // Ex: get
      "prepend": false,
      "functionOnClick": false,
  ])  }}

 // how to use of pagination macro in twig page
{% import "@cp/layouts/components/pagination.twig" as pagination %}

````