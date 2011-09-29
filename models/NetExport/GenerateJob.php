<?php

class NetExport_GenerateJob extends Omeka_JobAbstract
{
    public function perform()
    {
        if ($memoryLimit = netexport_get_config('memoryLimit')) {
            ini_set('memory_limit', $memoryLimit);
            _log("Set memory limit to $memoryLimit");
        }
        //@TODO: figure out this magic when I move to testing on a system that actually has S3.
       // $this->modifyS3Expiration(10080); //expires in one week
        $this->modifyS3Expiration(1000); //testing
        $fileId = $this->_options['fileId'];
        $report = $this->_db->getTable('NetExport_File')->find($fileId);
        $generator = $report->getGenerator();
        $generator->generate();
        $report->forceSave();
    }
    
    public function modifyS3Expiration($minutes)
    {
        $context = Omeka_Context::getInstance();
        $storageOptions = $context->getConfig('basic')->storage;
        if(isset($storageOptions) ) {
            $storageOptions = $storageOptions->toArray();
            $storageOptions['adapterOptions']['expiration'] = $minutes;
            //$storage = Zend_Registry::get('storage');
            //$storage->setOptions($storageOptions);
            $storage = new Omeka_Storage($storageOptions);
            Zend_Registry::set('storage', $storage);
            $context->setStorage($storage);
        }
    }
}
