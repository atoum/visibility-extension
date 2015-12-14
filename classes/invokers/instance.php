<?php

namespace mageekguy\atoum\visibility\invokers;

use
	mageekguy\atoum,
	mageekguy\atoum\visibility
;

class instance extends visibility\invoker
{
	public function setTarget($target)
	{
		if (is_object($target) === false)
		{
			throw new atoum\exceptions\logic\invalidArgument(sprintf('%s is not an object', $target));
		}

		return parent::setTarget($target);
	}

	public function getInvokable($method)
	{
		if ($this->targetIsSet()->reflectedTarget->hasMethod($method) === false)
		{
			throw new atoum\exceptions\logic\invalidArgument(sprintf('Method %s::%s() does not exist', get_class($this->target), $method));
		}

		$reflectedMethod = $this->reflectedTarget->getMethod($method);

		if ($reflectedMethod->isStatic() === true)
		{
			throw new atoum\exceptions\logic\invalidArgument(sprintf('Static methods are not supported by %s', get_class($this)));
		}

		return new visibility\invoker\invokable($this->reflectedTarget->getMethod($method), $this->target);
	}
} 
