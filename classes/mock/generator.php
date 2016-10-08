<?php

namespace mageekguy\atoum\visibility\mock;

use mageekguy\atoum\test;

class generator extends test\mock\generator
{
	protected $loosenedMethods = array();

	public function makeVisible($method)
	{
		if ($this->isLoosened($method) === false)
		{
			$this->loosenedMethods[] = strtolower($method);
		}

		return $this;
	}

	public function methodIsMockable(\reflectionMethod $method)
	{
		if ($method->isFinal() || $method->isStatic() || $method->isPrivate() || static::methodNameIsReservedWord($method))
		{
			return false;
		}

		return $this->isLoosened($method->getName()) || parent::methodIsMockable($method);
	}

	public function isLoosened($method)
	{
		return in_array(strtolower($method), $this->loosenedMethods);
	}

	public function generate($class, $mockNamespace = null, $mockClass = null)
	{
		parent::generate($class, $mockNamespace, $mockClass);

		$this->loosenedMethods = array();

		return $this;
	}

	protected function generateMethodSignature(\reflectionMethod $method)
	{
		return ($method->isPublic() === true || $this->isLoosened($method->getName()) ? 'public' : 'protected') . ' function' . ($method->returnsReference() === false ? '' : ' &') . ' ' . $method->getName() . '(' . $this->getParametersSignature($method) . ')' . $this->getReturnType($method);
	}
}
