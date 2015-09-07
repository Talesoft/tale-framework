
# Tale CRUD Concept

## Consistent PHP Crud Concept

What do we want to achieve?

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
}

```

It's important that it's a class, not just an object.
The class supports the reflection needed to work with the model

**The model not bound to the database**

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

Notice that all Type-Class get "Type" appended, other than the Tale naming conventions suggests,
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
e.g. Int-Types can be Unsigned

```php
$int = new IntType('-1234', true)
$int->validates() //Should return false
```

These Types already contain a basic validation (e.g. IntType will check for is_numeric)

If something is read, the values get converted to instances of this type.
If something is written, the Types will be checked via instanceof


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

foreach ($row as $key => $value ) {
	
	//Convert Type
	if ($value instanceof DateTimeType)
		$value = $value->getValue()->format(\DateTime::RFC3339);
	else if ($value instanceof IntType)
		$value = intval($value)
	else if($value instanceof BinaryType)
		throw new Exception("This adapter doesn't support the Binary-type");
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


#### Transfer data

Imagine you retrieve a row from a CSV file
`$row = CsvReader->readLine();`
and you can directly transmit it to another data adapter
`$this->db->insert($row)`


#### Input generation

You can generate Inputs based on the types given
`$htmlElement = HtmlManipulator->form($row)`

Interactive CLI Input is also planned (Microsoft PowerShell-Style)



## Automatic Form Recognition

To explain this, just an example

```php

$form = new ModelForm('My\\Models\\User');

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


## Data Hierarchy