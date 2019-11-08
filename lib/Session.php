<?php

class Session implements IteratorAggregate, ArrayAccess, Countable {

    /**
     * @var boolean whether the session should be automatically started when the session application component is initialized, defaults to true.
     */
    public $autoStart = true;

    public function getIterator() {
        return new SessionIterator();
    }

    /**
     * Initializes the application component.
     * This method is invoked by application.
     */
    public function init() {
        if ($this->autoStart)
            $this->open();
        register_shutdown_function(array($this, 'close'));
    }

    /**
     * Starts the session if it has not started yet.
     */
    public function open($debug = false) {
        @session_start();
        if ($debug && session_id() == '') {
            throw new Exception('Failed to start session.');
        }
    }

    /**
     * Ends the current session and store session data.
     */
    public function close() {
        if (session_id() !== '')
            @session_write_close();
    }

    /**
     * Frees all session variables and destroys all data registered to a session.
     */
    public function destroy() {
        if (session_id() !== '') {
            @session_unset();
            @session_destroy();
        }
    }

    /**
     * @return boolean whether the session has started
     */
    public function getIsStarted() {
        return session_id() !== '';
    }

    /**
     * @return string the current session ID
     */
    public function getSessionID() {
        return session_id();
    }

    /**
     * @param string $value the session ID for the current session
     */
    public function setSessionID($value) {
        session_id($value);
    }

    /**
     * Updates the current session id with a newly generated one .
     * Please refer to {@link http://php.net/session_regenerate_id} for more details.
     * @param boolean $deleteOldSession Whether to delete the old associated session file or not.
     * @since 1.1.8
     */
    public function regenerateID($deleteOldSession = false) {
        session_regenerate_id($deleteOldSession);
    }

    /**
     * @return string the current session name
     */
    public function getSessionName() {
        return session_name();
    }

    /**
     * @param string $value the session name for the current session, must be an alphanumeric string, defaults to PHPSESSID
     */
    public function setSessionName($value) {
        session_name($value);
    }

    /**
     * @return string the current session save path, defaults to '/tmp'.
     */
    public function getSavePath() {
        return session_save_path();
    }

    /**
     * @param string $value the current session save path
     * @throws CException if the path is not a valid directory
     */
    public function setSavePath($value) {
        if (is_dir($value))
            session_save_path($value);
        else
            throw new Exception('HttpSession.savePath "' . $value . '" is not a valid directory.');
    }

    /**
     * @return array the session cookie parameters.
     * @see http://us2.php.net/manual/en/function.session-get-cookie-params.php
     */
    public function getCookieParams() {
        return session_get_cookie_params();
    }

    /**
     * Sets the session cookie parameters.
     * The effect of this method only lasts for the duration of the script.
     * Call this method before the session starts.
     * @param array $value cookie parameters, valid keys include: lifetime, path, domain, secure.
     * @see http://us2.php.net/manual/en/function.session-set-cookie-params.php
     */
    public function setCookieParams($value) {
        $data = session_get_cookie_params();
        extract($data);
        extract($value);
        if (isset($httponly))
            session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
        else
            session_set_cookie_params($lifetime, $path, $domain, $secure);
    }

    /**
     * @return string how to use cookie to store session ID. Defaults to 'Allow'.
     */
    public function getCookieMode() {
        if (ini_get('session.use_cookies') === '0')
            return 'none';
        else if (ini_get('session.use_only_cookies') === '0')
            return 'allow';
        else
            return 'only';
    }

