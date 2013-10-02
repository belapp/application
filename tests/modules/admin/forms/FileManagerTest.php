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
 */

/**
 * Unit Test fuer FileManager Formular.
 *
 * @category    Application Unit Test
 * @package     Admin_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_Form_FileManagerTest extends ControllerTestCase {

    public function testConstructForm() {
        $form = new Admin_Form_FileManager();

        $this->assertEquals(3, count($form->getElements()));
        $this->assertNotNull($form->getElement('Id'));
        $this->assertNotNull($form->getElement('Save'));
        $this->assertNotNull($form->getElement('Cancel'));

        $this->assertEquals(3, count($form->getSubForms()));
        $this->assertNotNull($form->getSubForm('Action'));
        $this->assertNotNull($form->getSubForm('Info'));
        $this->assertNotNull($form->getSubForm('Files'));

        $this->assertEquals(3, count($form->getDecorators()));
        $this->assertNotNull($form->getDecorator('FormElements'));
        $this->assertNotNull($form->getDecorator('HtmlTag'));
        $this->assertNotNull($form->getDecorator('Form'));

        $this->assertEquals('FileManager', $form->getName());
    }

    public function testUpdateModel() {
        $this->markTestIncomplete('not tested');
    }

    public function testProcessPost() {
        $this->markTestIncomplete('not tested');
    }

    public function testConstructFromPost() {
        $this->markTestIncomplete('not tested');
    }

    public function testContinueEdit() {
        $this->markTestIncomplete('not tested');
    }

    public function testSetGetMessage() {
        $form = new Admin_Form_FileManager();

        $this->assertNull($form->getMessage());

        $form->setMessage('Test');

        $this->assertEquals('Test', $form->getMessage());
    }

}
