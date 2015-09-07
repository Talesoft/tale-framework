
# Tale CRUD Concept

## Consistent PHP Crud Concept

What do we want to achieve?


### Unified Access

Route Example
`/:model?/:crudAction?/:id?.:resultFormat?`


#### List
```
/user
/user.json
/user/index
/user/index.html
/user?search=some+search+string&sort=id,name+asc&page=5&count=200
```

- Needs no input parameters
- The URL Query can and should be used for query manipulation (sorting, filtering)

#### Create
```
/user/create
/user/create.json
/user/create.html
```

- Needs no input parameters
- `:id` can optionally be used for e.g. default username

#### Update
/user/edit/user-name
/user/edit/user-name.json
/user/edit/user-name.html

- Requires an input parameter
- `:id` can be omitted sometimes, e.g. you're logged in and it defaults to your own `:id`


#### Read/Retrieve
/user/view/user-name
/user/view/user-name.json
/user/view/user-name.html

- Requires an input parameter
- `:id` can be omitted sometimes, e.g. you're logged in and it defaults to your own `:id`

#### Remove
/user/edit/user-name
/user/edit/user-name.json
/user/edit/user-name.html

- Requires an input parameter
- `:id` can be omitted sometimes, e.g. you're logged in and it defaults to your own `:id`


#### Read/Retrieve
/user/view/user-name
/user/view/user-name.json
/user/view/user-name.html

- Requires an input parameter
- `:id` can be omitted sometimes, e.g. you're logged in and it defaults to your own `:id`



### Central Model

There is one Model for one Entity of Data.
This Model acts as a central way to access data of that kind in any way

The Model is an Object with public properties
e.g.

```php

class User extends ModelBase
{
    
    public $id = 'id';
    public $name = 'string(32) required unique';
    public $displayName = 'string(128) optional';
    public $age = 'datetime optional';
    public $role = 'enum(admin,moderator,user,guest) default(guest)';
    
    
    /* The following functions are all OPTIONAL */
    protected function initEvents()
    {
        
        $this->bind('beforeValidate', function ($e) {
            
            if (!$this->name)
                $this->name = $this->generateNewName();
                
            //Cancel and fail validation with
            //$e->preventDefault();
        });
    }
    
    protected function getExposedFields($crudAction)
    {
        
        return [
            'name',
            'displayName',
            'age'
        ];
    }
    
    protected function getTableName()
    {
    
        return 'users';
    }
    
    protected function getPrimaryColumnName()
    {
        
        return 'id';
    }
}

```

It's important that it's a class, not just an object.
The class supports the reflection needed to work with the model

**The model is not bound to the database, any kind of composing/decomposing mechanism makes use of the model**

### Central Validation

The Central Model contains the Validation Logic of the object.
There can be different Validators for different type of Tasks
e.g.

```
User->validateLogin()
User->validateCreation()
User->validateUpdate()
```

### Central Types

In order to gain a consistent usage of database through HTTP Input Data (GET, POST),
PHP and the underlying PHP Types, SQL and it's types as well as HTML Form-Input Types,
Tale\Crud will include a centralized Type-System

The Types will allow easy conversion to all other specified systems

Notice that all Type-Classes get "Type" appended to the name, other than the Tale naming conventions suggests,
to avoid naming collisions with e.g String, Int, Bool, Short etc.

Possible Types are:

