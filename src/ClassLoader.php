<?php

namespace Tale;

/**
 * A class loader with zero dependencies (and zero configuration, if you like)
 *
 * You might just include the file containing this class and get a class-loader up and running easily
 *
 * @version 1.0
 * @featureState Stable
 *
 * @package Tale
 */
class ClassLoader {

    /**
     * The default file name pattern to match the common PHP standard
     *
     * @var string
     */
    const DEFAULT_PATTERN = '%s.php';

    /**
     * The path the loader currently uses.
     * null to use the include path
     *
     * @var string|null
     */
    private $_path;

    /**
     * The namespace currently associated to the loader
     * null to use all namespaces
     *
     * @var string|null
     */
    private $_nameSpace;

    /**
     * The file name pattern used for this loader
     *
     * @var string
     */
    private $_fileNamePattern;

    /**
     * The handle given to spl_autoload_register
     *
     * @var callable
     */
    private $_handle;

    /**
     * The flag that tells if the loader is registered or not
     *
     * @var bool
     */
    private $_registered;

    /**
     * Creates a new auto-loader
     *
     * Call $this->register() to register the loader after instanciation
     *
     * @param string|null $path            The path to search in, null to use the include path
     * @param string|null $nameSpace       The namespace to filter to, null for all namespaces
     * @param string      $fileNamePattern The file name pattern, %s.php by default
     */
    public function __construct( $path = null, $nameSpace = null, $fileNamePattern = null ) {

        $this->_path = $path;
        $this->_nameSpace = $nameSpace;
        $this->_fileNamePattern = $fileNamePattern ? $fileNamePattern : self::DEFAULT_PATTERN;
        $this->_handle = [ $this, 'load' ];
    }

    /**
     * Unregisters the auto-loader automatically on object destruction
     */
    public function __destruct() {

        if( $this->_registered )
            $this->unregister();
    }

    /**
     * Gets the currently used path of the class loader
     *
     * @return string|null
     */
    public function getPath() {

        return $this->_path;
    }

    /**
     * Sets the currently used path of the class loader
     * Set to null to use the include path
     *
     * @param string|null $path
     *
     * @return $this
     */
    public function setPath( $path ) {

        $this->_path = $path;
        return $this;
    }

    /**
     * Gets the namespace currently associated with this class loader
     *
     * @return string|null
     */
    public function getNameSpace() {

        return $this->_nameSpace;
    }

    /**
     * Sets the namespace associated with this class loader
     * Set to null for all namespaces
     *
     * @param string|null $nameSpace
     *
     * @return $this
     */
    public function setNameSpace( $nameSpace ) {

        $this->_nameSpace = $nameSpace;

        return $this;
    }

    /**
     * Returns the current file name pattern
     * %s.php by default
     *
     * @return string
     */
    public function getFileNamePattern() {

        return $this->_fileNamePattern;
    }

    /**
     * Sets the file name pattern
     *
     * @param string $fileNamePattern
     *
     * @return $this
     */
    public function setFileNamePattern( $fileNamePattern ) {

        $this->_fileNamePattern = $fileNamePattern;

        return $this;
    }

    /**
     * Returns the callback used by spl_autoload_register, usually [ $this, 'load' ]
     *
     * @return callable
     */
    public function getHandle() {

        return $this->_handle;
    }

    /**
     * Registers the class loader (activates it)
     * Basically, spl_autoload_register( $this->getHandle() )
     *
     * @return $this
     */
    public function register() {

        spl_autoload_register( $this->_handle );
        $this->_registered = true;

        return $this;
    }

    /**
     * Unregisters the class loader (deactivates it)
     * Basically, spl_autoload_unregister( $this->getHandle() )
     *
     * @return $this
     */
    public function unregister() {

        spl_autoload_unregister( $this->_handle );
        $this->_registered = false;

        return $this;
    }

    /**
     * Checks if the loader is registered or not
     *
     * @return bool
     */
    public function isRegistered() {

        return $this->_registered;
    }

    /**
     * Loads a class based on its class name
     * If it's not found, it's simply ignored in order to let another loader try to load it
     * If it's the only loader, an error will be thrown after a failed loading
     *
     * There's no loader recursion, the final check uses the second parameter of class_exists() to
     * not trigger another autoloader inside this one
     *
     * @param $className The FQCN of the class to load
     *
     * @return bool Has the class been loaded or not
     */
    public function load( $className ) {

        $name = $className;
        if( $this->_nameSpace ) {

            $ns = rtrim( $this->_nameSpace, '\\' ).'\\';

            $nameLen = strlen( $className );
            $nsLen = strlen( $ns );

            if( $nameLen < $nsLen || substr( $className, 0, $nsLen ) !== $ns )
                return false;

            $name = substr( $name, $nsLen );
        }

        $ds = \DIRECTORY_SEPARATOR;
        $path = $this->_path ? $this->_path.$ds : '';
        $path .= str_replace( [ '_', '\\' ], $ds, sprintf( $this->_fileNamePattern, $name ) );

        if( ( $path = stream_resolve_include_path( $path ) ) !== false )
            include $path;

        return class_exists( $className, false ); 
    }
}