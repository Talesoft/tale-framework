<?php

namespace Tale\Runtime;

use Tale\System\StringUtils,
	Tale\System\InvalidArgumentException,
	Tale\System\Exception;

class Dispatcher {

	public function __construct( $nameSpace = null ) {
		
		$this->_nameSpace = $nameSpace;
	}

	public function dispatch( $target, array $args = null ) {

		$args = $args ? $args : [];
		$parts = explode( '.', $target );
		$cnt = count( $parts );

		if( $cnt < 2 )
			throw new InvalidArgumentException( 'Passed dispatch string needs to consist of at least 2 delimeted values (className.methodName)' );

		$className = StringUtils::camelize( $parts[ $cnt - 2 ] );
		$methodName = StringUtils::variablize( $parts[ $cnt - 1 ] );
		
		if( $cnt > 2 ) {

			$cnt -= 2;
			while( $cnt-- )
				$className = StringUtils::camelize( $parts[ $cnt ] ).'\\'.$className;
		}

		if( $this->_nameSpace )
			$className = "$this->_nameSpace\\$className";

		var_dump( "DISPATCH: $className->$methodName" );

		$baseClass = $this->getBaseClass();
		if( !class_exists( $className ) || ( $baseClass && !is_subclass_of( $className, $baseClass ) )
			throw new Exception( 'The supplied dispatch string doesn\'t match any available class name for this dispatcher' );
		else if( !method_exists( $className, $methodName ) )
			throw new Exception( "The supplied dispatch string doesn\'t match any available method names in the $className instance" );

		$instance = $this->createInstance( $className );
		return $this->getDispatchResult( $instance, $methodName, $args );
	}

	protected function inflectClassName( $className ) {

		return $className;
	}

	protected function inflectMethodName( $methodName ) {

		return $methodName;
	}

	protected function getBaseClass() {

		return null;
	}

	protected function createInstance( $className ) {

		return new $className(); 
	}

	protected function getDispatchResult( $instance, $methodName, $args ) {

		return call_user_func_array( [ $instance, $methodName ], $args );
	}
}