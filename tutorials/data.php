<?php


class User extends ModelBase
{
	
	public $id = 'id';
	public $name = 'string(32) required unique';
	public $displayName = 'string(128) optional';
	public $age = 'datetime optional';
	public $role = 'enum(admin,moderator,user,guest) default(guest)';
}


class Post extends ModelBase
{
	
	public $id = 'id';
	public $title = 'string(128) required';
	public $slug = 'string(32) required unique';
	public $content = 'string optional';

	public $userId = 'fk';	//Automagically maps to User model
}




//Setting up the source
$src = new Source([
	'adapter' => 'mysql', //Currently mysql and sqlite supported
	'options' => [ //Driver Options
		'data' => [ //this is needed for mysql/similar PDO instances
			'host' => 'some-host.com',
			//or
			'unix_socket' => '/some/unix/socket.sock'
		],
		'path' => ':memory:' //This is needed for SQLite
	]
]);






//These are valid entities:
//Database
//Table (Database has many Tables)
//Column (Table has many Columns)
//Row (Table has many Rows)

//All of these entities support the following CRUD methods
->load() //Loads all data for this entity on the remote storage
->save() //Saves all data for this entity on the remote storage
->create() //Creates this entity. Tabel needs columns before it's created
->remove() //Remove this entity from the remote storage












//Selecting a Database
// Deep way
$db = $src->someDatabaseName; //Will map to some_database_name (Depending on the driver)
// Non-inflected way
$db = $src->getDatabase('some_database_name'); //Will map to some_database_name (No inflection)
// Instance-way
$db = new Database($src, 'some_database_name');





//Selecting a Table
// Deep way
$tbl = $db->someTableName; //Will map to some_table_name (Depending on the driver)
// Non-inflected way
$tbl = $db->getTable('some_table_name');
// Instance-way
$tbl = new Table($db, 'some_table_name');




//Selecting a Column
// Deep way
$col = $tbl->someColumnName; //Will map to some_column_name (Depending on the driver)
// Non-inflected way
$col = $tbl->getColumn('some_column_name');
// Instance-way
$col = new Column($tbl, 'some_column_name');


//Changing a column
//Column has the following properties:

//The type of the column. For a List of all types var_dump(Column::getTypes())
//REQUIRED!!
$col->getType()
$col->setType($type)    

//The maximum length of the column value (optional)
$col->getMaxLength()
$col->setMaxLength($length)

//The allowed values for e.g. an enum-type
$col->getAllowedValues()
$col->setAllowedValues(array $values)

//Is the column automatically increased on passing NULL
$col->isAutoIncreased()
$col->autoIncrease()

//Is the value optional/Can the column be set to NULL
$col->isOptional()
$col->makeOptional()

$col->isRequired()
$col->makeRequired()

//The index type for this column
$col->getKeyType()
$col->setKeyType($keyType)

$col->isPrimary()
$col->makePrimary()

$col->isUnique()
$col->makeUnique()

$col->isIndex()
$col->makeIndex()

//Possible index types:
Column::KEY_PRIMARY //Primary keys, once per table
Column::KEY_UNIQUE //Unique keys, values can exist only once per table
Column::KEY_INDEX //General indexed field or foreign key


//The default value of the column (if NULL is passed)
$col->getDefaultValue()
$col->setDefaultValue($defaultValue)

//The column that this column references (foreign key)
$col->getReference() //returns a Column object
$col->setReference(Column $col) //sets a new referencing column



//Column Type Strings
//Types can also be assigned via type string
//Syntax for each token is ([] === optional)
// commandName[(argument)]

//Deep-way
$col->parse('int(11) required autoIncrease')

//Instance-way
$col = new Column($tbl, 'column_name', 'int(11) required autoIncrease')

//Supported commands:
// autoIncrement|autoIncrease 				=> $col->autoIncrease()
// allowNull|null|optional 					=> $col->makeOptional()
// disallowNull|notNull|required 			=> $col->makeRequired()
// unique 									=> $col->makeUnique()
// primary 									=> $col->makePrimary()
// index 									=> $col->makeIndex()
// id 										=> $col->parse('int(11) required primary autoIncrease')
// fk 										=> $col->parse('int(11) required index')
// default({defaultValue})					=> $col->setDefaultValue($defaultValue)
// {typeName} 								=> $col->setType($typeName)
// {typeName}({{numericValue}})				=> $col->setType($typeName)->setMaxLength($numericValue)
// {typeName}({{stringValue}})				=> $col->setType($typeName)->setAllowedValues(explode(',', $stringValue))
// reference|references({tableName})        => $col->reference($table->primaryColumn)
// "({{tableAndColumnName}}) 				=> $col->reference($table->$column)

//the quick types can be stacked (e.g. "fk optional")





//Model Support
//To allow Models, just add Model-Namespaces to the Database
$this->db->addModelNameSpace('My\\Models');


