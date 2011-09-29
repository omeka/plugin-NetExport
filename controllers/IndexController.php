<?php
/**
 * @package Reports
 * @subpackage Controllers
 * @copyright Copyright (c) 2011 Center for History and New Media
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
 
/**
 * Index controller
 *
 * @package Reports
 * @subpackage Controllers
 */
class NetExport_IndexController extends Omeka_Controller_Action
{
    private $_jobDispatcher;

    /**
     * Sets the model class for the Reports controller.
     */
    public function init()
    {
        $this->_helper->db->setDefaultModelName('NetExport_Export');
        $this->_jobDispatcher = $this->getInvokeArg('bootstrap')->jobs;
    }
    
    /**
     * Displays the browse page for all reports.
     */
    public function browseAction()
    {
        $saveDirectory = netexport_save_directory();
        $reportsDisplay = array();
        if (!$saveDirectory) {
            $this->flashError('The report save directory does not exist.');
        }
        if(!is_writable(netexport_save_directory())) {
            $this->flash('Warning: The directory ' . $saveDirectory .
                         ' must be writable by the server for reports to be'.
                         ' generated.', Omeka_Controller_Flash::ALERT);
        }
        
        $reports = $this->getTable('NetExport_Export')->findAllReports();
        foreach($reports as $report) {
            $id = $report->id;
            $creator = $report->creator;
            $user = $this->getTable('User')->find($creator);
            if(method_exists($user, 'getName')) {
                $userName = $this->getTable('User')->find($creator)->getName();
            } else {
                $userName = $this->getTable('User')->find($creator)->Entity->getName();
            }
            $query = unserialize($report->query);
            $params = netexport_convert_search_filters($query);
            $count = $this->getTable('Item')->count($params);
            
            $reportsDisplay[] = array(
                'reportObject' => $report,
                'userName' => $userName,
                'count' => $count);
        }
        $this->view->reports = $reportsDisplay;
        $this->view->formats = netexport_get_output_formats();
    }
    
    public function addAction()
    {
        $class = $this->_helper->db->getDefaultModelName();
        $record = new $class();
        require_once dirname(__FILE__) . '/../forms/Exports/Detail.php';
        $form = new NetExport_Form_Detail();
        $this->view->form = $form;
        $this->view->assign(array(strtolower($class) => $record));

        if (!$this->_request->isPost()) {
            return;
        }
        if (!$form->isValid($this->_request->getPost())) {
            return;
        }

        try {
            if ($record->saveForm($form->getValues())) {
                $this->redirect->gotoRoute(
                    array(
                        'module' => 'net-export',
                        'id'     => $record->id,
                        'action' => 'query'
                    ),
                    'default'
                );
            }
        } catch (Omeka_Validator_Exception $e) {
            $this->flashValidationErrors($e);
        }
    }
    
    /**
     * Edits the filter for a report.
     */
    public function queryAction()
    {
        $report = $this->findById();
        
        if(isset($_GET['search'])) {
            $report->query = serialize($_GET);
            $report->forceSave();
            $this->redirect->goto('index');
        }
        else {
            $queryArray = unserialize($report->query);
            // Some parts of the advanced search check $_GET, others check
            // $_REQUEST, so we set both to be able to edit a previous query.
            $_GET = $queryArray;
            $_REQUEST = $queryArray;
            $this->view->reportsreport = $report;
        }
        
    }
    
    /**
     * Shows details and generated files for a report.
     */
    public function showAction()
    {
        $report = $this->findById();
        
        $reportFiles = $report->getFiles();
        
        $formats = netexport_get_output_formats();
        
        $this->view->formats = $formats;
        
        $this->view->report = $report;
        $this->view->reportFiles = $reportFiles;
    }
    
    /**
     * Spawns a background process to generate a new report file.
     */
    public function generateAction()
    {
        $report = $this->findById();
        
        if (!is_writable(netexport_save_directory())) {
            // Disallow generation if the save directory is not writable
            $this->flash('The directory ' . netexport_save_directory() .
                         ' must be writable by the server for reports to be' .
                         ' generated.',
                         Omeka_Controller_Flash::GENERAL_ERROR);
            return;
        }
        $reportFile = new NetExport_File();
        $reportFile->report_id = $report->id;
        $reportFile->type = $_GET['format'];
        $reportFile->status = NetExport_File::STATUS_STARTING;
    
        // Send the base URL to the background process for QR Code
        // This should be abstracted out to work more generally for
        // all generators.
        if($reportFile->type == 'PdfQrCode') {
            $reportFile->options = serialize(array('baseUrl' => WEB_ROOT));
        }
    
        $reportFile->forceSave();
        $this->_jobDispatcher->setQueueName('reports');
        $this->_jobDispatcher->send('NetExport_GenerateJob',
            array(
                'fileId' => $reportFile->id,
            )
        );

        $this->redirect->gotoRoute(
            array(
                'module' => 'net-export',
                'id'     => $report->id,
                'action' => 'show',
            ),
            'default'
        );
    }
}
