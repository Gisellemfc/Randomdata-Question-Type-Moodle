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
 * Defines the editing form for the randomdata question type.
 * In this PHP file all the elements for the first page of the plugin are defined.
 *
 * @package    qtype randomdata
 * @subpackage randomdata
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/question/type/numerical/edit_numerical_form.php');
require_once($CFG->dirroot . '/question/type/edit_question_form.php');


/**
 * Randomdata question type editing form definition.
 *
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_randomdata_edit_form extends qtype_numerical_edit_form {
    /**
     * Handle to the question type for this question.
     *
     * @var qtype_randomdata
     */
    public $qtypeobj;
    public $questiondisplay;
    public $activecategory;
    public $categorychanged = false;
    public $initialname = '';
    public $reload = false;
    public $validation;

    // Construct.
    public function __construct($submiturl, $question, $category, $contexts, $formeditable = true) {
        global $CFG, $DB;
        $this->question = $question;
        $this->reload = optional_param('reload', false, PARAM_BOOL);

        if (!$this->reload) { // Use database data as this is first pass.
            if (isset($this->question->id)) {
                $this->initialname = $question->name;
                $question->name = question_bank::get_qtype($this->qtype())
                        ->clean_technical_prefix_from_question_name($question->name);
            }
        }
        parent::__construct($submiturl, $question, $category, $contexts, $formeditable);
    }


    /**
     * Get the list of form elements to repeat, one for each answer.
     * 
     * @param object $mform the form being built.
     * @param $label the label to use for each option.
     * @param $gradeoptions the possible grades for each answer.
     * @param $repeatedoptions reference to array of repeated options to fill.
     * @param $answersoption reference to return the name of $question->options field holding an array of answers.
     * @return array of form fields.
     */
    public function get_per_answer_fields($mform, $label, $gradeoptions,&$repeatedoptions, &$answersoption) {

        // Add response options inherited from parent classes.
        $repeated = parent::get_per_answer_fields($mform, $label, $gradeoptions, $repeatedoptions, $answersoption);

        // Reorganise answer options group. 0 is the answer. 1 is tolerance. 2 is Grade. 3 is Feedback.
        $answeroptions = $repeated[0]->getElements();

        // Tolerance field will be part of its own group. Save the tolerance in a separate variable.
        $tolerance = $answeroptions[1];
        // Get feedback field to re append later. Save the feedback in a separate variable.
        $feedback = array_pop($repeated);

        // Save the answer and grade fields in a variable. Update answer options group to contain only answer and grade fields.
        $answeroptions[0]->setSize(55);
        $answeroptions = array($answeroptions[0], $answeroptions[2]);
        $repeated[0]->setElements($answeroptions);

        // Create the labels for the response formula field. Update answer field and group label.
        $repeated[0]->setLabel(get_string('answerformula', 'qtype_randomdata', '{no}') . ' =');
        $answeroptions[0]->setLabel(get_string('answerformula', 'qtype_randomdata', '{no}') . ' =');

        // Create tolerance.
        $answertolerance = array();
        $tolerance->setLabel(get_string('tolerance', 'qtype_randomdata') . '=');
        $answertolerance[] = $tolerance;
        $answertolerance[] = $mform->createElement('select', 'tolerancetype', get_string('tolerancetype', 'qtype_randomdata'), $this->qtypeobj->tolerance_types());
        $repeated[] = $mform->createElement('group', 'answertolerance', get_string('tolerance', 'qtype_randomdata'), $answertolerance, null, false);
        $repeatedoptions['tolerance']['default'] = 0.01;

        // Create the form fields for the configuration of the decimal format.
        $answerdisplay = array();
        $answerdisplay[] = $mform->createElement('select', 'correctanswerlength', get_string('answerdisplay', 'qtype_randomdata'), range(0, 9));
        $repeatedoptions['correctanswerlength']['default'] = 2;
        $answerlengthformats = array(
            '1' => get_string('decimalformat', 'qtype_numerical'),
            '2' => get_string('significantfiguresformat', 'qtype_randomdata')
        );
        $answerdisplay[] = $mform->createElement('select', 'correctanswerformat', get_string('correctanswershowsformat', 'qtype_randomdata'), $answerlengthformats);
        $repeated[] = $mform->createElement('group', 'answerdisplay', get_string('answerdisplay', 'qtype_randomdata'), $answerdisplay, null, false);
                 
        // Add feedback.
        $repeated[] = $feedback;

        if(!isset($this->question->id)){

            $header = get_string('tittleconditions', 'qtype_randomdata');
            $label = "<h3 class='mdl-align'>".$header."</h3>";
            $repeated[] = $mform->createElement('html', $label);
    
            // Create validation original formula: equal to zero.
            $answervalidationzero = array();
            $answervalidationformats = array(
                '1' => get_string('affirmation', 'qtype_randomdata'),
                '2' => get_string('negation', 'qtype_randomdata')
            );
            $answervalidationzero[] = $mform->createElement('select', 'validationzero', get_string('validationzero', 'qtype_randomdata'), $answervalidationformats);
            $repeated[] = $mform->createElement('group', 'answervalidationzero', get_string('validationceroformula', 'qtype_randomdata', '{no}'), $answervalidationzero, null, false);
            
            // Create validation original formula: its positive.
            $answervalidationpositive = array();
            $answervalidationpositive[] = $mform->createElement('select', 'validationpositive', get_string('validationpositive', 'qtype_randomdata'), $answervalidationformats);
            $repeated[] = $mform->createElement('group', 'answervalidationpositive', get_string('validationpositiveformula', 'qtype_randomdata', '{no}'), $answervalidationpositive, null, false);
                    
            // Create validation original formula: its negative.
            $answervalidationnegative = array();
            $answervalidationnegative[] = $mform->createElement('select', 'validationnegative', get_string('validationnegative', 'qtype_randomdata'), $answervalidationformats);
            $repeated[] = $mform->createElement('group', 'answervalidationnegative', get_string('validationnegativeformula', 'qtype_randomdata', '{no}'), $answervalidationnegative, null, false);
       
            // Create validation original formula: min, max.
            $minmaxvalidation = array();
            $minmaxvalidation[] = $mform->createElement('text', "min", get_string('calcmin', 'qtype_randomdata'));
            $minmaxvalidation[] = $mform->createElement('text', "max", get_string('calcmax', 'qtype_randomdata'));
            $repeated[] = $mform->createElement('group', 'minmaxvalidation', get_string('minmaxformula', 'qtype_randomdata', '{no}'), $minmaxvalidation, null, false);
        }

        return $repeated;

    }


    /**
     * Add a set of form fields to generate intermediate validations.
     * 
     * @param object $mform the form being built.
     * @param $label the label to use for each option.
     * @param $minoptions the minimum number of answer blanks to display. Default QUESTION_NUMANS_START.
     * @param $addoptions the number of answer blanks to add. Default QUESTION_NUMANS_ADD.
     */
    public function intermediate_formula_validation($mform, $label, $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        
        // Space to generate validations of intermediate formulas.
        $title = get_string('answervalidationhdr', 'qtype_randomdata');
        $mform->addElement('header', 'answervalidationhdr', $title);
        $mform->setExpanded('answervalidationhdr', 1);
        $answersoption = '';
        $repeatedoptions = array();
        $repeated = $this->generate_intermediate_formula_fields($mform, $label);
        
        if (isset($this->question->options)) {
            $repeatsatstart = count($this->question->options->$answersoption);
        } else {
            $repeatsatstart = $minoptions;
        }

        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,'noanswers', 'addanswersvalid', $addoptions, 
            get_string('addmorechoicevalidblanks', 'qtype_randomdata'), true);
            
    }


    /**
     * Function that obtains the list of validation elements of the intermediate formula of the form.
     * 
     * @param object $mform the form being built.
     * @param $label the label to use for each option.
     * @return array of form fields.
     */
    public function generate_intermediate_formula_fields($mform, $label) {
        
        // Add response options inherited from parent classes.
        $repeated = array();
        $answervalidoptions[] = $mform->createElement('text', 'validationanswer', $label, array('size' => 40));
        $repeated[] = $mform->createElement('group', 'validationansweroptions', $label, $answervalidoptions, null, false);
        $repeatedoptions['validationanswer']['type'] = PARAM_RAW;

        $repeated[0]->setLabel(get_string('answerformula', 'qtype_randomdata', '{no}') . ' =');
        $answervalidoptions[0]->setLabel('');

        // Title of the space for validations.
        $header = get_string('tittleconditions', 'qtype_randomdata');
        $label = "<h3 class='mdl-align'>".$header."</h3>";
        $repeated[] = $mform->createElement('html', $label);

        // Create validation formula: equal to zero.
        $answervalidationzero = array();
        $answervalidationformats = array(
            '1' => get_string('affirmation', 'qtype_randomdata'),
            '2' => get_string('negation', 'qtype_randomdata')
        );
        $answervalidationzero[] = $mform->createElement('select', 'validationformulazero',
        get_string('validationzero', 'qtype_randomdata'), $answervalidationformats);
        $repeated[] = $mform->createElement('group', 'answervalidationformulazero',
        get_string('validationceroformula', 'qtype_randomdata', '{no}'), $answervalidationzero, null, false);
        
        // Create validation formula: its positive.
        $answervalidationpositive = array();
        $answervalidationpositive[] = $mform->createElement('select', 'validationformulapositive', get_string('validationpositive', 'qtype_randomdata'), $answervalidationformats);
        $repeated[] = $mform->createElement('group', 'answervalidationformulapositive', get_string('validationpositiveformula', 'qtype_randomdata', '{no}'), $answervalidationpositive, null, false);
                
        // Create validation formula: its negative.
        $answervalidationnegative = array();
        $answervalidationnegative[] = $mform->createElement('select', 'validationformulanegative', get_string('validationnegative', 'qtype_randomdata'), $answervalidationformats);
        $repeated[] = $mform->createElement('group', 'answervalidationformulanegative', get_string('validationnegativeformula', 'qtype_randomdata', '{no}'), $answervalidationnegative, null, false);
   
        // Create validation  formula: min and max.
        $minmaxvalidation = array();
        $minmaxvalidation[] = $mform->createElement('text', "minformula", get_string('calcmin', 'qtype_randomdata'));
        $minmaxvalidation[] = $mform->createElement('text', "maxformula", get_string('calcmax', 'qtype_randomdata'));
        $repeated[] = $mform->createElement('group', 'minmaxvalidationformulas', get_string('minmaxformula', 'qtype_randomdata', '{no}'), $minmaxvalidation, null, false);
                
        return $repeated;
    }


    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */
    protected function definition_inner($mform) {

        $this->qtypeobj = question_bank::get_qtype($this->qtype());

        $mform->removeElement('idnumber');

        // Initial parameters - hidden.
        $mform->addElement('hidden', 'initialcategory', 1);
        $mform->addElement('hidden', 'reload', 1);
        $mform->setType('initialcategory', PARAM_INT);
        $mform->setType('reload', PARAM_BOOL);

        // Load the question name if you are accessing an already created question.
        if (isset($this->question->id)) {
            $mform->insertElementBefore($mform->createElement('static', 'initialname', get_string('questionstoredname', 'qtype_randomdata'), format_string($this->initialname)), 'name');
        };

        // Editing as regular question.
        $mform->setType('single', PARAM_INT);
        $mform->addElement('hidden', 'shuffleanswers', '1');
        $mform->setType('shuffleanswers', PARAM_INT);
        $mform->addElement('hidden', 'answernumbering', 'abc');
        $mform->setType('answernumbering', PARAM_SAFEDIR);

        if(isset($this->question->id)){
            $coef = '<p>' . get_string('modifanswers', 'qtype_randomdata'). '</p>';
            $mform->addElement('html', $coef);
        }

        // Add the fields for the different response formulas.
        $this->add_per_answer_fields($mform, get_string('answerhdr', 'qtype_randomdata', '{no}'),
                question_bank::fraction_options(), 1, 1);
        $repeated = array();

        if(!isset($this->question->id)){
            $this->intermediate_formula_validation($mform, get_string('answerhdr', 'qtype_randomdata', '{no}'), 1, 1);
            $repeated_val = array();
        }

        // Hidden elements.
        $mform->addElement('hidden', 'synchronize', '');
        $mform->setType('synchronize', PARAM_INT);
        $mform->addElement('hidden', 'wizard', 'datasetdefinitions');
        $mform->setType('wizard', PARAM_ALPHA);

        if(isset($this->question->id)){
            $mform->removeElement('addanswers');
        }
    
    }


    protected function can_preview() {
        return false;
    }


    /**
     * Language string to use for 'Add {no} more {whatever we call answers}'. Change the text of the BTN.
     */
    protected function get_more_choices_string() {
        return get_string('addmorechoiceblanks', 'qtype_randomdata');
    }

    
    /**
     * Function that preprocessing data of the form.
     * 
     * @param object $question of the form.
     * @return array $question.
     */
    public function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_answers($question);
        $question = $this->data_preprocessing_hints($question);
        $question = $this->data_preprocessing_units($question);
        $question = $this->data_preprocessing_unit_options($question);
       
        if (isset($question->options->synchronize)) {
            $question->synchronize = $question->options->synchronize;
        }

        return $question;
    }


    /**
     * Function that preprocessing data answers of the form.
     * 
     * @param object $question of the form.
     * @param $withanswerfiles flag.
     * @return array $question.
     */
    protected function data_preprocessing_answers($question, $withanswerfiles = false) {
        
        $question = parent::data_preprocessing_answers($question, $withanswerfiles);
        
        if (empty($question->options->answers)) {
            return $question;
        }

        $key = 0;
        foreach ($question->options->answers as $answer) {
            unset($this->_form->_defaultValues["tolerancetype[{$key}]"]);
            unset($this->_form->_defaultValues["correctanswerlength[{$key}]"]);
            unset($this->_form->_defaultValues["correctanswerformat[{$key}]"]);

            $question->tolerancetype[$key]       = $answer->tolerancetype;
            $question->correctanswerlength[$key] = $answer->correctanswerlength;
            $question->correctanswerformat[$key] = $answer->correctanswerformat;
            $key++;
        }

        return $question;

    }


    public function qtype() {
        return 'randomdata';
    }


    /**
     * Validate the equations in the some question content.
     * 
     * @param array $errors where errors are being accumulated.
     * @param string $field the field being validated.
     * @param string $text the content of that field.
     * @return array the updated $errors array.
     */
    protected function validate_text($errors, $field, $text) {
        
        $problems = qtype_randomdata_find_formula_errors_in_text($text);
        if ($problems) {
            $errors[$field] = $problems;
        }
        return $errors;
    }


    /**
     * Validate some functions of form.
     * 
     * @param array $data data of form.
     * @param string $field the field being validated.
     * @return array the updated $errors array.
     */
    public function validation($data, $files) {
        
        $errors = question_wizard_form::validation($data, $files);
        $errors = parent::validate_answers($data, $errors);

        // Verifying for errors in {=...} in question text.
        $errors = $this->validate_text($errors, 'questiontext', $data['questiontext']['text']);
        $errors = $this->validate_text($errors, 'generalfeedback', $data['generalfeedback']['text']);

        // Check that the answers use datasets.
        $answers = $data['answer'];
        $mandatorydatasets = array();

        foreach ($answers as $key => $answer) {
            $problems = qtype_randomdata_find_formula_errors($answer);
            if ($problems) {
                $errors['answeroptions['.$key.']'] = $problems;
            }
            $mandatorydatasets += $this->qtypeobj->find_dataset_names($answer);
            $errors = $this->validate_text($errors, 'feedback[' . $key . ']',
                    $data['feedback'][$key]['text']);
        }

        if (empty($mandatorydatasets)) {
            foreach ($answers as $key => $answer) {
                $errors['answeroptions['.$key.']'] = get_string('atleastonewildcard', 'qtype_randomdata');
            }
        }

        // Validate the answer format.
        foreach ($answers as $key => $answer) {
            $trimmedanswer = trim($answer);
            if (trim($answer)) {
                if ($data['correctanswerformat'][$key] == 2 && $data['correctanswerlength'][$key] == '0') {
                    $errors['answerdisplay['.$key.']'] = get_string('zerosignificantfiguresnotallowed', 'qtype_randomdata');
                }
            }
        }

        $formulas_answers = $data['answer'];
        $formulas_answers_valid = $data['validationanswer'];
        $formulas_answers_full = '';

        foreach ($formulas_answers_valid as $key => $answer) {
            $problems = qtype_randomdata_find_formula_errors($answer);
            if ($problems) {
                $errors['validationansweroptions['.$key.']'] = $problems;
            }
        }

        global $DB;

        // Check that the answers in update are not modified.
        if(isset($this->question->id)){

            $answers_db = $DB->get_records('question_answers', array('question' => $this->question->id));
            $flag_equal = false;

            foreach($formulas_answers as $key => $ans){

                    foreach ($answers_db as $validation) {
                        if($validation->answer == trim($ans)){
                            $flag_equal = true;
                        }
                    }

                    if(!$flag_equal){
                        $errors['answeroptions['. $key .']'] = get_string('errormodif', 'qtype_randomdata');
                    }

                    $flag_equal = false;

                }
    
        }
        
         // If no error save question validation of type randomdata.
        if (!(array)$errors) { 

            $this->qtypeobj->save_question_randomdata_validations($this->question->id, $data, false);

        }

        return $errors;
    }


    protected function is_valid_answer($answer, $data) {
        return !qtype_randomdata_find_formula_errors($answer);
    }


    protected function valid_answer_message($answer) {
        if (!$answer) {
            return get_string('mustenteraformulaorstar', 'qtype_numerical');
        } else {
            return qtype_randomdata_find_formula_errors($answer);
        }
    }

}