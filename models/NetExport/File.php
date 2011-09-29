<?php
/**
 * @package Reports
 * @subpackage Models
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Model class for generated report files.
 *
 * @package Reports
 * @subpackage Models
 */
class NetExport_File extends Omeka_Record
{
    const STATUS_STARTING    = 'starting';
    const STATUS_IN_PROGRESS = 'in progress';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_ERROR       = 'error';
    
    public $id;
    public $report_id;
    public $type;
    public $filename;
    public $status;
    public $messages;
    public $created;
    public $options;

    /**
     * Unlink the associated file.
     */
    protected function afterDelete()
    {
        $filename = netexport_save_directory() . '/' . $this->filename;
        if (is_writable($filename)) {
            unlink($filename);
        }
    }

    /**
     * Gets the report associated with this object.
     *
     * @return ReportsReport The report associated with the file.
     */
    public function getReport()
    {
        if($report_id = $this->report_id) {
            return $this->_db->getTable('NetExport_Export')->find($report_id);
        }
    }
    
    /**
     * Gets the report generator used for this file.
     *
     * @return string Name of the report generator class.
     */
    public function getGenerator()
    {
        return NetExport_Generator::factory($this);
    }
}
