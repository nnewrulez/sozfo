<?php
class Sozfo_Log_Writer_Twitter extends Zend_Log_Writer_Abstract
{
    protected $_username;
    protected $_password;
    protected $_twitter;

    public function __construct ($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;

        $this->_formatter = new Zend_Log_Formatter_Simple();
    }

    protected function _getTwitter ()
    {
        if (null === $this->_twitter) {
            $this->_twitter = new Zend_Service_Twitter($this->_username, $this->_password);
            $response = $this->_twitter->account->verifyCredentials();
            if ($response->isError()) {
                throw new Zend_Log_Exception('Provided credentials for Twitter log writer are wrong');
            }
        }
        return $this->_twitter;
    }

    public function _write ($event)
    {
        $line = $this->_formatter->format($event);
        $this->_getTwitter()->status->update($line);
    }
}