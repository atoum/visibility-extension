<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'autoloader.php';

use mageekguy\atoum\visibility;

$runner->addExtension(new visibility\extension($script));
