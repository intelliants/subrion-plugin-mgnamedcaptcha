<?php

require_once dirname(__FILE__) . '/Abstract.php';

class NamedButtonsCaptcha_KeyStorage_Apc extends NamedButtonsCaptcha_KeyStorage_Abstract
{
    protected $_ttl = 0;
    protected $_namespace = 'dtc';
    
    public function setNamespace($namespace)
    {
        $this->_namespace = $namespace;
        
        return $this;
    }
    
    public function setOptions(array $options)
    {
        parent::setOptions($options);
                
        if (isset($options['namespace'])) {
            
            $this->_namespace = $options['namespace'];
        }
                
        if (isset($options['ttl'])) {
            
            $this->_ttl = $options['ttl'];
        }
        
        return $this;
    }
    
    public function read($key)
    {
        $key = $this->_namespace . '.' . $key;
        
        return apc_exists($key) ? apc_fetch($key) : null;
    }
    
    public function exists($key)
    {
        return apc_exists($this->_namespace . '.' . $key);
    }
    
    public function write($key, $value)
    {
        apc_store($this->_namespace . '.' . $key, $value, $this->_ttl);
        
        return $this;
    }
}
