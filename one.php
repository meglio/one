<?php

/**
 * Represents mutable registry of immutable values/objects.
 * Each object or value will be lazily initialized upon first request and placed in registry only once,
 * and from that moment the value or the link to an object instance will remain immutable.
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
	// Storage of all immutable single objects and values
	static $x = array();

	// If index already exists in registry, just return it
	if (is_string($key) && isset($x[$key]))
		return $x[$key];

	// If index still not defined, intelligently set value for it.
	if (!is_string($key))
		throw new Exception('Key must be string');

	// If $instance is given, use it
	if (!is_null($instance))
		return $x[$key] = is_callable($instance)? $instance() : $instance;

	// If $instance is not given, then $key must be class name
	if (!class_exists($key))
		throw new Exception('$key must be valid class name when $instance not given');

	return $x[$key] = new $key;
}