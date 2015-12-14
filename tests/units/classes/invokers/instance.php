<?php

namespace mageekguy\atoum\visibility\tests\units\invokers;

use
	mageekguy\atoum,
	mageekguy\atoum\visibility\invokers\instance as testedClass
;

class instance extends atoum\test
{
	public function testClass()
	{
		$this->testedClass->isSubClassOf('mageekguy\atoum\visibility\invoker');
	}

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
					->hasMessage('Invoker\'s target is not set')
			->if($invoker->setTarget($object = new \stdClass()))
			->then
				->exception(function() use ($invoker, $method) {
						$invoker->getInvokable($method);
					}
				)
					->isInstanceOf('mageekguy\atoum\exceptions\logic\invalidArgument')
					->hasMessage(sprintf('Method %s::%s() does not exist', get_class($object), $method))
			->given(
				$this->mockGenerator->orphanize('__construct')->shuntParentClassCalls()->generate('reflectionMethod'),
				$factory = function($object) use (& $reflectionClass, & $reflectionMethod) {
					$reflectionClass = new \mock\reflectionClass($object);
					$reflectionMethod = new \mock\reflectionMethod();

					$reflectionClass->getMockController()->hasMethod = true;
					$reflectionClass->getMockController()->getMethod = $reflectionMethod;

					$reflectionMethod->getMockController()->isStatic = true;

					return $reflectionClass;
				}
			)
			->if($invoker->setReflectionClassFactory($factory))
			->and($invoker->setTarget($object = new \stdClass()))
			->then
				->exception(function() use ($invoker, $method) {
					   $invoker->getInvokable($method);
					}
				)
					->isInstanceOf('mageekguy\atoum\exceptions\logic\invalidArgument')
					->hasMessage(sprintf('Static methods are not supported by %s', get_class($invoker)))
			->given(
				$factory = function($object) use (& $reflectionClass, & $reflectionMethod, & $method) {
					$reflectionClass = new \mock\reflectionClass($object);
					$reflectionMethod = new \mock\reflectionMethod();

					$reflectionClass->getMockController()->hasMethod = true;
					$reflectionClass->getMockController()->getMethod = $reflectionMethod;

					$reflectionMethod->getMockController()->isStatic = false;

					return $reflectionClass;
				}
			)
			->if($invoker->setReflectionClassFactory($factory))
			->and($invoker->setTarget($object = new \stdClass()))
			->then
				->object($invoker->getInvokable($method))->isInstanceOf('mageekguy\atoum\visibility\invoker\invokable')
		;
	}

	public function testInvoke()
	{
		$this
			->given(
				$this->mockGenerator
					->orphanize('__construct')
					->orphanize('invoke')
					->generate('reflectionMethod'),
				$factory = function($object) use (& $reflectionClass, & $reflectionMethod, & $method, & $return) {
					$reflectionClass = new \mock\reflectionClass($object);
					$reflectionMethod = new \mock\reflectionMethod();

					$reflectionClass->getMockController()->hasMethod = true;
					$reflectionClass->getMockController()->getMethod = $reflectionMethod;

					$reflectionMethod->getMockController()->isStatic = false;
					$reflectionMethod->getMockController()->isPublic = false;
					$reflectionMethod->getMockController()->invoke = $return = uniqid();
					$reflectionMethod->getMockController()->invokeArgs = $return;

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
					->hasMessage('Invoker\'s target is not set')
			->and($invoker->setTarget($object = new \stdClass()))
			->and($method = uniqid())
			->then
				->variable($invoker->invoke($method))->isEqualTo($return)
				->mock($reflectionMethod)
					->call('invoke')->withArguments($object, array())->once()
				->variable($invoker->invoke($method, $arguments = array(uniqid())))->isEqualTo($return)
				->mock($reflectionMethod)
					->call('invokeArgs')->withArguments($object, $arguments)->once()
				->variable($invoker->invoke($method, $arguments = array(uniqid(), $otherArgument = uniqid())))->isEqualTo($return)
				->mock($reflectionMethod)
					->call('invokeArgs')->withArguments($object, $arguments)->once()
		;
	}
}
