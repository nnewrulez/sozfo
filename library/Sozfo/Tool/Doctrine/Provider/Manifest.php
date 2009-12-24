<?php
class Sozfo_Tool_Doctrine_Provider_Manifest implements Zend_Tool_Framework_Manifest_ProviderManifestable
{
    public function getProviders ()
    {
        require_once 'Sozfo/Tool/Doctrine/Provider/Doctrine.php';
        return array(
            new Sozfo_Tool_Doctrine_Provider_Doctrine,
        );
    }
}