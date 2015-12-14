<?php

namespace mageekguy\atoum\visibility\tests\units;

use
	mageekguy\atoum,
	\mock\mageekguy\atoum\visibility\invoker as testedClass
;

class invoker extends atoum\test
{
	public function test__construct()
	{
		$this
			->if($invoker = new testedClass())
			->then
				->object($invoker->getReflectionClassFactory())->isInstanceOf('closure')
			->if($invoker = new testedClass($factory = function() {}))
			->then
				->object($invoker->getReflectionClassFactory())->isIdenticalTo($factory)
		;
	}

	public function testGetSetReflectionClassFactory()
	{
		$this
			->if($invoker = new testedClass())
			->then
				->object($invoker->getReflectionClassFactory())->isInstanceOf('closure')
				->object($invoker->setReflectionClassFactory())->isIdenticalTo($invoker)
				->object($invoker->getReflectionClassFactory())->isInstanceOf('closure')
			->if($factory = function() {})
			->then
				->object($invoker->setReflectionClassFactory($factory))->isIdenticalTo($invoker)
				->object($invoker->getReflectionClassFactory())->isIdenticalTo($factory)
			->if($factory = uniqid())
			->then
				->exception(function() use ($invoker, $factory) {
						$invoker->setReflectionClassFactory($factory);
					}
				)
					->isInstanceOf('invalidArgumentException')
					->hasMessage(sprintf('Argument of %s::setReflectionClassFactory() must be callable', get_class($invoker)))
		;
	}
}
