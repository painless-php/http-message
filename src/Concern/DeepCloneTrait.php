<?php

namespace PainlessPHP\Http\Message\Concern;

use DeepCopy\DeepCopy;

trait DeepCloneTrait
{
    protected function clone()
    {
        $copier = new DeepCopy();
        $copier->skipUncloneable();
        return $copier->copy($this);
    }
}
