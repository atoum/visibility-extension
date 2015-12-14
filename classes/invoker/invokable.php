<?php

namespace mageekguy\atoum\visibility\invoker;

class invokable
{
	protected $method;
	protected $target;
	protected $wasLoosened;

	public function __construct(\reflectionMethod $method, $target = null)
	{
		$this->method = $method;
		$this->target = $target;
	}

	public function __invoke($arguments)
	{
		return call_user_func_array(array($this, 'invoke'), $arguments);
	}

	public function invoke()
	{
		$arguments = array_slice(func_get_args(), 1);

		$this->loosenVisibility();

		if (sizeof($arguments) > 0)
		{
			$return = $this->method->invokeArgs($this->target, $arguments);
		}
		else
		{
			$return = $this->method->invoke($this->target);
		}

		$this->strenghtenVisibility();

		return $return;
	}

	protected function loosenVisibility()
	{
		if ($this->method->isPublic() === false)
		{
			$this->method->setAccessible(true);
			$this->wasLoosened = true;
		}

		return $this;
	}

	protected function strenghtenVisibility()
	{
		if ($this->wasLoosened === true)
		{
			$this->method->setAccessible(false);
			$this->wasLoosened = false;
		}

		return $this;
	}
} 
