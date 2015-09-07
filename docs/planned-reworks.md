
## Tale\Crud
See `crud.md`

## Tale\Crud\Request

```
class Request
    $_method
    ->getArg($name, $default = null)   //The below using $_method as the method
    ->getArgs($namesAndDefaultValues=null)
    ->getCreateArg(...)
    ->getCreateArgs(...)
    ->getReadArg(...)
    ->getReadArgs(...)
    ->getUpdateArg(...)
    ->getUpdateArgs(...)
    ->getRemoveArg(...)
    ->getRemoveArgs(...)
```


## Tale\Environment

```
static class Environment
    ::$_args
    ::$_request 
    ::getOption($name, $default = null) => $_ENV[$name]
    ::getOptions() => $_ENV
    ::getClientOption($name, $default = null) => $_SERVER[$name]
    ::getClientOptions()
    ::getArg($name, $shortHand, $optional = null) => getopt()
    ::getArgs() => $_SERVER['argv']
    ::isWeb() => \PHP_SAPI != 'cli'
    ::isCli() => \PHP_SAPI = 'cli'
    ::isServer() => \PHP_SAPI = 'cli-server'
```



# ::fromEnvironment Rework

Some classes need a more convient way to retrieve environment variables

## Tale\Net\Url::fromEnvironment
Reads the URL from the current Environment

## Tale\Net\Http\Request\Server
Removed

## Tale\Net\Http\Response\Server
Removed

## Tale\Net\Http\Request::fromEnvironment
Reads the current HTTP Request from the environment (Uses `Environment::isWeb()` to check wether we're in web mode)

## Tale\Net\Http\Response::fromEnvironment
Creates a fitting response for the `Request::fromEnvironment()`

## Tale\Crud\Request::fromEnvironment
Creates the CRUD-Request from the environment
Don't read from $_POST/$_GET but rather from Url::fromEnvironment()->getQuery and Request::fromEnvironment()->getBodyArgs()


# Tale\App Rework
->isWeb, ->isCli and ->isServer removed

# Tale\App\Feature\Router
Router now doesnt create any input data.


# Tale\Form

Form now uses CRUD-Fields and types
Form is created as the following

`$form = new Form($crudAction, $fields)`

e.g.
`$form = new Form(Crud::CREATE, $fields)`

Validate complete forms

`$form->validates() //true/false`
`$form->getErrors() //[]`