```
Tale\Crud\Type\BoolType				=> true/false, maps to Radio Input (Yes, No)
Tale\Crud\Type\ByteType				=> 1Byte, maps to Int Input (0-255)
Tale\Crud\Type\ShortType			=> 2Byte, maps to Int Input (0-SHORT_MAX)
Tale\Crud\Type\IntType 				=> 4Byte, maps to Int Input (0-INT_MAX)
Tale\Crud\Type\LongType				=> 8Byte, maps to Int Input (0-LONG_MAX)
Tale\Crud\Type\FloatType			=> 4Byte, maps to Text-Input
Tale\Crud\Type\DoubleType			=> 8Byte, maps to Text-Input
Tale\Crud\Type\CharType				=> 1Byte, maps to Text-Input
Tale\Crud\Type\StringType			=> Maps to Text-Input
Tale\Crud\Type\DateTimeType			=> Maps to DateTime-Input
Tale\Crud\Type\TimeStampType		=> Maps to Date-Time Input
Tale\Crud\Type\EnumType				=> Maps to Select-Input
Tale\Crud\Type\BinaryType			=> Maps to File-Upload Input
Tale\Crud\Type\ArrayType			=> Array of Types
Tale\Crud\Type\ObjectType			=> Object with Properties of Type
```

Some Types can be grouped.
e.g. Int-Types can be Unsigned, so they extend `UnsignedTypeBase` and introduce some kind of `$_signed` property

```php
$int = new IntType('-1234', true)
$int->validates() //Should return false
```

These Types already contain a basic validation (e.g. IntType will check for is_numeric)


The types introduce a `->getValue()` method which converts it to the value
that's the most useful for PHP and also applies sanitizing etc.

They also introduce a `->setValue($value)` method which has to set the value **raw**

The only point of type-conversion should be the `->getValue()` function
There's also a `->getRawValue()` in case you want to work with that

The `null` value **has** to be preserved in `->getValue()`, there's also no NullType, it has no value,
every language possible knows it and it's easy to cross-use

Null indicates that the value **has not been set**!
This will be important in `->required` validation

If something is read from anywhere, the values get converted to instances of this type.
Every library that uses this has to decide on it's own, what's the most fitting type.
If you create own types, make sure you extend existing ones before recreating the wheel.

If something is written, the Types will be checked via instanceof and converted
into the appropiate type for the database/file or whatever to write into

Some Examples:

#### Read from a database

```php
$row = [
    'name' => new StringType($row['name']),
    'id' => new IntType($row['id']),
    'creationTime' => new TimeStampType($row['creationTime'])
];
```


At this point, we have a few possibilities to act on this:

#### Validate

We can validate the values we retrieved easily

```
$row['id']->validates(); //Should return FALSE, if ID is not an INT or NULL
```

#### Save data

```php

foreach ($row as $key => $type ) {
	
	//Convert Type
	$value = null;
	if ($type instanceof DateTimeType)
		$value = $type->getValue()->format(\DateTime::RFC3339);
	else if ($type instanceof IntType)
		$value = intval($value->getValue())
	else if($type instanceof BinaryType)
		throw new Exception("This adapter doesn't support the Binary-type");
	else
	    throw new Exception("Failed to convert types: $key contains no valid type");
}

$this->db->update($row);

```


#### Serialize data/API Outputs

This will also support the `Serializable` and `JsonSerializable` Interface of PHP
This is primarily useful for API output
```
$serialized = serialize($row);
$json = json_encode($row);
$xml = XmlElement::fromArray($row);
```

**This also means, you can serialize models**
```
$users = $this->db->selectArray(); //The result will be en EntityCollection, which is also a Crud Entity
$json = json_encode($users);
//Cache $json, print it as an API output or put it into your JavaScript?
```


#### Transfer data

Imagine you retrieve a row from a CSV file
`$row = CsvReader->readLine();`
and you can directly transmit it to another data adapter
`$this->db->insert($row)`
and maybe to another
`$xml->appendChild(XmlElement::fromArray($row))`
maybe you want to cache the data cleanly
`$this->fetchCached('csvLines', function () { return CsvReader->readLine(); })`


#### Input generation

You can generate Inputs based on the types given
`$htmlElement = HtmlManipulator->form($row)`

It automatically generates form elements out of form data available

You can extend the form by usual methods with HtmlManipulator
```
$htmlElement->appendClass('form form-horizontal')
            ->find('> div')
                ->appendClass('form-group')
                ->parent()
            ->find('label')
                ->appendClass('control-label col-md-4')
                ->parent()
            ->find('input')
                ->appendClass('form-control');
```


