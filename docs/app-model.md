
# Tale App Model


## App Structure

The App basically is a directory structure
It has a recursive module-system similar to composer's and is also composer-compatible

The smallest possible structure is the following


```
/my-app
    /app.json
    /composer.json
    /vendor
        /talesoft
            /tale-framework
```

The `app.json` is the main entry point to the app.
It can also be a `app.xml`, `app.ini` or `app.php`

Now an example of a larger app structure for a medium-sized app

```
/my-app
    /app.json
    /composer.json
    /controllers
        /IndexController.php
        /ErrorController.php
    /models
        /User.php
    /library
        /Model
            /CanonicalNameTrait.php
        /ControllerBase.php
        /ModelBase.php
    /themes
        /default
            /styles
                /common.less
            /scripts
                /common.ts
            /views
                /index
                    /index.jade
                /error
                    /index.jade
    /vendor
        /talesoft
            /tale-framework
```


## app.json

An example of the minimum that an `app.json` should contain (no really, it has to, TF validates it)

```json
{
    "name": "my-app",
    "displayName": "My App",
    "version": "1.0",
    "description": "My own Tale Framework app!",
    "license": "MIT",
    "authors": [ "Torben Köhn <tk@talesoft.io>" ]
}
```

There's also a virtual option called `path` defined that specifies the current app path


### Custom Values

You can use the `app.json` to add own configuration options as needed
```json
{
    "someCustomKey": "Some custom value"
}
```


### Interpolation

You can interpolate values recursively inside the `app.json` in the following way:

```json
{
    "someKey": "World",
    
    "strings": {
        "helloWorld": "Hello {{someKey}}!"
    },
    
    "hello{{someKey}}": "{{strings.helloWorld}}"
}
```

To access sub-keys (always from the root of the config), use the `.`-character

You can interpolate keys and values

### App Containment/Dependencies

Apps can run and depend on other apps

```
/my-app
    /app.json
    /apps
        /tale-blog
            /app.json
        /tale-dbms
            /app.json
        /tale-cms
            /app.json
```


You can define this like the following in the `app.json`

```json
{
    "require": {
        "tale-blog": "latest",
        "tale-dbms": "latest",
        "tale-cms": "latest"
    }
}
```


### Features

Tale Apps use Features for their internal functionalities.
There are many existing Tale Framework features and its pretty easy to create own ones.
To add own features, visit the `Tale\App\FeatureBase` documentation.

Some features have aliases with which you can reach them easily.
You can also specify own aliases

```json
{
    "featureAliases": {
        "auth": "My\\Own\\Auth\\Feature"
    },
    "features": {
        "data": {
            "adapter": "mysql"
        },
        "auth": {
            "some": "Custom Options"
        },
        "My\\Own\\Other\\Feature": null
    }
}
```


### PHP Options

You can set PHP Options automatically with the `phpOptions` option.
The option names get inflected (e.g. "xdebug.maxNestingLevel" will become "xdebug.max_nesting_level")

```json
{
    "phpOptions": {
        "xdebug.maxNestingLevel": 100000
    }
}
```



### Additional Config Files

If your `app.json` grows too large, you can load additional config files
that get merged with your `app.json` to create a final app config.

To add additional config patterns to load, use the `configure` option

The files are loaded in order as defined in the `include` sub-option.
Files inside directories are ordered by name.
If you want to have them ordered inside directories, give them number prefixes (00-*, 10-*, 20-* etc.)

**Notice that if a config file doesn't exist, it is just ignored. This is wanted to give the possibility to add
optional development and staging config files that are registered in a `.gitignore` file**

```json
{
    "configure": {
        "path": "{{path}}/config",
        "include": ["*.app.json", "features/*.json", "*.features/*.json"]
    }
}
```

This will first try to load any kind of `*.app.json` (e.g. `dev.app.json`, `staging.app.json`).
Then it tries to load all files inside the config/features directory of the app.
After that it tries to load additional feature config inside `*.features` (e.g. `dev.features/data.json`)



## Initialization

Example index.php for simply any kind of app:
```php

include 'vendor/autoload.php';

$app = new Tale\App(__DIR__);
$app->run();
```
