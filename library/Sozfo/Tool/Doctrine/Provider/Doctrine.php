<?php
require_once 'Sozfo/Tool/Doctrine/Provider/Abstract.php';
class Sozfo_Tool_Doctrine_Provider_Doctrine extends Sozfo_Tool_Doctrine_Provider_Abstract
{
    protected $_specialties = array('Db', 'Models', 'Schema', 'Tables', 'Data');

    public function build ($module = null, $environment = null)
    {
        if (null !== $environment) {
            self::$_environment = $environment;
        }
        $this->generateModels($module);exit;
        $this->generateSchema($module);
        $this->loadData($module);
    }

    public function createDb ($module = null)
    {
        $this->_loadDoctrineConfig(true);
        $connections = array();
        Doctrine::createDatabases($connections);
    }
    
    public function dropDb ($module = null)
    {
        $this->_loadDoctrineConfig(true);
        $connections = array();
        Doctrine::dropDatabases($connections);
    }

    public function migrateDb ($module = null, $to = null)
    {
        $this->_loadDoctrineConfig(true);
        $directory = self::getDirectory('migrations', $to);
        Doctrine::migrate($directory, $to);
    }

    public function generateModels ($module = null, $from = 'yaml')
    {
        $this->_loadDoctrineConfig(false);
        $directory = self::getDirectory($this->_loadedProfile, 'models', $module);
        $schema    = self::getDirectory($this->_loadedProfile, 'schema', $module);
        Doctrine::generateModelsFromYaml($schema, $directory);
    }

    public function generateTables ($module = null, $from = 'models')
    {
    }

    public function generateSchema ($module = null, $from = 'models')
    {
    }

    public function loadData ($module = null, $individualFiles = false)
    {
        $this->_loadDoctrineConfig(true);
        $directory = self::getDirectory('fixtures', $module);
        Doctrine::loadData($directory, $individualFiles);
    }

    public function dumpData ($module = null, $append = false)
    {
        $this->_loadDoctrineConfig(true);
        $directory = self::getDirectory('fixtures', $module);
        Doctrine::dumpData($directory, $append);
    }
}