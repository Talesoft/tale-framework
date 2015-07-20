<?php

namespace Tale\Net;

use Tale\System\Enum;

abstract class AddressFamily extends Enum {

	const INET = \AF_INET;
	const INET6 = \AF_INET6;
	const UNIX = \AF_UNIX;
}