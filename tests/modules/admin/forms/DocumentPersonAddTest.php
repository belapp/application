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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Form_DocumentPersonAddTest extends ControllerTestCase {
 
    public function testConstructForm() {
        $form = new Admin_Form_DocumentPersonAdd();
        
        $this->assertEquals(1, count($form->getSubForms()));
        
        $this->assertNotNull($form->getSubForm('Document'));
        
        // TODO formelements
        // TODO buttons
    }
    
    public function testProcessPostCancel() {
        $form = new Admin_Form_DocumentPersonAdd();
        
        $post = array( 
            'Cancel' => 'Abbrechen'
        );
        
        $this->assertEquals(Admin_Form_DocumentPersonAdd::RESULT_CANCEL, $form->processPost($post, null));
    }
    
    public function testProcessPostNext() {
        $form = new Admin_Form_DocumentPersonAdd();
        
        $post = array( 
            'Next' => 'Weiter'
        );
        
        $this->assertEquals(Admin_Form_DocumentPersonAdd::RESULT_NEXT, $form->processPost($post, null));
    }
    
    public function testSetSelectedRole() {
        $form = new Admin_Form_DocumentPersonAdd();
        
        $form->setSelectedRole('other');
        
        $this->assertEquals('other', $form->getSubForm('Document')->getElement('Role')->getValue());
    }
    
    public function testSetSelectedRoleBadRole() {
        $form = new Admin_Form_DocumentPersonAdd();
        
        $logger = new MockLogger();
        
        $form->setLog($logger);
        $form->setSelectedRole('unknown');
        
        $this->assertEquals('author', $form->getSubForm('Document')->getElement('Role')->getValue());
        
        $messages = $logger->getMessages();
        $this->assertEquals(1, count($messages));
        $this->assertContains('Called with unknown role', $messages[0]);
    }
    
    public function testValidationFalse() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_DocumentPersonAdd();
        
        $post = array(
            'LastName' => '', // darf nicht leer sein
            'Email' => 'beispiel', // muss Email sein ('name@domain')
            'DateOfBirth' => '1970/02/31', // muss gültiges Datum sein
            'Document' => array(
                'Role' => 'unknown', // muss gültige Rolle sein
                'SortOrder' => 'Erster' // muss Integer sein
            ) 
        );
        
        $this->assertFalse($form->isValid($post));
        
        $this->assertContains('isEmpty', $form->getErrors('LastName'));
        $this->assertContains('emailAddressInvalidFormat', $form->getErrors('Email'));
        $this->assertContains('dateInvalidDate', $form->getErrors('DateOfBirth'));
        $this->assertContains('notInArray', $form->getSubForm('Document')->getErrors('Role'));
        $this->assertContains('notInt', $form->getSubForm('Document')->getErrors('SortOrder'));
    }
    
    public function testValidationTrue() {
        $this->setUpEnglish();
        
        $form = new Admin_Form_DocumentPersonAdd();
        
        $post = array(
            'LastName' => 'Meier', // darf nicht leer sein
            'Email' => 'beispiel@example.org', // muss Email sein ('name@domain')
            'DateOfBirth' => '1970/01/31', // muss gültiges Datum sein
            'Document' => array(
                'Role' => 'editor', // muss gültige Rolle sein
                'SortOrder' => '1' // muss Integer sein
            ) 
        );
        
        $this->assertTrue($form->isValid($post));
    }
    
}