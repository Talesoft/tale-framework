<?php

namespace Tale\Net;

interface SocketInterface {

	public function getAddressFamily()
	public function getType();

	public function getEndPoint();
	public function setEndPoint( EndPoint $endPoint );
	public function getTimeOut();
	public function setTimeOut( $timeOut );

	public function isBlocking();

	public function isConnected();
	public function connect( EndPoint $endPoint );
	public function disconnect();

	public function listen( $backLog = null );
}