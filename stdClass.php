<?php

namespace
{
	class foo
	{
		public function baz()
		{
			return $this->bar();
		}

		protected function bar()
		{
			$args = func_get_args();

			return sizeof($args) ? $args : $this;
		}
	}
}

namespace tests\units
{
	use mageekguy\atoum;

	class foo extends atoum\test
	{
		public function testBar()
		{
			$this
				->if($sut = new \foo())
				->then
					->object($this->invoke($sut, 'bar'))->isIdenticalTo($sut)
					->array($this->invoke($sut, 'bar', $a = uniqid(), $b = uniqid()))->isIdenticalTo(array($a, $b))

				->given(
					$this->mockGenerator
						->makeVisible('bar')
						->generate('foo')
				)
				->if($mockedSut = new \mock\foo)
				->and($this->calling($mockedSut)->bar = 'foo')
				->then
					->string($mockedSut->baz())->isEqualTo('foo')
			;
		}
	}
}
