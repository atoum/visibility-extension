<?php

namespace mageekguy\atoum\visibility;

use mageekguy\atoum;
use mageekguy\atoum\observable;
use mageekguy\atoum\runner;
use mageekguy\atoum\test;

class extension implements atoum\extension
{
	public function __construct(atoum\configurator $configurator = null)
	{
		if ($configurator)
		{
			$parser = $configurator->getScript()->getArgumentsParser();

			$handler = function($script, $argument, $values) {
				$script->getRunner()->addTestsFromDirectory(dirname(__DIR__) . '/tests/units/classes');
			};

			$parser
				->addHandler($handler, array('--test-ext'))
				->addHandler($handler, array('--test-it'))
			;
		}
	}

	public function setRunner(runner $runner)
	{
		return $this;
	}

	public function setTest(test $test)
	{
		$invoker = null;
		$invokerFactory = function() use (& $invoker) {
			if ($invoker === null)
			{
				$invoker = new atoum\visibility\invoker();
			}

			return $invoker;
		};

		$test->getAssertionManager()
			->setHandler(
				'invoke',
				function($object, $method) use ($test, $invokerFactory) {
					return call_user_func_array(
						array($invokerFactory()->setObject($object), 'invoke'),
						array($method) + array_slice(func_get_args(), 1)
					);
				}
			)
		;

		$test->setMockGenerator(new atoum\visibility\mock\generator($test));

		return $this;
	}

	public function handleEvent($event, observable $observable) {}
} 