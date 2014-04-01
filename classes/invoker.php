<?php

namespace mageekguy\atoum\visibility;

use mageekguy\atoum;
use mageekguy\atoum\visibility\invoker;

class invoker
{
	protected $object;
	protected $reflectionFactory;

	public function __construct($reflectionFactory = null)
	{
		$this->reflectionFactory = $reflectionFactory ?: function($classname) {
			return new \reflectionClass($classname);
		};
	}

	public function setObject($object)
	{
		if (is_object($object) === false)
		{
			throw new atoum\exceptions\logic\invalidArgument(sprintf('%s is not an object', $object));
		}

		$this->object = $object;

		return $this;
	}

	public function getInvokable($method)
	{
		$reflection = call_user_func($this->reflectionFactory, $this->objectIsSet()->object);

		if ($reflection->hasMethod($method) === false)
		{
			throw new atoum\exceptions\logic\invalidArgument(sprintf('Method %s::%s does not exist', $reflection->getName(), $method));
		}

		$method = $reflection->getMethod($method);
		$method->setAccessible(true);

		return $method;
	}

	public function invoke($method)
	{
		$arguments = array_slice(func_get_args(), 1);

		if (sizeof($arguments) === 0)
		{
			return $this->getInvokable($method)->invoke($this->object);
		}

		return $this->getInvokable($method)->invokeArgs($this->object, $arguments);
	}

	protected function objectIsSet()
	{
		if ($this->object === null)
		{
			throw new invoker\exception('Object is not set');
		}

		return $this;
	}
} 