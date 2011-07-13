<?php

/**
 * Represents mutable registry of immutable values/objects.
 * Each object or value will be lazily initialized upon first request and placed in registry only once,
 * and from that moment the value or the link to an object instance will remain immutable.
 *
 * Registry is 
 *
 * @param string $key If $instance given, $key can be any valid array key string.
 * If $instance is not given, $key must be valid class name in order to instantiate class.
 *
 * @param mixed $instance Either initial value provided directly or closure
 * to be called in order to retrieve value at first request.
 *
 * @return mixed Always the same value or object.
 *
 * @throws Exception when key is not a string, or when $instance is not given and $key is not a valid class name.
 *
 * @author Anton Andriyevskyy (Meglio), Denis Morozov (iX-Cray)
 */
function one($key, $instance = null)
{
	if (!is_string($key) || empty($key))
		throw new Exception('Key must be non-empty string');

	# Storage of all immutable single objects and values.
	# Each $key represents one ArrayObject where the key is a unique hash of instantiation parameters
	# and the value is what instantiation returned when called with these parameters.
	static $x = array();
	# ArrayObject that corresponds to the $key
	$a = isset($x[$key])? $x[$key] : $x[$key] = new ArrayObject();

	# Profiling shows that class_exists is slow, so we have to cache it
	static $ce = array();
	$classExists = isset($ce[$key]) ? $ce[$key] : $ce[$key] = class_exists($key, true);

	# is_callable is also slow, so better call it once and store in variable
	$instanceCallable = is_callable($instance);

	# If there is no instantiation behavior, use $instance as value
	if (!$instanceCallable && !$classExists)
	{
		$argsHash = 'single';
		return isset($a[$argsHash])? $a[$argsHash] : $a[$argsHash] = $instance;
	}

	# Below is the code WITH instantiation behavior

	# If instance is callable, it will be treated as instantiation behavior, otherwise as argument to instantiation
	$ignoreArgs = ($instanceCallable)? 2 : 1;

	if (func_num_args() > $ignoreArgs)
	{
		$args = array_slice(func_get_args(), $ignoreArgs);
		$argsHash = json_encode($args);
	}
	else
	{
		$args = array();
		$argsHash = 'single';
	}

	# If instance or value exists already, return it
	if (isset($a[$argsHash]))
		return $a[$argsHash];

	# ... otherwise instantiate with the code below and then return

	# If callback provided, just call it
	if ($instanceCallable)
		return $a[$argsHash] = call_user_func_array($instance, $args);

	# Last case if when class exists - then just use its constructor.
	# This case coming taking into account a check above when there is no instantiation behavior at all.

	static $rfl = array();
	/** @var ReflectionClass $classReflection */
	$classReflection = isset($rfl[$key])? $rfl[$key] : $rfl[$key] = new ReflectionClass($key);
	return $a[$argsHash] = $classReflection->newInstanceArgs($args);
}
