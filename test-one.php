<?php

function test_one()
{
	function doTest($test)
	{
		echo "<h3>Test: $test</h3>";
		$mStart = $m1 = $m10 = $m1k = memory_get_usage();
		for ($i = 1; $i <= 1000; $i++)
		{
			call_user_func($test);
			if ($i == 1)
				$m1 = memory_get_usage();
			elseif ($i == 10)
				$m10 = memory_get_usage();
		}
		$m1k = memory_get_usage();

		echo "Memory used: after 1 time = " . ($m1 - $mStart)
			. ' after 10 times = ' . ($m10 - $mStart)
			. ' after 1k times = ' . ($m1k - $mStart);
	}

	function value()
	{
		one('myKey', 50);
		assert(50 == one('myKey'));
	}

	function instanceGivenNoParams()
	{
		one('myKey2', function() { return new ArrayObject(); });
		assert(one('myKey2') instanceof ArrayObject) or die;
	}

	function instantiateWithParams()
	{
		# Here we have a collection
		for ($i = 0; $i < 3; $i++)
		{
			static $f;
			if (empty($f))
				$f = function($arr) { return new ArrayObject($arr); };
			$x = one('myKey3', $f, array($i));
			assert($x instanceof ArrayObject && isset($x[0]) && $x[0] == $i && !isset($x[1])) or die;
		}

		# Single value with key myKey3 must be separately initialized to null
		assert(one('myKey3') === null) or die;
	}

	function single_ParamsFromCaller($a, $b, $c){ return one('myKey4',
		function($a, $b, $c) { return new ArrayObject(array('a' => $a, 'b' => $b, 'c' => $c)); }
	); }

	function arr() { return one('ArrayObject'); }

	function singletonNoParams()
	{
		$x = arr();
		assert($x instanceof ArrayObject) or die;
	}

	function arr2($x) { return one('ArrayObject', $x); }

	function singletonWithOneParam()
	{
		for($i = 0; $i < 20; $i++)
		{
			$x = arr2(array($i, $i+1));
			assert($x instanceof ArrayObject && isset($x[0]) && $x[0] == $i && isset($x[1]) && $x[1] == $i+1) or die;
		}
	}

	doTest('value');
	doTest('instanceGivenNoParams');
	doTest('instantiateWithParams');
	doTest('singletonNoParams');
	doTest('singletonWithOneParam');
}

test_one();