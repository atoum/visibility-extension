<?php

namespace mageekguy\atoum\visibility;

use mageekguy\atoum;

atoum\autoloader::get()
	->addNamespaceAlias('atoum\visibility', __NAMESPACE__)
	->addDirectory(__NAMESPACE__, __DIR__ . DIRECTORY_SEPARATOR . 'classes');
;
