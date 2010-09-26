<?php
/*
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
 * @category    TODO
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Basic unit tests for Admin_PersonController class.
 */
class Amdin_PersonController extends ControllerTestCase {

    /**
     * Test routing to and successfull execution of index action.
     */
    public function testIndexAction() {
        $this->_setPersonDisabled(false);
        $this->dispatch('/admin/person');
        $this->assertResponseCode(200);
        $this->assertController('person');
        $this->assertAction('index');
    }

    /**
     * Test index action in case the person administration is disabled.
     */
    public function testIndexActionIfDisabled() {
        $this->_setPersonDisabled(true);
        $this->dispatch('/admin/person');
        $this->assertRedirect();
        $this->markTestIncomplete("Validate redirect URL.");
    }

    /**
     * Disable person administration in admin module.
     * @param boolean $enabled
     */
    protected function _setPersonDisabled($enabled) {
        $config = Zend_Registry::get('Zend_Config');
        $config->admin->persons->disabled = $enabled;
    }

    public function testShowAction() {
        $this->dispatch('/admin/person/show/id/1');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('person');
        $this->assertAction('show');
    }

}

?>
