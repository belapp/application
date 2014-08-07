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
 * @package     Module_Admin
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for administration of collection roles.
 *
 * TODO auf Application_Controller_ActionCRUD umstellen (IndexTabelle und neue Actions berücksichtigen)
 */
class Admin_CollectionrolesController extends Controller_Action {

    /**
     * List all available collection role instances.
     */
    public function indexAction() {
        $this->view->collectionRoles = Opus_CollectionRole::fetchAll();
    }

    /**
     * Zeigt Formular für Erzeugung einer neuen CollectionRole an.
     */
    public function newAction() {
        Opus_CollectionRole::fixPositions();
        $collectionRoleModel = new Admin_Model_CollectionRole();
        $this->view->form = $this->getRoleForm($collectionRoleModel->getObject());
    }



    /**
     * Zeigt Formular für das Editieren einer CollectionRole.
     */
    public function editAction() {
        Opus_CollectionRole::fixPositions();
        try {
            $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
            $this->view->form = $this->getRoleForm($collectionRoleModel->getObject());
            $this->setCollectionBreadcrumb('default_collection_role_' . $collectionRoleModel->getObject()->getName());
        }
        catch (Application_Exception $e) {
            return $this->_redirectToAndExit('index', array('failure' => $e->getMessage()));
        }
    }

    /**
     * Verschiebt eine CollectionRole einen Schritt nach oben oder unten.
     */
    public function moveAction() {
        try {
            $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
            $collectionRoleModel->move($this->getRequest()->getParam('pos'));
            return $this->_redirectTo('index', $this->view->translate('admin_collectionroles_move',
                $collectionRoleModel->getObject()->getName()));
        }
        catch (Application_Exception $e) {
            return $this->_redirectToAndExit('index', array('failure' => $e->getMessage()));
        }
    }

    /**
     * Ändert die Sichtbarkeit einer CollectionRole.
     * @param $visibility boolean
     */
    private function changeRoleVisibility($visibility) {
        try {
            $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
            $collectionRoleModel->setVisibility($visibility);
            return $this->_redirectTo('index', $this->view->translate('admin_collectionroles_changevisibility',
                $collectionRoleModel->getObject()->getName()));
        }
        catch (Application_Exception $e) {
            return $this->_redirectToAndExit('index', array('failure' => $e->getMessage()));
        }
    }

    /**
     * Sets Breadcrumbs for a CollectionRole.
     * @param $name
     */
    public function setCollectionBreadcrumb($name) {
        $page = $this->view->navigation()->findOneBy('label', 'admin_collection_index');
        if (!is_null($page)) {
            $page->setLabel($name);
        }
    }

    /**
     * Setzt eine CollectionRole unsichtbar.
     */
    public function hideAction() {
        $this->changeRoleVisibility(false);
    }

    /**
     * Setzt eine CollectionRole sichtbar.
     */
    public function unhideAction() {
        $this->changeRoleVisibility(true);
    }

    /**
     * Erzeugt eine neue CollectionRole bzw. speichert eine geänderte ab.
     */
    public function createAction() {
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectToAndExit('index');
        }

        $data = $this->getRequest()->getPost();

        $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('oid'));
        $collectionRole = $collectionRoleModel->getObject();

        $form = new Admin_Form_CollectionRole();
        $form->populate($data);

        if (!$form->isValid($data)) {
            $this->view->form = $this->initCreateRoleForm($form, $collectionRole);
            $this->setTitle($collectionRole);
            return;
        }

        $form->updateModel($collectionRole);

        if (true === $collectionRole->isNewRecord()) {
            $messageKey = 'admin_collectionroles_add';

            if (true === is_null($collectionRole->getRootCollection())) {
                $collectionRole->addRootCollection();
                $collectionRole->getRootCollection()->setVisible('1');
            }
        }
        else {
            $messageKey = 'admin_collectionroles_edit_notice';
        }

        $collectionRole->store();

        return $this->_redirectTo('index', $this->view->translate($messageKey, $collectionRole->getName()));
    }

    /**
     * Setzt die Überschrift der Seite, abhängig vom Status der CollectionRole.
     * @param $collectionRole
     */
    private function setTitle($collectionRole) {
        if ($collectionRole->isNewRecord()) {
            $this->view->title = 'admin_collectionroles_new';
        }
        $this->view->title = 'admin_collectionroles_edit';
    }

    /**
     * Erzeugt Formular für ein CollectionRole Objekt.
     *
     * @param Opus_CollectionRole $collectionRole
     * @return mixed
     */
    private function getRoleForm(Opus_CollectionRole $collectionRole) {
        $form = new Admin_Form_CollectionRole();
        $form->populateFromModel($collectionRole);

        $this->initCreateRoleForm($form, $collectionRole);

        return $form;
    }

    /**
     * Setzt Formularaction.
     * @param $form
     * @param $collectionRole
     * @return Admin_Form_CollectionRole
     */
    private function initCreateRoleForm($form, $collectionRole) {
        if ($collectionRole->isNewRecord()) {
            $form->setAction($this->view->url(array('action' => 'create')));
        }
        else {
            $form->setAction($this->view->url(array('action' => 'create', 'oid' => $collectionRole->getId())));
        }
        return $form;
    }

    /**
     * Löscht eine CollectionRole.
     */
    public function deleteAction() {
        try {
            $collectionRoleModel = new Admin_Model_CollectionRole($this->getRequest()->getParam('roleid', ''));
            $collectionRoleModel->delete();
            $collectionRoleModel->getObject()->getDisplayName();
            $message = $this->view->translate('admin_collectionroles_delete',
                $collectionRoleModel->getObject()->getName());
            return $this->_redirectTo('index', $message);
        }
        catch (Application_Exception $e) {
            return $this->_redirectToAndExit('index', array('failure' => $e->getMessage()));
        }
    }

}
