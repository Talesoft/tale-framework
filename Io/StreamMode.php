<?php

namespace Tale\Io;

use Tale\System\Enum;

class StreamMode extends Enum {

    const READ = 'r';
    const READ_WRITE = 'r+';
    const WRITE = 'w';
    const WRITE_READ = 'w+';
    const APPEND = 'a';
    const APPEND_READ = 'a+';
    const WRITE_NEW = 'x';
    const WRITE_READ_NEW = 'x+';
    const WRITE_CREATE = 'c';
    const WRITE_READ_CREATE = 'c+';
}