    /**
     * @param string $value how to use cookie to store session ID. Valid values include 'none', 'allow' and 'only'.
     */
    public function setCookieMode($value) {
        if ($value === 'none') {
            ini_set('session.use_cookies', '0');
            ini_set('session.use_only_cookies', '0');
        } else if ($value === 'allow') {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '0');
        } else if ($value === 'only') {
            ini_set('session.use_cookies', '1');
            ini_set('session.use_only_cookies', '1');
        } else
            throw new Exception('HttpSession.cookieMode can only be "none", "allow" or "only".');
    }

    /**
     * @return integer the probability (percentage) that the gc (garbage collection) process is started on every session initialization, defaults to 1 meaning 1% chance.
     */
    public function getGCProbability() {
        return (int) ini_get('session.gc_probability');
    }

    /**
     * @param integer $value the probability (percentage) that the gc (garbage collection) process is started on every session initialization.
     * @throws CException if the value is beyond [0,100]
     */
    public function setGCProbability($value) {
        $value = (int) $value;
        if ($value >= 0 && $value <= 100) {
            ini_set('session.gc_probability', $value);
            ini_set('session.gc_divisor', '100');
        } else
            throw new Exception('HttpSession.gcProbability "' . $value . '" is invalid. It must be an integer between 0 and 100.');
    }

    /**
     * @return boolean whether transparent sid support is enabled or not, defaults to false.
     */
    public function getUseTransparentSessionID() {
        return ini_get('session.use_trans_sid') == 1;
    }

    /**
     * @param boolean $value whether transparent sid support is enabled or not.
     */
    public function setUseTransparentSessionID($value) {
        ini_set('session.use_trans_sid', $value ? '1' : '0');
    }

    /**
     * @return integer the number of seconds after which data will be seen as 'garbage' and cleaned up, defaults to 1440 seconds.
     */
    public function getTimeout() {
        return (int) ini_get('session.gc_maxlifetime');
    }

    /**
     * @param integer $value the number of seconds after which data will be seen as 'garbage' and cleaned up
     */
    public function setTimeout($value) {
        ini_set('session.gc_maxlifetime', $value);
    }

    /**
     * Returns the number of items in the session.
     * @return integer the number of session variables
     */
    public function getCount() {
        return count($_SESSION);
    }

    /**
     * Returns the number of items in the session.
     * This method is required by Countable interface.
     * @return integer number of items in the session.
     */
    public function count() {
        return $this->getCount();
    }

    /**
     * @return array the list of session variable names
     */
    public function getKeys() {
        return array_keys($_SESSION);
    }

    /**
     * Returns the session variable value with the session variable name.
     * This method is very similar to {@link itemAt} and {@link offsetGet},
     * except that it will return $defaultValue if the session variable does not exist.
     * @param mixed $key the session variable name
     * @param mixed $defaultValue the default value to be returned when the session variable does not exist.
     * @return mixed the session variable value, or $defaultValue if the session variable does not exist.
     * @since 1.1.2
     */
    public function get($key, $defaultValue = null) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
    }

    /**
     * Returns the session variable value with the session variable name.
     * This method is exactly the same as {@link offsetGet}.
     * @param mixed $key the session variable name
     * @return mixed the session variable value, null if no such variable exists
     */
    public function itemAt($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    /**
     * Adds a session variable.
     * Note, if the specified name already exists, the old value will be removed first.
     * @param mixed $key session variable name
     * @param mixed $value session variable value
     */
    public function add($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Removes a session variable.
     * @param mixed $key the name of the session variable to be removed
     * @return mixed the removed value, null if no such session variable.
     */
    public function remove($key) {
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);
            return $value;
        } else
            return null;
    }

    /**
     * Removes all session variables
     */
    public function clear() {
        foreach (array_keys($_SESSION) as $key)
            unset($_SESSION[$key]);
    }

    /**
     * @param mixed $key session variable name
     * @return boolean whether there is the named session variable
     */
    public function contains($key) {
        return isset($_SESSION[$key]);
    }

    /**
     * @return array the list of all session variables in array
     */
    public function toArray() {
        return $_SESSION;
    }

    /**
     * This method is required by the interface ArrayAccess.
     * @param mixed $offset the offset to check on
     * @return boolean
     */
    public function offsetExists($offset) {
        return isset($_SESSION[$offset]);
    }

    /**
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to retrieve element.
     * @return mixed the element at the offset, null if no element is found at the offset
     */
    public function offsetGet($offset) {
        return isset($_SESSION[$offset]) ? $_SESSION[$offset] : null;
    }

    /**
     * This method is required by the interface ArrayAccess.
     * @param integer $offset the offset to set element
     * @param mixed $item the element value
     */
    public function offsetSet($offset, $item) {
        $_SESSION[$offset] = $item;
    }

    /**
     * This method is required by the interface ArrayAccess.
     * @param mixed $offset the offset to unset element
     */
    public function offsetUnset($offset) {
        unset($_SESSION[$offset]);
    }

}

class SessionIterator implements Iterator {

    /**
     * @var array list of keys in the map
     */
    private $_keys;

    /**
     * @var mixed current key
     */
    private $_key;

    /**
     * Constructor.
     * @param array the data to be iterated through
     */
    public function __construct() {
        $this->_keys = array_keys($_SESSION);
    }

    /**
     * Rewinds internal array pointer.
     * This method is required by the interface Iterator.
     */
    public function rewind() {
        $this->_key = reset($this->_keys);
    }

    /**
     * Returns the key of the current array element.
     * This method is required by the interface Iterator.
     * @return mixed the key of the current array element
     */
    public function key() {
        return $this->_key;
    }

    /**
     * Returns the current array element.
     * This method is required by the interface Iterator.
     * @return mixed the current array element
     */
    public function current() {
        return isset($_SESSION[$this->_key]) ? $_SESSION[$this->_key] : null;
    }

    /**
     * Moves the internal pointer to the next array element.
     * This method is required by the interface Iterator.
     */
    public function next() {
        do {
            $this->_key = next($this->_keys);
        } while (!isset($_SESSION[$this->_key]) && $this->_key !== false);
    }

    /**
     * Returns whether there is an element at current position.
     * This method is required by the interface Iterator.
     * @return boolean
     */
    public function valid() {
        return $this->_key !== false;
    }

}
