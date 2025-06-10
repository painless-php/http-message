<?php

namespace PainlessPHP\Http\Message\Internal;

class Stream
{
    public CONST array READ_MODES = [
        'r',
        'r+',
        'w+',
        'a+',
        'x+',
        'c+'
    ];

    public CONST array WRITE_MODES = [
        'r+',
        'w',
        'w+',
        'a',
        'a+',
        'x',
        'x+',
        'c',
        'c+'
    ];
}
