# atoum/visibility-extension [![Build Status](https://travis-ci.org/atoum/visibility-extension.svg?branch=master)](https://travis-ci.org/atoum/visibility-extension)

The atoum visibility-extension allows you to override method visibility in your unit tests. For example, you will be able
to test protected method with it.

## Example

In the example, we test the protected method `bar` :

```php
<?php

namespace
{
  class foo
  {
    protected function bar()
    {
      return 'foo';
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
          ->string($this->invoke($sut)->bar())->isEqualTo('foo')
      ;
    }
  }
}
```

## Install it

Install extension using [composer](https://getcomposer.org):

```
composer require --dev atoum/visibility-extension
```

The extension will be automatically loaded. If you ever want to unload it, you can add this to your configuration file:

```php
<?php

// .atoum.php

use mageekguy\atoum\visibility;

$runner->removeExtension(visibility\extension::class);
```

## Use it

You can achieve visibility override using two methods:

* Override concrete classes' methods visibility: this will allow you to assert on protected methods returned values,
* Override mocked classes' methods visibility: this will allow you to override protected methods code and assert on their calls.

### Override concrete classes' methods visibility

Overriding methods visibility is done on-the-fly in unit tests body using the `invoke` method:

```php
<?php

namespace
{
	class foo
	{
		protected function bar()
		{
			return $this;
		}
		
		protected function baz($arg)
		{
			return $arg;
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
					->object($this->invoke($sut)->bar())->isIdenticalTo($sut)
			;
		}
		
		public function testBaz()
		{
			$this
				->if($sut = new \foo())
				->and($arg = uniqid())
				->then
					->variable($this->invoke($sut)->baz($arg))->isEqualTo($arg)
			;
		}
	}
}
```

As you can see, we only used the `invoke` method. It has a special syntax that we are going to detail: `$this->invoke(<object instance>)-><method name>(<arguments>)`

* `<object instance>` is a reference to an object instance. In the previous example it was `$foo`, a reference to a `\foo` instance;
* `<method name>` is the name of the method we want to make visible and call. In the previous example it was `bar`or `baz`.;
* `<arguments>` is the arguments list you want to pass to the method. In the previous example it was `$arg`, a string generated with `uniqid()`.

### Override mocked classes' methods visibility

Overriding mocked classes' methods requires a bit more work, involving the mock generator. Before detailing how to achieve that, 
**keep in mind that there are some limitations you have to be aware of**. We'll detail the just after a short example:

```php
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
				->given(
					$this->mockGenerator
						->makeVisible('bar')
						->generate('foo')
				)
				->if($mockedSut = new \mock\foo)
				->and($this->calling($mockedSut)->bar = 'foo')
				->then
					->string($mockedSut->baz())->isEqualTo('foo')
					->string($mockedSut->baz())->isEqualTo('foo')
					->mock($mockedSut)
						->call('bar')->twice()
			;
		}
	}
}
```

The mock generator now provides a `makeVisible` method which you can call to override a method visibility. You have to call
this method **before** the generation of the mocked class which happens the first time a mock is instanciated or when you call
the `generate` method of the mock controller.

Doing this will create a child class (the mock) and define the protected methods as public. You will then be able to call
them directly, without even using the `invoke` method we saw in the previous section.

You will also be able to assert on those methods' calls using standard atoum assertions.

Now let's talk about the limitations:

* The first one is that the visibility override has to be declared **before the first generation of the mocked class**,
* Once the visibility has been overridden, **it can't be reverted**,
* Overriding the visibility of method in mocks has to be done carefully: **it is a permanent operation involving reflection**.

When you want to temporarily override the visibility of a mock class method, you can change the name of the mocked class using the `generate` method's
arguments. Using the previous example, it would look like:

```php
<?php

namespace tests\units
{
	use mageekguy\atoum;

	class foo extends atoum\test
	{
		public function testBar()
		{
			$this
				->given(
					$this->mockGenerator
						->makeVisible('bar')
						->generate('foo', 'mock', 'mockedFoo')
				)
				->if($mockedSut = new \mock\mockedFoo)
				->and($this->calling($mockedSut)->bar = 'foo')
				->then
					->string($mockedSut->baz())->isEqualTo('foo')
					->string($mockedSut->baz())->isEqualTo('foo')
					->mock($mockedSut)
						->call('bar')->twice()
			;
		}
	}
}
```

Doing this, we would generate a `\mock\mockedFoo` class from the `\foo` class with a looser visiblity on the `bar` method.
This will allow us to bypass some limitation:

* This will generate a new mock class so the visibility override will always apply, even if the `\foo` class was already mocked,
* We can "revert" this operation by treating this mock class as a one-shot mock and forget it right after the test. This will still
require that we don't reuse the same name for future mocks.

## Links

* [atoum](http://atoum.org)
* [atoum's documentation](http://docs.atoum.org)


## License

visibility-extension is released under the BSD-3 Clause License. See the bundled LICENSE file for details.


![atoum](http://atoum.org/images/logo/atoum.png)
