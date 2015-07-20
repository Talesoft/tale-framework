<?php

namespace Tale\Io;

use Tale\System\Enum;

class SeekOrigin extends Enum {
	
	const CURRENT = \SEEK_CUR;
	const START = \SEEK_SET;
	const END = \SEEK_END;
}