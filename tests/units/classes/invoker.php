<?php

namespace mageekguy\atoum\visibility\tests\units;

use
	mageekguy\atoum,
	mageekguy\atoum\visibility\invoker as testedClass
;

class invoker extends atoum\test
{
	public function testSetObject()
	{
		$this
			->if($invoker = new testedClass())
			->and($object = new \stdClass())
			->then
				->object($invoker->setObject($object))->isIdenticalTo($invoker)
		;
	}

	public function testGetInvokable()
	{
		$this
			->if($invoker = new testedClass())
			->and($method = uniqid())
			->then
				->exception(function() use ($invoker, $method) {
						$invoker->getInvokable($method);
					}
				)
					->isInstanceOf('mageekguy\atoum\visibility\invoker\exception')
					->hasMessage('Object is not set')
			->if($invoker->setObject($object = new \stdClass()))
			->then
				->exception(function() use ($invoker, $method) {
						$invoker->getInvokable($method);
					}
				)
					->isInstanceOf('mageekguy\atoum\exceptions\logic\invalidArgument')
					->hasMessage(sprintf('Method %s::%s does not exist', get_class($object), $method))
			->given(
				$this->mockGenerator
					->orphanize('__construct')
					->generate('reflectionMethod'),
				$factory = function($object) use (& $reflectionClass, & $reflectionMethod, & $method, & $return) {
					$reflectionClass = new \mock\reflectionClass($object);
					$reflectionClass->getMockController()->hasMethod = true;

					$reflectionMethod = new \mock\relfectionMethod();
					$reflectionMethod->getMockController()->invoke = $return = uniqid();
					$reflectionClass->getMockController()->getMethod = $reflectionMethod;

					return $reflectionClass;
				}
			)
			->if($invoker = new testedClass($factory))
			->and($invoker->setObject($object = new \stdClass()))
			->then
				->object($invoker->getInvokable($method))->isIdenticalTo($reflectionMethod)
				->mock($reflectionMethod)
					->call('setAccessible')->withArguments(true)->once()
		;
	}

	public function testInvoke()
	{
		$this
			->given(
				$this->mockGenerator
					->orphanize('__construct')
					->generate('reflectionMethod'),
				$factory = function($object) use (& $reflectionClass, & $reflectionMethod, & $method, & $return) {
					$reflectionClass = new \mock\reflectionClass($object);
					$reflectionClass->getMockController()->hasMethod = true;

					$reflectionMethod = new \mock\relfectionMethod();
					$reflectionMethod->getMockController()->invoke = $return = uniqid();
					$reflectionMethod->getMockController()->invokeArgs = $return;
					$reflectionClass->getMockController()->getMethod = $reflectionMethod;

					return $reflectionClass;
				}
			)
			->if($invoker = new testedClass($factory))
			->then
				->exception(function() use ($invoker, $method) {
						$invoker->getInvokable($method);
					}
				)
					->isInstanceOf('mageekguy\atoum\visibility\invoker\exception')
					->hasMessage('Object is not set')
			->and($invoker->setObject($object = new \stdClass()))
			->and($method = uniqid())
			->then
				->variable($invoker->invoke($method))->isEqualTo($return)
				->mock($reflectionMethod)
					->call('invoke')->withArguments($object)->once()
				->variable($invoker->invoke($method, $argument = uniqid()))->isEqualTo($return)
				->mock($reflectionMethod)
					->call('invokeArgs')->withArguments($object, array($argument))->once()
				->variable($invoker->invoke($method, $argument, $otherArgument = uniqid()))->isEqualTo($return)
				->mock($reflectionMethod)
					->call('invokeArgs')->withArguments($object, array($argument, $otherArgument))->once()
		;
	}
}