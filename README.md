## Joonika Lite PHP Framework

### Install

Markdown is a lightweight and easy-to-use syntax for styling your writing. It includes conventions for

```markdown
composer required joonika/joonika
```

OR Using By json config

```json
{
  "name": "projectName",
  "description": "joonika",
  "type": "project",
  "license": "MIT",
  "minimum-stability": "dev",
  "config": {
    "platform-check": false
  },
  "repositories": [
    {
      "type": "vcs",
      "url": "https://gitfa.com/Joonika/joonika.git"
    }
  ],
  "require": {
    "joonika/joonika": "dev-Beta"
  }
}
```

## Controller Available Methods

### Permission Check

```` php
$this->hasPermission('users');
````

### Validate Check

```` php
$this->validate(['q|required,min:10,max:11']);
````
**Available Methods:**

required

max:(value) (Ex: max:10)

min:(value) (Ex: min:10)
<hr>

### Response
#### Success

```` php
this->setResponseSuccess($data,$success=true);
// you can response success as false in 200 (http status success)
````
````json
{
    "success": true,
    "data": [
        
    ]
}
````

#### Error

```` php
this->setResponseError($alert,$exit=false,$code=null);
// $alert text can be string or array
// if(array)
$alert = [
    "message"=>"sample message",
    "source"=>"sample message", // where happend -> file path or controller name or field name
    "data"=>"sample message", // string or array
];
// if you want to exit code and not continue after
// you can change http status code else 200
````
````json
{
    "success": false,
    "errors": [
        {"message":"sample message"}
    ]
}
````

###[TWIG](doc/tiwg.md)

