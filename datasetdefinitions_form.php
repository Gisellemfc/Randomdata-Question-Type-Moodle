<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Defines the editing form for the randomdata question data set definitions.
 * In this PHP file all the elements for the second page of the plugin are defined.
 * 
 * @package    qtype randomdata
 * @subpackage randomdata
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/question/type/edit_question_form.php');


/**
 * Randomdata question data set definitions editing form definition.
 *
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class question_dataset_dependent_definitions_form extends question_wizard_form {
    /**
     * Question object with options and answers already loaded by get_question_options.
     *
     * @var object
     */
    protected $question;
    /**
     * Reference to question type object.
     *
     * @var question_dataset_dependent_questiontype
     */
    protected $qtypeobj;
    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    
     
    // Construct.
    public function __construct($submiturl, $question) {

        global $DB;
        $this->question = $question;
        $this->qtypeobj = question_bank::get_qtype($this->question->qtype);
        // Validate the question category.
        if (!$category = $DB->get_record('question_categories',
                array('id' => $question->category))) {
            print_error('categorydoesnotexist', 'question', $returnurl);
        }
        $this->category = $category;
        $this->categorycontext = context::instance_by_id($category->contextid);
        parent::__construct($submiturl);

    }


    /**
     * Generates the second page of the form where some characteristics plugin are defined.
     * 
     */
    protected function definition() {
        
        global $SESSION;
        
        $mform = $this->_form;
        $mform->setDisableShortforms();

        $possibledatasets = $this->qtypeobj->find_dataset_names($this->question->questiontext);
        $mandatorydatasets = array();

        if (isset($this->question->options->answers)) {
            foreach ($this->question->options->answers as $answer) {
                $mandatorydatasets += $this->qtypeobj->find_dataset_names($answer->answer);
            }
        } else {
            foreach ($SESSION->randomdata->questionform->answers as $answer) {
                $mandatorydatasets += $this->qtypeobj->find_dataset_names($answer);
            }
        }

        $key = 0;
        $datadefscat= array();
        $datadefscat  = $this->qtypeobj->get_dataset_definitions_category($this->question);
        $datasetmenus = array();
        
        // Space of explaining the role of datasets.
        $label = "<div class='mdl-align'>".get_string('datasetrole', 'qtype_randomdata')."</div>";
        $mform->addElement('html', $label);
        $mform->addElement('header', 'mandatoryhdr',
                get_string('mandatoryhdr', 'qtype_randomdata'));

        foreach ($mandatorydatasets as $datasetname) { 
            if (!isset($datasetmenus[$datasetname])) {
                list($options, $selected) =
                        $this->qtypeobj->dataset_options($this->question, $datasetname);
                unset($options['0']);
                $label = get_string('wildcard', 'qtype_randomdata', $datasetname);
                $mform->addElement('select', "dataset[{$key}]", $label, $options);
                $mform->setDefault("dataset[{$key}]", $selected);
                $datasetmenus[$datasetname] = '';
                $key++;
            }
        }

        // Explanation space for the role of possible variables present only in the text of the question.
        $mform->addElement('header', 'possiblehdr', get_string('possiblehdr', 'qtype_randomdata'));

        foreach ($possibledatasets as $datasetname) {
            if (!isset($datasetmenus[$datasetname])) {
                list($options, $selected) = $this->qtypeobj->dataset_options(
                        $this->question, $datasetname, false);
                $label = get_string('wildcard', 'qtype_randomdata', $datasetname);
                $mform->addElement('select', "dataset[{$key}]", $label, $options);
                $mform->setDefault("dataset[{$key}]", $selected);
                $datasetmenus[$datasetname] = '';
                $key++;
            }
        }

        // Space of the form where it is indicated that the wildcards will not be synchronized.
        $mform->addElement('header', 'synchronizehdr',
                get_string('synchronize', 'qtype_randomdata'));
        $mform->addElement('radio', 'synchronize', '',
                get_string('synchronizeno', 'qtype_randomdata'), 0);
       
        if (isset($this->question->options) &&
                isset($this->question->options->synchronize)) {
            $mform->setDefault('synchronize', $this->question->options->synchronize);
        } else {
            $mform->setDefault('synchronize', 0);
        }

        // Button to the next page of the form.
        $this->add_action_buttons(false, get_string('nextpage', 'qtype_randomdata'));

        $this->add_hidden_fields();
        $mform->addElement('hidden', 'category');
        $mform->setType('category', PARAM_SEQUENCE);
        $mform->addElement('hidden', 'wizard', 'datasetitems');
        $mform->setType('wizard', PARAM_ALPHA);
        
    }


    /**
     * Validation of the second page of the form.
     * 
     */
    public function validation($data, $files) {

        $errors = parent::validation($data, $files);
        $datasets = $data['dataset'];
        $countvalid = 0;

        foreach ($datasets as $key => $dataset) {
            if ($dataset != '0') {
                $countvalid++;
            }
        }

        if (!$countvalid) {
            foreach ($datasets as $key => $dataset) {
                $errors['dataset['.$key.']'] =
                        get_string('atleastonerealdataset', 'qtype_randomdata');
            }
        }

        return $errors;
    }
}