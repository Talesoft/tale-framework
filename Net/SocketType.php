<?php

namespace Tale\Net;

use Tale\Enum;

class SocketType extends Enum {

	const STREAM = \SOCK_STREAM;
	const DGRAM = \SOCK_DGRAM;
	const SEQPACKET = \SOCK_SEQPACKET;
	const RAW = \SOCK_RAW;
	const RDM = \SOCK_RDM;
}