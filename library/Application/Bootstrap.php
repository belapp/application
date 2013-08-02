<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Application
 * @package     Application
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Simone Finkbeiner (simone.finkbeiner@ub.uni-stuttgart.de)
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Provide methods to setup and run the application. It also provides a couple of static
 * variables for quicker access to application components like the front controller.
 */
class Application_Bootstrap extends Opus_Bootstrap_Base {

    /**
     * Setup a front controller instance with error options and module
     * directory.
     *
     * @return void
     */
    protected function _initOpusFrontController() {
        $this->bootstrap(array('LanguageList', 'frontController'));

        $frontController = $this->getResource('frontController'); // Zend_Controller_Front::getInstance();

        /*
         * Add a custom front controller plugin for setting up an appropriate
         * include path to the form classes of modules.
         */
        $moduleprepare = new Application_Controller_Plugin_LoadTranslation();
        $frontController->registerPlugin($moduleprepare);

        // Add security realm initialization
        $realmSetupPlugin = new Controller_Plugin_SecurityRealm();
        $frontController->registerPlugin($realmSetupPlugin);

        // Add navigation initialization plugin
        $navigationPlugin = new Controller_Plugin_Navigation();
        $frontController->registerPlugin($navigationPlugin);

        // Get Name of Module, Controller and Action for Use in View
        $viewSetup = new Controller_Plugin_ViewSetup();
        $frontController->registerPlugin($viewSetup);
    }
    
    /**
     * Configure view with UTF-8 options and ViewRenderer action helper.
     * The Zend_Layout component also gets initialized here.
     *
     * @return void
     */
    protected function _initView() {
        $this->bootstrap(array('Configuration','OpusFrontController'));

        $config = $this->getResource('Configuration');

        $theme = $config->theme;
        if (empty($theme)) {
            $theme = 'opus4';
        }

        $layoutpath = APPLICATION_PATH . '/public/layouts/' . $theme;
        if (false === is_dir($layoutpath)) {
            throw new Exception('Requested theme "' . $theme . '" not found.');
        }

        Zend_Layout::startMvc(array(
                'layoutPath'=> $layoutpath,
                'layout'=>'common'));

        // Initialize view with custom encoding and global view helpers.
        $view = new Zend_View;
        $view->setEncoding('UTF-8');

        // Set doctype to XHTML1 strict
        $view->doctype('XHTML1_STRICT');

        // Set path to Zend extension view helpers to be accessible in other
        // modules too.
        $libRealPath = realpath(APPLICATION_PATH . '/library');

        $view->addHelperPath($libRealPath . '/View/Helper', 'View_Helper');

        // Set path to shared view partials
        // TODO für nächstes Ticket $view->addScriptPath($libRealPath . '/Application/View/Partial');
        $view->addScriptPath($libRealPath . '/View/Partials');
        
        // Fieldset View Helper global ersetzen
        $fieldsetHelper = new View_Helper_FieldsetWithAnker();
        $view->registerHelper($fieldsetHelper, 'fieldset');
        
        // Breadcrumbs View Helper global ersetzen
        $breadcrumbsHelper = new View_Helper_Breadcrumbs();
        $view->registerHelper($breadcrumbsHelper, 'breadcrumbs');
        
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        // Make View available to unit test (TODO maybe there is a better way?)
        Zend_Registry::set('Opus_View', $view);

        return $view;
    }

    /**
     * Setup Zend_Cache for caching application data and register under 'Zend_Cache_Page'.
     *
     * @return void
     */
    protected function _setupPageCache() {
        $config = $this->getResource('Configuration');

        $pagecache = null;
        $frontendOptions = array(
            'lifetime' => 600, // in seconds
            'debug_header' => false,
            // turning on could slow down caching
            'automatic_serialization' => false,
            'default_options' => array(
                'cache_with_get_variables' => true,
                'cache_with_post_variables' => true,
                'cache_with_session_variables' => true,
                'cache_with_files_variables' => true,
                'cache_with_cookie_variables' => true,
                'make_id_with_get_variables' => true,
                'make_id_with_post_variables' => true,
                'make_id_with_session_variables' => true,
                'make_id_with_files_variables' => true,
                'make_id_with_cookie_variables' => true,
                'cache' => true)
        );

        $backendOptions = array(
            // Directory where to put the cache files. Must be writeable for application server
            'cache_dir' => $config->workspacePath . '/cache/'
            );

        $pagecache = Zend_Cache::factory('Page', 'File', $frontendOptions, $backendOptions);
        Zend_Registry::set('Zend_Cache_Page', $pagecache);
    }

