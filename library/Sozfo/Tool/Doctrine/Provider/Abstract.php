<?php
abstract class Sozfo_Tool_Doctrine_Provider_Abstract extends Zend_Tool_Project_Provider_Abstract
{
    protected static $_config;
    protected static $_environment = 'development';
    protected static $_paths = array();
    
    public function __construct ()
    {
        if (!self::$_isInitialized) {
            $contextRegistry = Zend_Tool_Project_Context_Repository::getInstance();
            $contextRegistry->addContextsFromDirectory(
                dirname(dirname(__FILE__)) . '/Context/', 'Sozfo_Tool_Project_Context_'
            );
        }
        
        parent::__construct();
    }

    public function getRegistry ()
    {
        return $this->_registry;
    }

    public static function getDirectory(Zend_Tool_Project_Profile $profile, $type, $module)
    {
        $resource = self::_getModuleDirectoryResource($profile, $module);
        $path     = $resource->getContext()->getPath();

        if (!isset(self::$_paths['modules'][$type])) {
            throw new Zend_Tool_Project_Provider_Exception('Type "' . $type . '" not supported to determine module path');
        }

        return $path .= '/' . self::$_paths['modules'][$type];
    }

    protected static function _getApplicationConfigsFileResource (Zend_Tool_Project_Profile $profile)
    {
        $profileSearchParams = array('applicationDirectory', 'configsDirectory', 'applicationConfigFile');
        return $profile->search($profileSearchParams);
    }

    protected static function _getModuleDirectoryResource (Zend_Tool_Project_Profile $profile, $moduleName)
    {
        $profileSearchParams = array('modulesDirectory', 'moduleDirectory' => array('moduleName' => $moduleName));
        return $profile->search($profileSearchParams);
    }

    protected function _loadDoctrineConfig ($enableConnections = false)
    {
        // If config is set, the doctrine config is already loaded
        if (isset(self::$_config)) {
            return;
        }
        
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        if(!($resource = self::_getApplicationConfigsFileResource($this->_loadedProfile))) {
            throw new Zend_Tool_Project_Provider_Exception('No application configuration file was found');
        }

        switch ($resource->getAttribute('type')) {
            case 'ini':
                require_once 'Zend/Config/Ini.php';
                $config = new Zend_Config_Ini($resource->getContext()->getPath(), self::$_environment);
                break;
            case 'xml':
                require_once 'Zend/Config/Xml.php';
                $config = new Zend_Config_Xml($resource->getContext()->getPath(), self::$_environment);
                break;
            default:
                throw new Zend_Tool_Project_Provider_Exception('Type of configuration file not supported');
        }

        if (!isset($config->resources->doctrine)) {
            throw new Zend_Tool_Project_Provider_Exception('No doctrine resource found in configuration file');
        }

        if (isset($config->resources->doctrine->iniPath)) {
            self::$_config = new Zend_Config_Ini($config->resources->doctrine->iniPath, self::$_environment);
        } else {
            self::$_config = $config->resources->doctrine;
        }

        foreach (self::$_config->toArray() as $type => $options) {
            switch ($type) {
                case 'autoload':
                    self::_initDoctrineAutoload();
                    break;
                case 'connection':
                case 'connections':
                    if ($enableConnections) {
                        self::_initDoctrineConnections();
                    }
                    break;
                case 'paths':
                    self::_initDoctrinePaths();
                    break;
                case 'manager':
                    self::_initDoctrineManager();
                    break;
            }
        }

    }

    protected static function _initDoctrineAutoload ()
    {
        if ('1' === self::$_config->autoload) {
            require_once 'Zend/Loader/Autoloader.php';
            Zend_Loader_Autoloader::getInstance()
                                  ->registerNamespace('Doctrine')
                                  ->pushAutoloader(array('Doctrine', 'autoload'));
            spl_autoload_register(array('Doctrine', 'modelsAutoload'));
        }
    }

    protected static function _initDoctrineConnections ()
    {
        if (isset(self::$_config->connection)) {
            $connections = array(self::$_config->connection->toArray());
        } elseif (isset(self::$_config->connections)) {
            $connections = self::$_config->connections->toArray();
        } else {
            throw new Zend_Tool_Project_Provider_Exception('No connection information provided');
        }

        foreach ($connections as $name => $options) {
            if (!is_array($options)) {
                throw new Zend_Tool_Project_Provider_Exception('Invalid connection on ' . $name);
            }

            if (!array_key_exists('dsn', $options)) {
                throw new Zend_Tool_Project_Provider_Exception('Undefined DSN on ' . $name);
            }

            if (empty($options['dsn'])) {
                throw new Zend_Tool_Project_Provider_Exception('Invalid DSN on ' . $name);
            }

            $dsn = (is_array($options['dsn']))
                 ? self::_buildDsnFromArray($options['dsn'])
                 : $options['dsn'];

            $conn = Doctrine_Manager::connection($dsn, $name);

            if (array_key_exists('attributes', $options)) {
                self::_setAttributes($conn, $options['attributes']);
            }
        }
    }

    protected static function _initDoctrinePaths ()
    {
        $options = array_change_key_case(self::$_config->paths->toArray(), CASE_LOWER);

        foreach ($options as $key => $value) {
            if (!is_array($value)) {
                throw new Zend_Tool_Project_Provider_Exception("Invalid paths on $key.");
            }

            self::$_paths[$key] = array();

            foreach ($value as $subKey => $subVal) {
                if (!empty($subVal)) {
                    if ($key === 'modules') {
                        $path = $subVal;
                    } else {
                        $path = realpath($subVal);

                        if (!is_dir($path)) {
                            throw new Zend_Tool_Project_Provider_Exception("$subVal does not exist.");
                        }
                    }

                    self::$_paths[$key][$subKey] = $path;
                }
            }
        }
    }

    protected static function _initDoctrineManager ()
    {
        $options = array_change_key_case(self::$_config->manager->toArray(), CASE_LOWER);

        if (array_key_exists('attributes', $options)) {
            $this->_setAttributes(
                Doctrine_Manager::getInstance(),
                $options['attributes']
            );
        }
    }

    /**
     * Build DSN string from an array
     *
     * @param   array $dsn
     * @return  string
     */
    protected static function _buildDsnFromArray(array $dsn)
    {
        $options = null;
        if (array_key_exists('options', $dsn)) {
            $options = http_build_query($dsn['options']);
        }

        return sprintf('%s://%s:%s@%s/%s?%s',
            $dsn['adapter'],
            $dsn['user'],
            $dsn['pass'],
            $dsn['hostspec'],
            $dsn['database'],
            $options);
    }

    /**
     * Set attributes for a Doctrine_Configurable instance
     *
     * @param   Doctrine_Configurable $object
     * @param   array $attributes
     * @return  void
     * @throws  Zend_Application_Resource_Exception
     */
    protected static function _setAttributes(Doctrine_Configurable $object, array $attributes)
    {
        $reflect = new ReflectionClass('Doctrine');
        $doctrineConstants = $reflect->getConstants();

        $attributes = array_change_key_case($attributes, CASE_UPPER);

        foreach ($attributes as $key => $value) {
            if (!array_key_exists($key, $doctrineConstants)) {
                throw new Zend_Tool_Project_Provider_Exception("Invalid attribute $key.");
            }

            $attrIdx = $doctrineConstants[$key];

            if (is_string($value)) {
                $value = strtoupper($value);
                if (array_key_exists($value, $doctrineConstants)) {
                    $attrVal = $doctrineConstants[$value];
                    $object->setAttribute($attrIdx, $attrVal);
                }
            }
        }
    }
}