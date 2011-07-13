### Hello World

	// Create singleton
	one('Application');

	// ... or create function for application singleton
	function app() { return one('Application'); }

	// Define read-only mysql config parameters in config.php
	one('mysqlParams', array('host' => 'localhost', 'user' => 'me', 'password' => '123'));

	// Initialize database lazily and mantain singleton of class DB
	// 2nd parameter for one() is anonymous function that will be used only once when db() first accessed
	function db() { return one('DB', function() { return new DB(one('mysqlParams')); }) }

	// Given Route class with constructor which takes $url parameter,
	// we can mantain one Route instance for each unique $url.
	// 2nd parameter is not callable, and so will be interpreted by one() as argument to Route's constructor
	function route($sql) { return one('Route', $sql) }

### What one() does?

It implements a registry of immutable values / object references.

Registry is n-dimensional where n is a number of arguments of instantiation behavior.

For example, class with no constructor has dimension = 0 (0 parameters/arguments needed to instantiate),
so only one instance of such class can be stored by one() - this makes singletoning easy as easy as

	one('ClassName');

But power of one() does not end with singletons. Because one() takes into account how much arguments
required to retrieve/instantiate a value when first accessed, it can retrieve one value for each unique
set of arguments. As an example consider you want to keep PDOStatement for the whole php application -
one instance for each query string:

	function pdo($sql) { return one('preparedPdo', function($s) { return db()->prepare($s); }, $sql); }

Here one() sees that instantiation (anonymous function passed in 2nd argument to one)
takes one argument, and so instantiaion will be processed and its result stored separately
each time for different value of $sql. Then one() uses its 3rd parameter (and next ones if required)
to pass into instantiation function.

#### Inferring class constructor

One more useful thing is that one() can infer instantiation for classes:

	function myRegistry(){ return one('ArrayObject'); }

Taking into account that 'ArrayObject' is a name of class and no callable argument provided for one(),
it will infer instantiation to be constructor of class ArrayObject.

The same works well with constructors which expects parameters:

	class User() { function __construct($id) { /*...*/ } }
	function user($id){ return one('User', $id) }


### Performance - no issues

Passing closure when calling one() will not consume memory because of the way how PHP improves usage
of function parameters. Actually, closure object will be instantiated only when called, this means
it will be done only once. Further more, it will be done lazily - at first request with one().

Profiling on win7, php5.3.5 reports average 50ms / 1000 calls.


### Summary

* lazy initialization of stored values;
* easy singletoning without adding factory methods to classes;
* n-dimensional caching;
* no performance issues.


### Some more examples

	// Create 2 global fake singleton objects of the same class with parametrized constructor calls
	// (I say fake because real singleton is only one possible instance created for one class):
	function some() { return one( 'MyKeyForSome', function(){return new AClass();} ); }
	function bla() { return one( 'MyKeyForBla', function(){return new AClass(false, 'abc');} ); }
