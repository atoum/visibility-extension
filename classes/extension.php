<?php

namespace mageekguy\atoum\visibility;

use
	mageekguy\atoum,
	mageekguy\atoum\observable,
	mageekguy\atoum\runner,
	mageekguy\atoum\test
;

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

	public function addToRunner(runner $runner)
	{
		$runner->addExtension($this);

		return $this;
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
						$invoker = new invokers\klass();
					}
					else
					{
						$invoker = new invokers\instance();
					}

					return $invoker->setTarget($target);
				}
			)
		;

		$test->setMockGenerator(new mock\generator($test));

		return $this;
	}

	public function handleEvent($event, observable $observable) {}
} 
