<?php

namespace Tale\Net;

use Tale\System\Enum;

//http://php.net/manual/de/function.getprotobyname.php
abstract class ProtocolType extends Enum {

	const IP = 'ip';
	const ICMP = 'icmp';
	const GGP = 'ggp';
	const TCP = 'tcp';
	const EGP = 'egp';
	const PUP = 'pup';
	const UDP = 'udp';
	const HMP = 'hmp';
	const XNX_IDP = 'xns-idp';
	const RDP = 'rdp';
	const RVD = 'rvd';

	public static function getNumber( $protocolType ) {

		return getprotobyname( $protocolType );
	}
}