//Once you access a table in any way, it will work with the namespaces registered on the Database
//Names will automatically be inflected to a valid class name based on common conventions (camel-case, singular)
//e.g.
//$this->db->users				=> My\Models\User 					=> Table: users
//$this->db->storageProducts	=> My\Models\StorageProduct 		=> Table: storage_products


//If a model class is found, all results from that table will be of the model class type.
//If you want to override that, use the second parameter of select/selectArray/selectOne




//Basic crud operations

/*
 * Add new User (CREATE)
 */
// Deep way
$user = $this->db->users->insertRow(['name' => 'admin', 'displayName' => 'Administrator']);
// Instance-way
$user = new User($this->db->users, ['name' => 'admin', 'displayName' => 'Administrator']);
$user->create();



/*
 * Select User (READ)
 */
// deep way
$users = $this->db->users->select(['role!' => 'admin']); //Returns Generator or User Instances
$users = $this->db->users->selectArray(['role!' => 'admin']); //Returns Array of User Instances
$user = $this->db->users->selectOne(['role!' => 'admin']); //Returns User Instance or null of not found
$userCount = $this->db->users->count(['role!' => 'admin']); //Returns int (result amount)

// instance way
$user = new User($this->db->users, ['id' => 5]);
$user->load();


/*
 * Save User (UPDATE)
 */
// deep way
$user = $this->db->users->select(['name' => 'admin']);
$user->name = 'Administrator';
$user->save();

//or
$this->db->users->where(['name' => 'admin'])->update(['name' => 'Administrator']);
//or (on all entries)
$this->db->users->update(['name' => 'Administrator']);



/*
 * Remove User (DELETE)
 */
// deep way
$user = $this->db->users->select(['name' => 'admin']);
$user->remove();

//or
$this->db->users->where(['name' => 'admin'])->remove();
//or (on all entries)
$this->db->users->remove();



//Automatic lookup
$user->getPosts() //Does a one-to-many lookup on posts.user_id
$post->getUser() //Does a one-to-one lookup on users.id
//EXPERIMENTAL!!!!






//Building and re-using queries

//Building a query always requires a table to work on

$qry = new Query($tbl);

//Query supports a few query-methods
$qry->where($clauses)
$qry->sortBy($sortings)
$qry->limit($range, [$start])



//Clauses work as follows
['id' => 5]								// WHERE `id`=5
['id!' => 5] 							// WHERE `id`!=5
['id>' => 5] 							// WHERE `id`>5
['id>' => 5, 'id<' => 7]				// WHERE `id`>5 AND `id`<7
['or' => ['id' => 5, 'id.' => 6]]		// WHERE `id`=5 OR `id`=6
['id' => [1, 2, 3]] 					// WHERE `id` IN (1,2,3)
['id!' => [1, 2, 3]] 					// WHERE `id` NOT IN (1,2,3)
['id!' => null] 						// WHERE `id` IS NOT NULL
['id' => null] 							// WHERE `id` IS NULL
['name~' => 'george'] 					// WHERE `name` LIKE "george"
['name^' => 'george'] 					// WHERE `name` LIKE "%george"
['name$' => 'george'] 					// WHERE `name` LIKE "george%"
['name*' => 'george'] 					// WHERE `name` LIKE "%george%"


//Column names are interpolated
'someColumnName'   //maps to some_column_name (Depends on driver)

//Deep queries with or and and
[
	'and' => [
		'content*' => 'a',
		'content*.' => 'b',
		'content*..' => 'c',
		'or' => [
			'content*' => 'd',
			'content*.' => 'e'
		]
	],
	'and.' => ['id' => null],
	'and..' => ['userId' => null]
]

//Resulting query
/*
WHERE (
	`content` LIKE "%a%" 
	AND `content` LIKE "%b%" 
	AND `content` LIKE "%c%" 
	AND (`content LIKE "%d%" OR `content` LIKE "%e%")
) 
AND `id` IS NULL
AND `user_id` IS NULL
*/


//Sortings work as follows
['id' => 'asc'] 				//ORDER BY `id` ASC
['id' => '-'] 					//ORDER BY `id` DESC
['id' => '+', 'name' => '-'] 	//ORDER BY `id` ASC, `name` DESC

//Possible values for ASC: asc, ascending, +, <, ^
//Possible values for DESC: desc, descending, -, >, v


//Limit works as follows
$this->limit(5) 		//LIMIT 5
$this->limit(5, 10)		//LIMIT 10,5



//You can use most crud operations on the query 
//(Except for CREATE, since it doesn't make sense)

$qry->selectArray();
$qry->count();
$qry->update($data)
$qry->remove()

//You can also use these methods directly on the table (including all Query-methods)
//You might also chain them


//Example for a query


$tbl->where([
	'role' => 'admin', 
	'visible' => 1
])->sortBy(['id', 'name' => 'desc'])->limit(10, 5)->remove();

//Result Query:
//DELETE FROM `table_name` WHERE `role`='admin' AND `visible`=1 ORDER BY `id` ASC, `name` DESC LIMIT 5, 10