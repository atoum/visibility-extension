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
		$test->getAssertionManager()
			->setHandler(
				'invoke',
				function($target) use ($test) {
					if (is_string($target))
                    {
                        $invoker = new atoum\visibility\invokers\klass();
                    }
                    else
                    {
                        $invoker = new atoum\visibility\invokers\instance();
                    }

                    return $invoker->setTarget($target);
				}
			)
		;

		$test->setMockGenerator(new atoum\visibility\mock\generator($test));

		return $this;
	}

	public function handleEvent($event, observable $observable) {}
} 
