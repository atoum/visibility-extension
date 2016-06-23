<?php

namespace mageekguy\atoum\visibility;

use mageekguy\atoum;

atoum\autoloader::get()
	->addNamespaceAlias('atoum\visibility', __NAMESPACE__)
	->addDirectory(__NAMESPACE__, __DIR__ . DIRECTORY_SEPARATOR . 'classes');
;

if (defined('mageekguy\atoum\scripts\runner') === true) {
	\mageekguy\atoum\scripts\runner::addConfigurationCallable(function($script, $runner) {
		$runner->addExtension(new \mageekguy\atoum\visibility\extension($script));
	});
}
