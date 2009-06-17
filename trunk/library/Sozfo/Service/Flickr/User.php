<?php
class Sozfo_Service_Flickr_User extends Sozfo_Service_Flickr_Abstract
{
    protected $_username;
    protected $_realname;
    protected $_location;
    protected $_profileUrl;
    protected $_photosUrl;
    protected $_mobileUrl;
    protected $_pro;
    protected $_count;
    protected $_views;

    public function find ($id)
    {
        if (false !== strstr($id, '@')) {
            return $this->findByEmail($id);
        } elseif (false !== strstr($id, Sozfo_Service_Flickr_Abstract::URI_BASE)) {
            return $this->findByUrl($id);
        } else {
            return $this->findByUsername($id);
        }
    }

    public function findByUsername ($name)
    {
        $user = $this->_request('people.findByUsername', array('username'=>$name))->user;
        $this->setId($user['id']);
        $this->setUsername($user['username']);
        return $this;
    }

    public function findByEmail ($email)
    {
        $response = $this->_request( 'people.findByEmail', array('find_email'=>$email) );
        $this->setId($response['user']['id']);
        $this->setUsername($response['user']['username']);
        return $this;
    }

    public function findByUrl ($url)
    {
        $response = $this->_request( 'urls.lookupUser', array('url'=>$url) );
        $this->setId($response['user']['id']);
        $this->setUsername($response['user']['username']);
        return $this;
    }

    public function import ($data)
    {
        $this->setId($data['id'])
             ->setUsername($data['username'])
             ->setRealname($data['realname'])
             ->setLocation($data['location'])
             ->setProfileUrl($data['profileurl'])
             ->setPhotosUrl($data['photosurl'])
             ->setMobileUrl($data['mobileurl'])
             ->setPro($data['ispro'])
             ->setCount($data['photos']['count']);

        if(isset($data['views'])) {
            $this->setViews($data['views']);
        }
        return $this;
    }

    public function setUsername ($name)
    {
        $this->_username = (string) $name;
        return $this;
    }

    public function getUsername ()
    {
        if (null === $this->_username) {
            $this->_loadInfo();
        }
        return $this->_username;
    }

    public function getName ()
    {
        return $this->getUsername();
    }

    public function setRealname ($name)
    {
        $this->_realname = (string) $name;
        return $this;
    }

    public function getRealname ()
    {
        if (null === $this->_realname) {
            $this->_loadInfo();
        }
        return $this->_realname;
    }

    public function setLocation ($location)
    {
        $this->_location = (string) $location;
        return $this;
    }

    public function getLocation ()
    {
        if (null === $this->_location) {
            $this->_loadInfo();
        }
        return $this->_location;
    }

    public function setProfileUrl ($url)
    {
        $this->_profileUrl = (string) $url;
        return $this;
    }

    public function getProfileUrl ()
    {
        if (null === $this->_profileUrl) {
            $this->_loadInfo();
        }
        return $this->_profileUrl;
    }

    public function setPhotosUrl ($url)
    {
        $this->_photosUrl = (string) $url;
        return $this;
    }

    public function getPhotosUrl ()
    {
        if (null === $this->_photosUrl) {
            $this->_loadInfo();
        }
        return $this->_photosUrl;
    }

    public function setMobileUrl ($url)
    {
        $this->_mobileUrl = (string) $url;
        return $this;
    }

    public function getMobileUrl ()
    {
        if (null === $this->_mobileUrl) {
            $this->_loadInfo();
        }
        return $this->_mobileUrl;
    }

    public function setPro ($pro)
    {
        $this->_pro = (bool) $pro;
        return $this;
    }

    public function getPro ()
    {
        if (null === $this->_pro) {
            $this->_loadInfo();
        }
        return $this->_pro;
    }

    public function isPro ()
    {
        return $this->getPro();
    }

    public function setCount ($count)
    {
        $this->_count = (int) $count;
        return $this;
    }

    public function getCount ()
    {
        if (null === $this->_count) {
            $this->_loadInfo();
        }
        return $this->_count;
    }

    public function setViews ($view)
    {
        $this->_views = (int) $view;
        return $this;
    }

    public function getViews ()
    {
        if (null === $this->_views) {
            $this->_loadInfo();
        }
        return $this->_views;
    }

    protected function _loadInfo(){
        $user = $this->_request('people.getInfo', array('user_id'=>$this->getId()))->person;
        $this->import($user);
    }
}