and then easily get the HTML out of it
`$html = (string)$htmlElement`
  

Interactive CLI Input is also planned (Microsoft PowerShell-Style)



## Automatic Form Recognition

To explain this, just an example

```php

$form = $formProvider->getModelForm('My\\Models\\User');

if ($form->hasInput(Crud::CREATE) {
    
    if (!$form->validates(Crud::CREATE) {
        
        return ['success' => false, 'errors' => $form->getErrors()];
    }
    
    $createdModel = $form->pass(Crud::CREATE);
    
    return ['success' => true, 'name' => $createdModel->name];
}



if ($form->hasInput(Crud::REMOVE) {
    
    if (!$form->validates(Crud::REMOVE) {
        
        return ['success' => false, 'errors' => $form->getErrors()];
    }
    
    $form->pass(Crud::REMOVE);
    
    return ['success' => true];
}
```



## App Targets:
- Web Apps
- Console Apps
- [Generic Apps]



## Basis

The Tale CRUD scheme is based on the following ENUM
The ENUM CRUD operations can map to HTTP Method Types as well as SQL methods

```php

class Tale\Crud extends Enum
{
    //CRUD: CREATE
    //SQL:  INSERT
    //REST: POST
    //CODE: ADD
    const CREATE = 'create';

    //CRUD: READ
    //SQL:  SELECT
    //REST: GET
    //CODE: LOAD
    const READ = 'read';

    //CRUD: UPDATE
    //SQL:  UPDATE
    //REST: POST, PUT
    //CODE: SAVE
    const UPDATE = 'update';

    //CRUD: DELETE
    //SQL:  DELETE
    //REST: GET, DELETE
    //CODE: REMOVE
    const DELETE ='delete';
}
```


One further operation that will mostly be available (but is not needed on all CRUD-elements)
is LIST

CRUD: LIST
SQL:  SELECT
REST: GET
CODE: INDEX



## Data Hierarchy

Many things can be seen as Data hierarchies that are split into Databases, Tables, Columns and Rows

Imagine a folder containing files

The `Source` is a Root-Folder
`/data/xml`

The `Database` is a Sub-Folder
`/data/xml/blog`

The `Table` is a XML File
`/data/xml/blog/posts.xml`

The XML File contains `Columns` and `Rows` in the following, automatically generated way

```xml
<?xml version="1.0" encoding="utf-8"?>
<table>
    <columns>
        <!-- 
            Notice that some (inbuilt) types are aliased via a TypeFactory 
            Custom aliases can be created (As usual with the Tale\Factory)
        -->
        <column name="id" type="int" max="11" auto-increased primary />
        <column name="title" type="string" max="128" />
        <column name="slug" type="My\Blog\Crud\Type\SlugType" />
        <column name="content" type="string" />
        
    </columns>
    <rows>
        <row id="1" slug="my-awesome-blog-post"><!-- The Indexes are stored as attributes for easy access (maybe ints too?) -->
            <title><![CDATA[My Awesome Blog Post]]></title>
            <content><![CDATA[
                Just take a fucking look at my
                fucking awesome blog post
                stored in fucking XML
            ]]></content>
        </row>
    </rows>
</table>
```


You can replicate this on a series of formats

CSV:

```csv
id:int(11) autoIncrease primary;title:string(128);slug:My\Blog\Crud\Type\SlugType;content:string
1;"My Awesome Blog Post";"my-awesome-blog-post";"Just take a fucking look at my\nfucking awesome blog post\nstored in fucking CSV"
```

JSON:

```json
{
    "columns": {
        "id": "int(11) autoIncrease primary",
        "title": "string(128",
        "slug": "My\Blog\Crud\Type\SlugType",
        "content": "string"
    },
    "rows": {
        "1": {
            "title": "My Awesome Blog Post",
            "canonicalName": "my-awesome-blog-post",
            "content": "Just take a fucking look at my\nfucking awesome blog post\nstored in fucking JSON"
        }
    }
```