    /**
     * Setup Zend_Translate with language resources of all existent modules.
     *
     * It is assumed that all modules are stored under modules/. The search
     * pattern Zend_Translate gets configured with is to look for a
     * folder and file structure similar to:
     *
     * language/
     *         index.tmx
     *         loginform.tmx
     *         ...
     *
     * @return void
     */
    protected function _initTranslation()  {
        $this->bootstrap(array('Session', 'Logging', 'ZendCache'));
                
        $logger = $this->getResource('Logging');
        $sessiondata = $this->getResource('Session');

        Application_LanguageSupport::getInstance(true)->loadModule('default');

        $sessiondata = new Zend_Session_Namespace();
        if (empty($sessiondata->language)) {
            $language = 'en';
            $logger->debug("language need to be set");
            $supportedLanguages = array();
            $config = $this->getResource('configuration');
            if (isset($config->supportedLanguages)) {
                $supportedLanguages = explode(",", $config->supportedLanguages);
                $logger->debug(count($supportedLanguages) . " supported languages: " . $config->supportedLanguages);
            }
            $currentLocale = new Zend_Locale();
            $currentLanguage = $currentLocale->getLanguage();
            $logger->debug("current locale: " . $currentLocale);
            foreach ($supportedLanguages as $supportedLanguage) {
                if ($currentLanguage === $supportedLanguage) {
                    $language = $currentLanguage;
                    break;
                }
            }
            $sessiondata->language = $language;
        }
        $logger->debug('Set language to "' . $sessiondata->language . '".');

        $translate = Zend_Registry::get('Zend_Translate');
        $translate->setLocale($sessiondata->language);

        $this->translate = $translate;

        return $translate;
    }

    /**
     * Setup session.
     *
     * @return Zend_Session_Namespace
     */
    protected function _initSession() {
        $this->bootstrap(array('Database'));
        return new Zend_Session_Namespace();
    }

    /**
     * Setup language list.
     *
     * @return void
     */
    protected function _initLanguageList() {
        $this->bootstrap(array('Translation', 'Backend'));
        $translate = $this->getResource('Translation');

        $languages = array();
        foreach (Opus_Language::getAllActiveTable() as $languageRow) {
            $ref_name = $languageRow['ref_name'];
            $part2_t = $languageRow['part2_t'];
            $languages[$part2_t] = $translate->translate($part2_t);
        }
        Zend_Registry::set('Available_Languages', $languages);
    }

    /**
     * Initializes general navigation as configured in navigationModules.xml'
     *
     * @return void
     */
    protected function _initNavigation() {
        $this->bootstrap('Logging', 'View');

        $log = $this->getResource('Logging');
        $log->debug('Initializing Zend_Navigation');

        $navigationConfigFile = APPLICATION_PATH . '/application/configs/navigationModules.xml';
        $navConfig = new Zend_Config_Xml($navigationConfigFile, 'nav');

        $log->debug('Navigation config file is: ' . $navigationConfigFile);

        $container = null;
        try {
            $container = new Zend_Navigation($navConfig);
        }
        catch (Zend_Navigation_Exception $e) {
            /* TODO This suppresses the "Mystery Bug" that is producing errors
             * in unit tests sometimes. So far we haven't figured out the real
             * reason behind the errors. In regular Opus instances the error
             * has not appeared (as far as we know).
             */
            $log->err($e);
        }

        $view = $this->getResource('View');
        $view->navigation($container);
        
        $log->debug('Zend_Navigation initialization completed');

        return $container;
    }
    
    /**
     * Initialisiert Zend_Acl für die Authorization in OPUS.
     * 
     * TODO use Application_Security_AclProvider
     */
    protected function _initAuthz() {
        $this->bootstrap('Logging', 'Navigation', 'view');
        
        $config = $this->getResource('configuration');
        $log = $this->getResource('Logging');
        
        if (isset($config->security) && $config->security == 1) {
            $aclProvider = new Application_Security_AclProvider();

            $acl = $aclProvider->getAcls();
            
            $log->debug('ACL: bootrapping');
            Zend_Registry::set('Opus_Acl', $acl);
            
            Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl($acl);
            Zend_View_Helper_Navigation_HelperAbstract::setDefaultRole(
                    Application_Security_AclProvider::ACTIVE_ROLE);   
        }
    }
    
    /**
     * Initializes navigation container for main menu.
     * @return Zend_Navigation
     */
    protected function _initMainMenu() {
        $this->bootstrap('Logging', 'View', 'Navigation');

        $config = $this->getResource('configuration');

        $navigationConfigFile = APPLICATION_PATH . '/application/configs/navigation.xml';

        $navConfig = new Zend_Config_Xml($navigationConfigFile, 'nav');

        $container = new Zend_Navigation($navConfig);

        $view = $this->getResource('View');

        $view->navigationMainMenu = $container;

        // TODO Find better way without Zend_Registry
        Zend_Registry::set('Opus_Navigation', $container);

        // return $container;
    }

}