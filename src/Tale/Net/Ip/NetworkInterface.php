<?php

namespace Tale\Net\Ip;

interface IpNetworkInterface
{

	public function getAddress();

	public function setAddress(IpAddressInterface $address);

	public function getNetMask();

	public function setNetMask($netMask);

	public function getCidrNetMask();

	public function setCidrNetMask($cidrNetMask);
}