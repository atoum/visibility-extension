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
        if (class_exists($target) === false)
        {
            throw new atoum\exceptions\logic\invalidArgument(sprintf('Class %s does not exist', $target));
        }

        return parent::setTarget($target);
    }

    public function getInvokable($method)
    {
        return new visibility\invoker\invokable($this->reflectedTarget->getMethod($method));
    }
} 
