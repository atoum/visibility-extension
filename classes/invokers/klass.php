<?php

namespace mageekguy\atoum\visibility\invokers;

use
	mageekguy\atoum,
	mageekguy\atoum\visibility
;

class klass extends visibility\invoker
{
	public function setTarget($target)
	{
		if (is_object($target) === false && class_exists($target) === false)
		{
			throw new atoum\exceptions\logic\invalidArgument(sprintf('Class %s does not exist', $target));
		}

		return parent::setTarget($target);
	}

	public function getInvokable($method)
	{
		try
		{
			$method = $this->targetIsSet()->reflectedTarget->getMethod($method);
		}
		catch (\reflectionException $exception)
		{
			throw new atoum\exceptions\logic\invalidArgument(sprintf('Method %s::%s() does not exist', get_class($this->target), $method), $exception->getCode(), $exception);
		}

		if ($method->isStatic() === true)
		{
			throw new atoum\exceptions\logic\invalidArgument(sprintf('Static methods are not supported by %s', get_class($this)));
		}

		return new visibility\invoker\invokable($method, $this->target);
	}
} 
