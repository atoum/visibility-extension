<?php

namespace atoum\atoum\visibility\tests\units;

use
	atoum\atoum,
	atoum\atoum\visibility\extension as testedClass
;

class extension extends atoum\test
{
	public function testClass()
	{
		$this
			->testedClass
				->hasInterface('atoum\atoum\extension')
		;
	}

	public function test__construct()
	{
		$this
			->if($script = new atoum\scripts\runner(uniqid()))
			->and($script->setArgumentsParser($parser = new \mock\atoum\atoum\script\arguments\parser()))
			->and($configurator = new \mock\atoum\atoum\configurator($script))
			->then
				->object($extension = new testedClass())
			->if($this->resetMock($parser))
			->if($extension = new testedClass($configurator))
			->then
				->mock($parser)
					->call('addHandler')->twice()
		;
	}

	public function testSetRunner()
	{
		$this
			->if($extension = new testedClass())
			->and($runner = new atoum\runner())
			->then
				->object($extension->setRunner($runner))->isIdenticalTo($extension)
		;
	}

	public function testSetTest()
	{
		$this
			->if($extension = new testedClass())
			->and($test = new \mock\atoum\atoum\test())
			->and($manager = new \mock\atoum\atoum\test\assertion\manager())
			->and($test->setAssertionManager($manager))
			->then
				->object($extension->setTest($test))->isIdenticalTo($extension)
				->mock($manager)
					->call('setHandler')->withArguments('invoke')->once()
		;
	}
}
