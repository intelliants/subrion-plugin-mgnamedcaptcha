<?php

/**
 * This is used to store keys and values 
 */
abstract class NamedButtonsCaptcha_KeyStorage_Abstract
{
    protected $_options = array();
    
    /**
     * Constructor
     * 
     * @author thuan.nguyen
     * @param array $options 
     */
    public function __construct(array $options = array())
    {
        $this->_options = $options;
        
        $this->init();
    }
    
    /**
     * Set options to the storage object. How the options are treated depends
     * on the sub classes of this class.
     * @param array $options
     * @return NamedButtonsCaptcha_KeyStorage_Abstract 
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
        
        return $this;
    }
    
    /**
     * Just an empty function. Let the sub classes implement this if they need
     * initialization after constructing the object.
     */
    public function init() {}
    
    /**
     * Read value by key
     * @param string $key
     * @return string, null if the key does not exist 
     */
    abstract public function read($key);
    
    /**
     * Check if the key exists
     * 
     * @param string $key 
     * @return boolean
     */
    abstract public function exists($key);
    
    /**
     * Write key value
     * 
     * @param string $key
     * @param string $value
     */
    abstract public function write($key, $value);
}