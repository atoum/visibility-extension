<?php

namespace mageekguy\atoum\visibility;

use 
	mageekguy\atoum,
	mageekguy\atoum\visibility\invoker
;

abstract class invoker
{
	protected $target;
	protected $reflectedTarget;
	protected $reflectionClassFactory;

	public function __construct($reflectionClassFactory = null)
	{
		$this->setReflectionClassFactory($reflectionClassFactory);
	}

	public function __call($method, $arguments)
	{
		return $this->invoke($method, $arguments);
	}

	public function setReflectionClassFactory($reflectionClassFactory = null)
	{
		if ($reflectionClassFactory !== null && is_callable($reflectionClassFactory) === false)
		{
			throw new atoum\exceptions\logic\invalidArgument(sprintf('Argument of %s::%s() must be callable', get_class($this), __FUNCTION__));
		}

		$this->reflectionClassFactory = $reflectionClassFactory ?: function($classname) {
			return new \reflectionClass($classname);
		};;

		return $this;
	}

	public function getReflectionClassFactory()
	{
		return $this->reflectionClassFactory;
	}

	public function setTarget($target)
	{
		try
		{
			$this->target = $target;
			$this->reflectedTarget = call_user_func($this->reflectionClassFactory, $this->target);
		}
		catch (\reflectionException $exception)
		{
			throw new invoker\exception(
				sprintf('%s is not a valid invoker target', $target),
				$exception->getCode(),
				$exception
			);
		}

		return $this;
	}

	abstract public function getInvokable($method);

	public function invoke($method, array $arguments = array())
	{
		array_unshift($arguments, $this->targetIsSet()->target);

		return call_user_func_array(array($this->getInvokable($method), 'invoke'), $arguments);
	}

	protected function targetIsSet()
	{
		if ($this->target === null)
		{
			throw new invoker\exception('Invoker\'s target is not set');
		}

		return $this;
	}
} 
