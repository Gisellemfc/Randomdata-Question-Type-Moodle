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
 * Question type class for the randomdata question type.
 *
 * @package    qtype randomdata
 * @subpackage randomdata
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/question/type/questiontypebase.php');
require_once($CFG->dirroot . '/question/type/questionbase.php');
require_once($CFG->dirroot . '/question/type/numerical/question.php');


/**
 * The randomdata question type.
 *
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_randomdata extends question_type {
    /**
     * @const string a placeholder is a letter, followed by almost any characters. (This should probably be restricted more.)
     */
    const PLACEHOLDER_REGEX_PART = '[[:alpha:]][^>} <`{"\']*';

    /**
     * @const string REGEXP for a placeholder, wrapped in its {...} delimiters, with capturing brackets around the name.
     */
    const PLACEHODLER_REGEX = '~\{(' . self::PLACEHOLDER_REGEX_PART . ')\}~';

    /**
     * @const string Regular expression that finds the formulas in content, with capturing brackets to get the forumlas.
     */
    const FORMULAS_IN_TEXT_REGEX = '~\{=([^{}]*(?:\{' . self::PLACEHOLDER_REGEX_PART . '\}[^{}]*)*)\}~';

    const MAX_DATASET_ITEMS = 100;

    public $wizardpagesnumber = 3;

    public $id_q = 9999999;


    /**
    * Function to get question options.
    * @param $question of the form.
    * @return true.
    */
    public function get_question_options($question) {

        // First get the datasets and default options.
        global $CFG, $DB, $OUTPUT;
        parent::get_question_options($question);

        if (!$question->options = $DB->get_record('question_randomdata_options', array('question' => $question->id))) {
            $question->options = new stdClass();
            $question->options->synchronize = 0;
            $question->options->single = 0;
            $question->options->answernumbering = 'abc';
            $question->options->shuffleanswers = 0;
            $question->options->correctfeedback = '';
            $question->options->partiallycorrectfeedback = '';
            $question->options->incorrectfeedback = '';
            $question->options->correctfeedbackformat = 0;
            $question->options->partiallycorrectfeedbackformat = 0;
            $question->options->incorrectfeedbackformat = 0;
        }

        if (!$question->options->answers = $DB->get_records_sql("
            SELECT a.*, c.tolerance, c.tolerancetype, c.correctanswerlength, c.correctanswerformat
            FROM {question_answers} a,
                 {question_randomdata} c
            WHERE a.question = ?
            AND   a.id = c.answer
            ORDER BY a.id ASC", array($question->id))) {
                return false;
        }

        if ($this->get_virtual_qtype()->name() == 'numerical') {
            $this->get_virtual_qtype()->get_numerical_units($question);
            $this->get_virtual_qtype()->get_numerical_options($question);
        }

        $question->hints = $DB->get_records('question_hints',array('questionid' => $question->id), 'id ASC');

        if (isset($question->export_process)&&$question->export_process) {
            $question->options->datasets = $this->get_datasets_for_export($question);
        }
        return true;

    }

    
    /**
    * Function to get datasets for export.
    * @param $question of the form.
    * @return array $datasetdefs of the question.
    */
    public function get_datasets_for_export($question) {

        global $DB, $CFG;
        $datasetdefs = array();

        if (!empty($question->id)) {
            $sql = "SELECT i.*
                      FROM {question_datasets} d, {question_dataset_definitions} i
                     WHERE d.question = ? AND d.datasetdefinition = i.id";

            if ($records = $DB->get_records_sql($sql, array($question->id))) {

                foreach ($records as $r) {

                    $def = $r;
                    if ($def->category == '0') {
                        $def->status = 'private';
                    } else {
                        $def->status = 'shared';
                    }
                    
                    $def->type = 'randomdata';
                    list($distribution, $min, $max, $dec) = explode(':', $def->options, 4);
                    $def->distribution = $distribution;
                    $def->minimum = $min;
                    $def->maximum = $max;
                    $def->decimals = $dec;

                    if ($def->itemcount > 0) {
                        // Get the datasetitems.
                        $def->items = array();
                        if ($items = $this->get_database_dataset_items($def->id)) {
                            $n = 0;
                            foreach ($items as $ii) {
                                $n++;
                                $def->items[$n] = new stdClass();
                                $def->items[$n]->itemnumber = $ii->itemnumber;
                                $def->items[$n]->value = $ii->value;
                            }
                            $def->number_of_items = $n;
                        }
                    }
                    $datasetdefs["1-{$r->category}-{$r->name}"] = $def;

                }
            }
        }

        return $datasetdefs;

    }



    /**
    * Function to save question option.
    * @param $question of the form.
    * @return true.
    */
    public function save_question_options($question) {

        global $CFG, $DB;

        // Make it impossible to save bad formulas anywhere.
        $this->validate_question_data($question);

        $context = $question->context;

        // Randomdata options.
        $update = true;
        $options = $DB->get_record('question_randomdata_options', array('question' => $question->id));
        
        if (!$options) {
            $update = false;
            $options = new stdClass();
            $options->question = $question->id;
        }

        // As used only by Randomdata.
        if (isset($question->synchronize)) {
            $options->synchronize = $question->synchronize;
        } else {
            $options->synchronize = 0;
        }

        $options->single = 0;
        $options->answernumbering =  $question->answernumbering;
        $options->shuffleanswers = $question->shuffleanswers;

        foreach (array('correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback') as $feedbackname) {
            $options->$feedbackname = '';
            $feedbackformat = $feedbackname . 'format';
            $options->$feedbackformat = 0;
        }

        if ($update) {
            $DB->update_record('question_randomdata_options', $options);
        } else {
            $DB->insert_record('question_randomdata_options', $options);
        }

        // Get old versions of the objects.
        $oldanswers = $DB->get_records('question_answers', array('question' => $question->id), 'id ASC');
        $oldoptions = $DB->get_records('question_randomdata', array('question' => $question->id), 'answer ASC');

        // Save the units.
        $virtualqtype = $this->get_virtual_qtype();
        $result = $virtualqtype->save_units($question);

        if (isset($result->error)) {
            return $result;
        } else {
            $units = $result->units;
        }

        foreach ($question->answer as $key => $answerdata) {
            if (trim($answerdata) == '') {
                continue;
            }

            // Update an existing answer if possible.
            $answer = array_shift($oldanswers);
            if (!$answer) {
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = '';
                $answer->feedback = '';
                $answer->id = $DB->insert_record('question_answers', $answer);
            }

            $answer->answer   = trim($answerdata);
            $answer->fraction = $question->fraction[$key];
            $answer->feedback = $this->import_or_save_files($question->feedback[$key], $context, 'question', 'answerfeedback', $answer->id);
            $answer->feedbackformat = $question->feedback[$key]['format'];

            $DB->update_record("question_answers", $answer);

            // Set up the options object.
            if (!$options = array_shift($oldoptions)) {
                $options = new stdClass();
            }
            $options->question = $question->id;
            $options->answer = $answer->id;
            $options->tolerance = trim($question->tolerance[$key]);
            $options->tolerancetype = trim($question->tolerancetype[$key]);
            $options->correctanswerlength = trim($question->correctanswerlength[$key]);
            $options->correctanswerformat = trim($question->correctanswerformat[$key]);

            // Save options.
            if (isset($options->id)) {
                // Reusing existing record.
                $DB->update_record('question_randomdata', $options);
            } else {
                // New options.
                $DB->insert_record('question_randomdata', $options);
            }

        }

        // Delete old answer records.
        if (!empty($oldanswers)) {
            foreach ($oldanswers as $oa) {
                $DB->delete_records('question_answers', array('id' => $oa->id));
            }
        }

        // Delete old answer records.
        if (!empty($oldoptions)) {
            foreach ($oldoptions as $oo) {
                $DB->delete_records('question_randomdata', array('id' => $oo->id));
            }
        }

        $result = $virtualqtype->save_unit_options($question);
        if (isset($result->error)) {
            return $result;
        }

        $this->save_hints($question);

        if (isset($question->import_process)&&$question->import_process) {
            $this->import_datasets($question);
        }
        // Report any problems.
        if (!empty($result->notice)) {
            return $result;
        }
        return true;

    }


    /**
    * Function to import datasets of the question.
    * @param $question of the form.
    */
    public function import_datasets($question) {

        global $DB;
        $n = count($question->dataset);

        foreach ($question->dataset as $dataset) {
            // Name, type, option.
            $datasetdef = new stdClass();
            $datasetdef->name = $dataset->name;
            $datasetdef->type = 1;
            $datasetdef->options =  $dataset->distribution . ':' . $dataset->min . ':' . $dataset->max . ':' . $dataset->length;
            $datasetdef->itemcount = $dataset->itemcount;
           
            if ($dataset->status == 'private') {
                $datasetdef->category = 0;
                $todo = 'create';
            } else if ($dataset->status == 'shared') {
                if ($sharedatasetdefs = $DB->get_records_select(
                    'question_dataset_definitions',
                    "type = '1'
                    AND " . $DB->sql_equal('name', '?') . "
                    AND category = ?
                    ORDER BY id DESC ", array($dataset->name, $question->category)
                )) { // So there is at least one.
                    $sharedatasetdef = array_shift($sharedatasetdefs);
                    if ($sharedatasetdef->options ==  $datasetdef->options) {// Identical so use it.
                        $todo = 'useit';
                        $datasetdef = $sharedatasetdef;
                    } else { // Different so create a private one.
                        $datasetdef->category = 0;
                        $todo = 'create';
                    }
                } else { // No so create one.
                    $datasetdef->category = $question->category;
                    $todo = 'create';
                }
            }

            if ($todo == 'create') {
                $datasetdef->id = $DB->insert_record('question_dataset_definitions', $datasetdef);
            }

            // Create relation to the dataset.
            $questiondataset = new stdClass();
            $questiondataset->question = $question->id;
            $questiondataset->datasetdefinition = $datasetdef->id;
            $DB->insert_record('question_datasets', $questiondataset);

            if ($todo == 'create') {
                // Add the items.
                foreach ($dataset->datasetitem as $dataitem) {
                    $datasetitem = new stdClass();
                    $datasetitem->definition = $datasetdef->id;
                    $datasetitem->itemnumber = $dataitem->itemnumber;
                    $datasetitem->value = $dataitem->value;
                    $DB->insert_record('question_dataset_items', $datasetitem);
                }
            }
        }

    }


    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);

        question_bank::get_qtype('numerical')->initialise_numerical_answers(
                $question, $questiondata);
        foreach ($questiondata->options->answers as $a) {
            $question->answers[$a->id]->tolerancetype = $a->tolerancetype;
            $question->answers[$a->id]->correctanswerlength = $a->correctanswerlength;
            $question->answers[$a->id]->correctanswerformat = $a->correctanswerformat;
        }

        $question->synchronised = $questiondata->options->synchronize;
        $question->unitdisplay = $questiondata->options->showunits;
        $question->unitgradingtype = $questiondata->options->unitgradingtype;
        $question->unitpenalty = $questiondata->options->unitpenalty;
        $question->ap = question_bank::get_qtype('numerical')->make_answer_processor($questiondata->options->units, $questiondata->options->unitsleft);
        $question->datasetloader = new qtype_randomdata_dataset_loader($questiondata->id);
    
    }


    public function finished_edit_wizard($form) {
        return isset($form->savechanges);
    }


    public function wizardpagesnumber() {
        return 3;
    }

    
    /**
    * This gets called by editquestion.php after the standard question is saved.
    */
    public function print_next_wizard_page($question, $form, $course) {
        global $CFG, $SESSION, $COURSE;

        // Catch invalid navigation & reloads.
        if (empty($question->id) && empty($SESSION->randomdata)) {
            redirect('edit.php?courseid='.$COURSE->id, 'The page you are loading has expired.', 3);
        }

        // See where we're coming from.
        switch($form->wizardpage) {
            case 'question':
                require("{$CFG->dirroot}/question/type/randomdata/datasetdefinitions.php");
                break;
            case 'datasetdefinitions':
            case 'datasetitems':
                require("{$CFG->dirroot}/question/type/randomdata/datasetitems.php");
                break;
            default:
                print_error('invalidwizardpage', 'question');
                break;
        }
    }


    /**
    * This gets called by question2.php after the standard question is saved.
    */
    public function &next_wizard_form($submiturl, $question, $wizardnow) {
        
        global $CFG, $SESSION, $COURSE;

        // Catch invalid navigation & reloads.
        if (empty($question->id) && empty($SESSION->randomdata)) {
            redirect('edit.php?courseid=' . $COURSE->id,'The page you are loading has expired. Cannot get next wizard form.', 3);
        }
        if (empty($question->id)) {
            $question = $SESSION->randomdata->questionform;
        }

        // See where we're coming from.
        switch($wizardnow) {
            case 'datasetdefinitions':
                require("{$CFG->dirroot}/question/type/randomdata/datasetdefinitions_form.php");
                $mform = new question_dataset_dependent_definitions_form("{$submiturl}?wizardnow=datasetdefinitions", $question);
                break;
            case 'datasetitems':
                require("{$CFG->dirroot}/question/type/randomdata/datasetitems_form.php");
                $regenerate = optional_param('forceregeneration', false, PARAM_BOOL);
                $mform = new question_dataset_dependent_items_form("{$submiturl}?wizardnow=datasetitems", $question, $regenerate);
                break;
            default:
                print_error('invalidwizardpage', 'question');
                break;
        }

        return $mform;

    }


    /**
     * Function to display question editing page.
     *
     * @param question_edit_form $mform a child of question_edit_form.
     * @param object $question.
     * @param string $wizardnow is '' for first page.
     */
    public function display_question_editing_page($mform, $question, $wizardnow) {
        
        global $OUTPUT;

        switch ($wizardnow) {
            case '':
                // On the first page.
                parent::display_question_editing_page($mform, $question, $wizardnow);
                return;
            case 'datasetdefinitions':
                echo $OUTPUT->heading_with_help(get_string('choosedatasetproperties', 'qtype_randomdata'), 'questiondatasets', 'qtype_randomdata');
                break;
            case 'datasetitems':
                echo $OUTPUT->heading_with_help(get_string('editdatasets', 'qtype_randomdata'),'questiondatasets', 'qtype_randomdata');
                break;
        }

        $mform->display();

    }


    /**
     * Verify that the equations in part of the question are OK.
     * 
     * @param string $text containing equations.
     */
    protected function validate_text($text) {
        $error = qtype_randomdata_find_formula_errors_in_text($text);
        if ($error) {
            throw new coding_exception($error);
        }

    }


    /**
     * Verify that an answer is OK.
     * 
     * @param string $text containing equations.
     */
    protected function validate_answer($answer) {
        $error = qtype_randomdata_find_formula_errors($answer);
        if ($error) {
            throw new coding_exception($error);
        }

    }


    /**
     * Validate data before save.
     * @param stdClass $question data from the form / import file.
     */
    protected function validate_question_data($question) {
        
        $this->validate_text($question->questiontext); // Yes, really no ['text'].

        if (isset($question->generalfeedback['text'])) {
            $this->validate_text($question->generalfeedback['text']);
        } else if (isset($question->generalfeedback)) {
            $this->validate_text($question->generalfeedback); // Because question import is weird.
        }

        foreach ($question->answer as $key => $answer) {
            $this->validate_answer($answer);
            $this->validate_text($question->feedback[$key]['text']);
        }
    }


    /**
     * Function to remove prefix #{..}# if exists.
     * @param $name a question name,
     * @return string the cleaned up question name.
     */
    public function clean_technical_prefix_from_question_name($name) {
        return preg_replace('~#\{([^[:space:]]*)#~', '', $name);
    }


    /**
     * This method prepare the $datasets in a format similar to dadatesetdefinitions_form.php so that they can be saved
     * using the function save_dataset_definitions($form)
     * when creating a new randomdata question or
     * when editing an already existing randomdata question
     * or by  function save_as_new_dataset_definitions($form, $initialid)
     * when saving as new an already existing randomdata question.
     *
     * @param object $form.
     * @param int $questionfromid default = '0'.
     */
    public function preparedatasets($form, $questionfromid = '0') {

        // The dataset names present in the edit_question_form and edit_randomdata_form are retrieved.
        $possibledatasets = $this->find_dataset_names($form->questiontext);
        $mandatorydatasets = array();

        foreach ($form->answer as $key => $answer) {
            $mandatorydatasets += $this->find_dataset_names($answer);
        }

        // If there are identical datasetdefs already saved in the original question, either when editing a question or saving as new,
        // they are retrieved using $questionfromid.
        if ($questionfromid != '0') {
            $form->id = $questionfromid;
        }
        $datasets = array();
        $key = 0;

        // Always prepare the mandatorydatasets present in the answers.
        // The $options are not used here.
        foreach ($mandatorydatasets as $datasetname) {

            if (!isset($datasets[$datasetname])) {
                list($options, $selected) = $this->dataset_options($form, $datasetname);
                $datasets[$datasetname] = '';
                $form->dataset[$key] = $selected;
                $key++;

            }
        }

        // Do not prepare possibledatasets when creating a question.
        // They will defined and stored with datasetdefinitions_form.php.
        if ($questionfromid != '0') {

            foreach ($possibledatasets as $datasetname) {
                if (!isset($datasets[$datasetname])) {
                    list($options, $selected) = $this->dataset_options($form, $datasetname, false);
                    $datasets[$datasetname] = '';
                    $form->dataset[$key] = $selected;
                    $key++;
                }
            }
        }

        return $datasets;

    }


    /**
     * Function to add name of category.
     *
     * @param $question.
     */
    public function addnamecategory(&$question) {

        global $DB;

        $categorydatasetdefs = $DB->get_records_sql(
            "SELECT  a.*
               FROM {question_datasets} b, {question_dataset_definitions} a
              WHERE a.id = b.datasetdefinition
                AND a.type = '1'
                AND a.category != 0
                AND b.question = ?
           ORDER BY a.name ", array($question->id));
        $questionname = $this->clean_technical_prefix_from_question_name($question->name);

        if (!empty($categorydatasetdefs)) {
            // There is at least one with the same name.
            $questionname = '#' . $questionname;
            foreach ($categorydatasetdefs as $def) {
                if (strlen($def->name) + strlen($questionname) < 250) {
                    $questionname = '{' . $def->name . '}' . $questionname;
                }
            }
            $questionname = '#' . $questionname;
        }
        $DB->set_field('question', 'name', $questionname, array('id' => $question->id));
    }


    /**
     * This save the available data at the different steps of the question editing process
     * without using global $SESSION as storage between steps
     * at the first step $wizardnow = 'question'
     * when creating a new question, when modifying a question, when copying as a new question
     * the general parameters and answers are saved using parent::save_question
     * then the datasets are prepared and saved
     * at the second step $wizardnow = 'datasetdefinitions'
     * the datadefs final type are defined as private, category or not a datadef
     * at the third step $wizardnow = 'datasetitems'
     * the datadefs parameters and the data items are created or defined.
     *
     * @param object question.
     * @param object $form.
     * @return array $question.
     * 
     */
    public function save_question($question, $form) {
        global $DB;

        if ($this->wizardpagesnumber() == 1 || $question->qtype == 'calculatedsimple') {
            $question = parent::save_question($question, $form);
            return $question;
        }

        $wizardnow =  optional_param('wizardnow', '', PARAM_ALPHA);
        $id = optional_param('id', 0, PARAM_INT); // Question id.

        // In case 'question':for a new question $form->id is empty when saving as new question.
        // The $question->id = 0, $form is $data, and $data->makecopy is defined as $data->id is the initial question id.
        // Edit case. If it is a new question we don't necessarily need to return a valid question object.

        switch($wizardnow) {
            case '' :
            case 'question': // Coming from the first page, creating the second.
                if (empty($form->id)) { // or a new question $form->id is empty.
                    $question = parent::save_question($question, $form);
                    // Prepare the datasets using default $questionfromid.
                    $this->preparedatasets($form);
                    $form->id = $question->id;
                    $this->save_dataset_definitions($form);
                    if (isset($form->synchronize) && $form->synchronize == 2) {
                        $this->addnamecategory($question);
                    }
                } else if (!empty($form->makecopy)) {
                    $questionfromid =  $form->id;
                    $question = parent::save_question($question, $form);
                    // Prepare the datasets.
                    $this->preparedatasets($form, $questionfromid);
                    $form->id = $question->id;
                    $this->save_as_new_dataset_definitions($form, $questionfromid);
                    if (isset($form->synchronize) && $form->synchronize == 2) {
                        $this->addnamecategory($question);
                    }
                } else {
                    // Editing a question.
                    $question = parent::save_question($question, $form);
                    // Prepare the datasets.
                    $this->preparedatasets($form, $question->id);
                    $form->id = $question->id;
                    $this->save_dataset_definitions($form);
                    if (isset($form->synchronize) && $form->synchronize == 2) {
                        $this->addnamecategory($question);
                    }
                }
                break;
                case 'datasetdefinitions':
                    // Randomdata options.
                    // It cannot go here without having done the first page,
                    // so the question_randomdata_options should exist.
                    // We only need to update the synchronize field.
                    if (isset($form->synchronize)) {
                        $optionssynchronize = $form->synchronize;
                    } else {
                        $optionssynchronize = 0;
                    }

                    $DB->set_field('question_randomdata_options', 'synchronize', $optionssynchronize,array('question' => $question->id));
                    
                    if (isset($form->synchronize) && $form->synchronize == 2) {
                        $this->addnamecategory($question);
                    }
                    
                    $this->save_dataset_definitions($form);
                    $this->save_question_randomdata_validations($form->id, $this->data, true);
                break;
            case 'datasetitems':
                $this->save_dataset_items($question, $form);
                $this->save_question_randomdata($question, $form);
                break;
            default:
                print_error('invalidwizardpage', 'question');
                break;
        }
        return $question;

    }


    /**
     * Function that delete question.
     *
     * @param object questionid.
     * @param object $contextid.
     */
    public function delete_question($questionid, $contextid) {
       
        global $DB;

        $DB->delete_records('question_randomdata', array('question' => $questionid));
        $DB->delete_records('question_randomdata_options', array('question' => $questionid));
        $DB->delete_records('question_numerical_units', array('question' => $questionid));
        $DB->delete_records('question_randomdata_results', array('question' => $questionid));
        $DB->delete_records('question_randomdata_valid', array('question' => $questionid));
        
        if ($datasets = $DB->get_records('question_datasets', array('question' => $questionid))) {
            foreach ($datasets as $dataset) {
                if (!$DB->get_records_select('question_datasets',"question != ? AND datasetdefinition = ? ", array($questionid, $dataset->datasetdefinition))) {
                    $DB->delete_records('question_dataset_definitions', array('id' => $dataset->datasetdefinition));
                    $DB->delete_records('question_dataset_items', array('definition' => $dataset->datasetdefinition));
                }
            }
        }

        $DB->delete_records('question_datasets', array('question' => $questionid));
        parent::delete_question($questionid, $contextid);

    }


    public function get_random_guess_score($questiondata) {
        foreach ($questiondata->options->answers as $aid => $answer) {
            if ('*' == trim($answer->answer)) {
                return max($answer->fraction - $questiondata->options->unitpenalty, 0);
            }
        }
        return 0;
    }


    public function supports_dataset_item_generation() {
        // Randomdata support generation of randomly distributed number data.
        return true;
    }


    public function custom_generator_tools_part($mform, $idx, $j) {

        $minmaxgrp = array();
        $minmaxgrp[] = $mform->createElement('float', "calcmin[{$idx}]", get_string('calcmin', 'qtype_randomdata'));
        $minmaxgrp[] = $mform->createElement('float', "calcmax[{$idx}]", get_string('calcmax', 'qtype_randomdata'));
        $mform->addGroup($minmaxgrp, 'minmaxgrp', get_string('minmax', 'qtype_randomdata'), ' - ', false);
        $precisionoptions = range(0, 10);
        $mform->addElement('select', "calclength[{$idx}]", get_string('calclength', 'qtype_randomdata'), $precisionoptions);

    }


    public function custom_generator_set_data($datasetdefs, $formdata) {
        $idx = 1;
        foreach ($datasetdefs as $datasetdef) {
            if (preg_match('~^(uniform|loguniform):([^:]*):([^:]*):([0-9]*)$~', $datasetdef->options, $regs)) {
                $formdata["calcdistribution[{$idx}]"] = $regs[1];
                $formdata["calcmin[{$idx}]"] = $regs[2];
                $formdata["calcmax[{$idx}]"] = $regs[3];
                $formdata["calclength[{$idx}]"] = $regs[4];
            }
            $idx++;
        }
        return $formdata;
    }


    public function custom_generator_tools($datasetdef) {
        
        global $OUTPUT;

        if (preg_match('~^(uniform|loguniform):([^:]*):([^:]*):([0-9]*)$~', $datasetdef->options, $regs)) {
            
            $defid = "{$datasetdef->type}-{$datasetdef->category}-{$datasetdef->name}";
            
            for ($i = 0; $i<10; ++$i) {
                $lengthoptions[$i] = get_string(($regs[1] == 'uniform' ? 'decimals' : 'significantfigures'), 'qtype_randomdata', $i);
            }

            $menu1 = html_writer::label(get_string('lengthoption', 'qtype_randomdata'), 'menucalclength', false, array('class' => 'accesshide'));
            $menu1 .= html_writer::select($lengthoptions, 'calclength[]', $regs[4], null, array('class' => 'custom-select'));
            $options = array('uniform' => get_string('uniformbit', 'qtype_randomdata'),'loguniform' => get_string('loguniformbit', 'qtype_randomdata'));
            $menu2 = html_writer::label(get_string('distributionoption', 'qtype_randomdata'),'menucalcdistribution', false, array('class' => 'accesshide'));
            $menu2 .= html_writer::select($options, 'calcdistribution[]', $regs[1], null, array('class' => 'custom-select'));
            
            return '<input type="submit" class="btn btn-secondary" onclick="'
                . "getElementById('addform').regenerateddefid.value='{$defid}'; return true;"
                .'" value="'. get_string('generatevalue', 'qtype_randomdata') . '"/><br/>'
                . '<input type="text" class="form-control" size="3" name="calcmin[]" '
                . " value=\"{$regs[2]}\"/> &amp; <input name=\"calcmax[]\" "
                . ' type="text" class="form-control" size="3" value="' . $regs[3] .'"/> '
                . $menu1 . '<br/>'
                . $menu2;

        } else {
            return '';
        }

    }


    public function update_dataset_options($datasetdefs, $form) {

        global $OUTPUT;

        // If we have information about new options.
        $uniquedefs = array_values(array_unique($form->definition));
        foreach ($uniquedefs as $key => $defid) {
            if (isset($datasetdefs[$defid]) && is_numeric($form->calcmin[$key+1]) && is_numeric($form->calcmax[$key+1]) && is_numeric($form->calclength[$key+1])) {
                    $datasetdefs[$defid]->options =
                        'uniform' . ':'
                        . $form->calcmin[$key+1] . ':'
                        . $form->calcmax[$key+1] . ':'
                        . $form->calclength[$key+1];
                        
            }
        }

        // Look for empty options, on which we set default values.
        foreach ($datasetdefs as $defid => $def) {
            if (empty($def->options)) {
                $datasetdefs[$defid]->options = 'uniform:1.0:10.0:1';
            }
        }

        return $datasetdefs;

    }


    /**
     * Function to save question of randomdata.
     *
     * @param object $question.
     * @param object $fromform.
     */
    public function save_question_randomdata($question, $fromform) {
        
        global $DB;
        $valid_formula = new stdClass();

        foreach ($question->options->answers as $key => $answer) {
            if ($options = $DB->get_record('question_randomdata', array('answer' => $key))) {

                $options->tolerance = trim($fromform->tolerance[$key]);
                $options->tolerancetype  = trim($fromform->tolerancetype[$key]);
                $options->correctanswerlength  = trim($fromform->correctanswerlength[$key]);
                $options->correctanswerformat  = trim($fromform->correctanswerformat[$key]);
                $DB->update_record('question_randomdata', $options);
                

            }
        }

    }
    
    
    /**
     * Function to save the validation question of randomdata.
     *
     * @param object $question_id.
     * @param object $data.
     * @param object $updated.
     */
    public function save_question_randomdata_validations($question_id, $data, $updated){
       
        global $DB;
        
        $valid_formula = new stdClass();
        
        // If it has not been updated.
        if(!$updated){
            
            $question_id = 1;
            // Response validations (save).
            $formulas_answers = $data['answer'];
            $validation_zero = $data['validationzero'];
            $validation_positive = $data['validationpositive'];
            $validation_negative = $data['validationnegative'];
            $validation_min = $data['min'];
            $validation_max = $data['max'];
    
            // Save into the data base: response validations.
            foreach ($formulas_answers as $key => $answer) {
                
                $valid_formula->question = $question_id;
                $valid_formula->formula = $answer;
                $valid_formula->zero = trim($validation_zero[$key]);
                $valid_formula->positive = trim($validation_positive[$key]);
                $valid_formula->negative = trim($validation_negative[$key]);
                $valid_formula->min = trim($validation_min[$key]);
                $valid_formula->max = trim($validation_max[$key]);
                $DB->insert_record('question_randomdata_valid', $valid_formula);
    
            }

            // Intermediate formula validations.
            $formulas_answers_valid = $data['validationanswer'];
            $validation_zero = $data['validationformulazero'];
            $validation_positive = $data['validationformulapositive'];
            $validation_negative = $data['validationformulanegative'];
            $validation_min = $data['minformula'];
            $validation_max = $data['maxformula'];
    
            // Save into the data base: intermediate formula validations.
            foreach ($formulas_answers_valid as $key => $answer) {

                        $valid_formula->question = $question_id;
                        $valid_formula->formula = $answer;
                        $valid_formula->zero = trim($validation_zero[$key]);
                        $valid_formula->positive = trim($validation_positive[$key]);
                        $valid_formula->negative = trim($validation_negative[$key]);
                        $valid_formula->min = trim($validation_min[$key]);
                        $valid_formula->max = trim($validation_max[$key]);
                        if($valid_formula->formula != ""){
                            $DB->insert_record('question_randomdata_valid', $valid_formula);
                        }
    
            }
        
        // Updating.
        }else{
            
            if ($validations = $DB->get_records('question_randomdata_valid', array('question' => 1))){
                foreach ($validations as $validation) {

                    $validation->question = $question_id;
                    $DB->update_record('question_randomdata_valid', $validation);
                    
                }
            }

        }

    }


    /**
     * This function get the dataset items using id as unique parameter and return an
     * array with itemnumber as index sorted ascendant.
     * If the multiple records with the same itemnumber exist, only the newest one
     * i.e with the greatest id is used, the others are ignored but not deleted.
     */
    public function get_database_dataset_items($definition) {

        global $CFG, $DB;

        $databasedataitems = $DB->get_records_sql(
            " SELECT id , itemnumber, definition,  value
            FROM {question_dataset_items}
            WHERE definition = $definition order by id DESC ", array($definition));

        $dataitems = Array();
        foreach ($databasedataitems as $id => $dataitem) {
            if (!isset($dataitems[$dataitem->itemnumber])) {
                $dataitems[$dataitem->itemnumber] = $dataitem;
            }
        }
        ksort($dataitems);
        return $dataitems;

    }


    /**
     * Function to save the dataset items.
     *
     * @param object $question.
     * @param object $fromform.
     */
    public function save_dataset_items($question, $fromform) {

        global $CFG, $DB;
        $synchronize = false;

        if (isset($fromform->nextpageparam['forceregeneration'])) {
            $regenerate = $fromform->nextpageparam['forceregeneration'];
        } else {
            $regenerate = 0;
        }

        if (empty($question->options)) {
            $this->get_question_options($question);
        }

        if (!empty($question->options->synchronize)) {
            $synchronize = true;
        }

        // Get the old datasets for this question.
        $datasetdefs = $this->get_dataset_definitions($question->id, array());
        // Handle generator options.
        $olddatasetdefs = fullclone($datasetdefs);
        $datasetdefs = $this->update_dataset_options($datasetdefs, $fromform);
        $maxnumber = -1;

        foreach ($datasetdefs as $defid => $datasetdef) {
            if (isset($datasetdef->id) && $datasetdef->options != $olddatasetdefs[$defid]->options) {
                // Save the new value for options.
                $DB->update_record('question_dataset_definitions', $datasetdef);

            }
            // Get maxnumber.
            if ($maxnumber == -1 || $datasetdef->itemcount < $maxnumber) {
                $maxnumber = $datasetdef->itemcount;
            }
        }

        // Handle adding and removing of dataset items.
        $i = 1;
        if ($maxnumber > self::MAX_DATASET_ITEMS) {
            $maxnumber = self::MAX_DATASET_ITEMS;
        }

        ksort($fromform->definition);

        foreach ($fromform->definition as $key => $defid) {
            // If the delete button has not been pressed then skip the datasetitems in the 'add item' part of the form.
            if ($i > count($datasetdefs)*$maxnumber) {
                break;
            }

            $addeditem = new stdClass();
            $addeditem->definition = $datasetdefs[$defid]->id;
            $addeditem->value = $fromform->number[$i];
            $addeditem->itemnumber = ceil($i / count($datasetdefs));

            if ($fromform->itemid[$i]) {
                // Reuse any previously used record.
                $addeditem->id = $fromform->itemid[$i];
                $DB->update_record('question_dataset_items', $addeditem);
            } else {
                $DB->insert_record('question_dataset_items', $addeditem);
            }

            $i++;
        }

        if (isset($addeditem->itemnumber) && $maxnumber < $addeditem->itemnumber && $addeditem->itemnumber < self::MAX_DATASET_ITEMS) {
            $maxnumber = $addeditem->itemnumber;
            foreach ($datasetdefs as $key => $newdef) {
                if (isset($newdef->id) && $newdef->itemcount <= $maxnumber) {
                    $newdef->itemcount = $maxnumber;
                    // Save the new value for options.
                    $DB->update_record('question_dataset_definitions', $newdef);
                }
            }
        }
        
        // Adding supplementary items.
        $numbertoadd = 0;
        if (isset($fromform->addbutton) && $fromform->selectadd > 0 && $maxnumber < self::MAX_DATASET_ITEMS) {

            $dataset_items = $this->generate_data_with_distribution($datasetdefs, $fromform->selectadd,$question->id);

            $numbertoadd = $fromform->selectadd;

            if (self::MAX_DATASET_ITEMS - $maxnumber < $numbertoadd) {
                $numbertoadd = self::MAX_DATASET_ITEMS - $maxnumber;
            }

            // Add datasetitem.
            foreach ($datasetdefs as $defid => $datasetdef) {
               
                $addedevals = 0;
                for ($numberadded = $maxnumber+1; $numberadded <= $maxnumber + $numbertoadd; $numberadded++) {
                    
                        // Check if the data to save is not NAN.
                        $nanflag = is_nan(($dataset_items[$defid][$numberadded-1]));
                        // If is not NAN, save the datasetitem.
                        if($nanflag != 1){
                            $datasetitem = new stdClass();
                            $datasetitem->definition = $datasetdef->id;
                            $datasetitem->itemnumber = $addedevals + 1;
                            $datasetitem->value = $dataset_items[$defid][$numberadded-1];
                            $DB->insert_record('question_dataset_items', $datasetitem);
                            $addedevals++;
                        }
                }// For number added.
            }// Datasetsdefs end.

            $maxnumber = $addedevals;

            // Updating.
            foreach ($datasetdefs as $key => $newdef) {
                if (isset($newdef->id)) {
                    $newdef->itemcount = $maxnumber;
                    // Save the new value for options.
                    $DB->update_record('question_dataset_definitions', $newdef);
                }
            }

        }

        // To delete datasetitem.
        if (isset($fromform->deletebutton)) {

            if (isset($fromform->selectdelete)) {
                $newmaxnumber = $maxnumber-$fromform->selectdelete;
            } else {
                $newmaxnumber = $maxnumber-1;
            }

            if ($newmaxnumber < 0) {
                $newmaxnumber = 0;
            }

            foreach ($datasetdefs as $datasetdef) {
                if ($datasetdef->itemcount == $maxnumber) {
                    $datasetdef->itemcount= 0;
                    $DB->update_record('question_dataset_definitions', $datasetdef);
                }
                $DB->delete_records('question_dataset_items', array('definition' => $datasetdef->id));
                $DB->delete_records('question_randomdata_results', array('question' => $question->id));
            }
        }

    }



    public function generate_dataset_item($options) {

        if (!preg_match('~^(uniform|loguniform):([^:]*):([^:]*):([0-9]*)$~',
                $options, $regs)) {
            // Unknown options...
            return false;
        }
        if ($regs[1] == 'uniform') {
            $nbr = $regs[2] + ($regs[3]-$regs[2])*mt_rand()/mt_getrandmax();
            return sprintf("%.".$regs[4].'f', $nbr);

        } else if ($regs[1] == 'loguniform') {
            $log0 = log(abs($regs[2])); // It would have worked the other way to.
            $nbr = exp($log0 + (log(abs($regs[3])) - $log0)*mt_rand()/mt_getrandmax());
            return sprintf("%.".$regs[4].'f', $nbr);

        } else {
            print_error('disterror', 'question', '', $regs[1]);
        }
        return '';

    }


     /**
     * Function to generate data with normal, uniform, loguniform, and triangle distributions. 
     * This returns the ideal distribution of the data.
     * 
     * @param object $datasetdefs.
     * @param object $selectadd.
     * @param object $questionid.
     * @return array $ideal_distribution.
     */
    public function generate_data_with_distribution($datasetdefs, $selectadd,$questionid) {

        global $DB;

        $object_uniform = [];
        $object_loguniform = [];
        $object_normal = [];
        $object_triangle = [];

        // Go through the X evaluations.
        for ($i = 1; $i <= $selectadd; $i++) {

            // Related to UNIFORM DISTRIBUTION:

            // Boolean to validate in formula if the data is useful or not.
            $valido_uniforme = true;
            $flag_inf_uniforme = 0;
            
            do{
                $flag_inf_uniforme += 1;

                // Loop through the variables and create the data for each variable.
                foreach ($datasetdefs as $defid => $variable) {

                    if($i == 1){
                        $object_uniform[$defid] = [];
                    }

                    if (!preg_match('~^(uniform|loguniform):([^:]*):([^:]*):([0-9]*)$~',
                            $variable->options, $regs)) {
                        // Unknown options...
                        return false;
                    }
                        
                    // Applying the uniform distribution.
                    $object_uniform[$defid][$i-1] = round($regs[2] + ($regs[3]-$regs[2])*mt_rand()/mt_getrandmax(), $regs[4]);
                       
                }

                // Verifying the formula and intermediate validations.
                $valido_uniforme = $this->validation_formulas_with_distributions($object_uniform, $i, $questionid);

            // Calculate the data with this distribution while they are not 
            // valid and this cycle has been repeated less than 30 times.
            }while($valido_uniforme && $flag_inf_uniforme < 30);

            // If not valid, place the array with NAN.
            if($valido_uniforme){
                foreach ($datasetdefs as $defid => $variable) {
                    $object_uniform[$defid][$i-1] = NAN;    
                }
            }

            // Related to LOGUNIFORM DISTRIBUTION:

            
            // Boolean to validate in formula if the data is useful or not.
            $valido_loguniforme = true;
            $flag_inf_loguniforme = 0;
            
            do{
                $flag_inf_loguniforme += 1;

                // Loop through the variables and create the data for each variable.
                foreach ($datasetdefs as $defid => $variable) {

                    if($i == 1){
                        $object_loguniform[$defid] = [];
                    }

                    if (!preg_match('~^(uniform|loguniform):([^:]*):([^:]*):([0-9]*)$~',
                            $variable->options, $regs)) {
                        // Unknown options...
                        return false;
                    }
                        
                    // Applying the Log-uniform
                    $log0 = log(abs($regs[2]));
                    $object_loguniform[$defid][$i-1] = round(exp($log0 + (log(abs($regs[3])) - $log0)*mt_rand()/mt_getrandmax()),$regs[4]);
                        
                }

                // Verifying the formula and intermediate validations.
                $valido_loguniforme = $this->validation_formulas_with_distributions($object_loguniform, $i, $questionid);

            // Calculate the data with this distribution while they are not 
            // valid and this cycle has been repeated less than 30 times.
            }while($valido_loguniforme && $flag_inf_loguniforme < 30);

            // If not valid, place the array with NAN.
            if($valido_loguniforme){
                foreach ($datasetdefs as $defid => $variable) {
                    $object_loguniform[$defid][$i-1] = NAN;    
                }
            }

            // Related to NORMAL DISTRIBUTION:

            // Boolean to validate in formula if the data is useful or not.
            $valido_normal = true;
            $flag_inf_normal = 0;
            
            do{
                $flag_inf_normal += 1;

                // Loop through the variables and create the data for each variable.
                foreach ($datasetdefs as $defid => $variable) {

                    if($i == 1){
                        $objectnormal[$defid] = [];
                    }

                    if (!preg_match('~^(uniform|loguniform):([^:]*):([^:]*):([0-9]*)$~',
                            $variable->options, $regs)) {
                        // Unknown options...
                        return false;
                    }

                    // Applying normal distribution.
                    $flag=true;
                    $normal = 0;
                    do{
                        $rand1 = mt_rand()/mt_getrandmax();
                        $rand2 = mt_rand()/mt_getrandmax();
                        $gaussian_number = sqrt(-2 * log($rand1)) * cos(2 * M_PI * $rand2);
                        $mean = ($regs[3] + $regs[2]) / 2;
                        $normal = ($gaussian_number * 1) + $mean;
                        $normal = round($normal / 1,$regs[4]) * 1;
                        if($normal > $regs[2] && $normal < $regs[3]) {
                            $flag=false;
                        }
                    }while($flag);

                    $object_normal[$defid][$i-1] = $normal;
                }

                // Verifying the formula and intermediate validations.
                $valido_normal = $this->validation_formulas_with_distributions($object_normal, $i, $questionid);

            }while($valido_normal && $flag_inf_normal < 30);

            // If not valid, place the array with NAN.
            if($valido_normal){
                foreach ($datasetdefs as $defid => $variable) {
                    $object_normal[$defid][$i-1] = NAN;    
                }
            }

            // Related to TRIANGULAR DISTRIBUTION:

            // Boolean to validate in formula if the data is useful or not.
            $valido_triangle = true;
            $flag_inf_triangle = 0;
            
            do{
                
                $flag_inf_triangle += 1;
                
                 // Loop through the variables and create the data for each variable.
                foreach ($datasetdefs as $defid => $variable) {

                    if($i == 1){
                        $objecttriangle[$defid] = [];
                    }

                    if (!preg_match('~^(uniform|loguniform):([^:]*):([^:]*):([0-9]*)$~',
                            $variable->options, $regs)) {
                        // Unknown options...
                        return false;
                    }

                    // Applying triangle distribution.
                    $trend = ($regs[3] + $regs[2]) / 2; 
                    $triangle = 0;
                    $flag2=true;
                    do{
                        $x = $regs[3]*(float)mt_rand()/(float)mt_getrandmax(); //4
                        if($regs[2] <= $x && $x < $trend){
                            $triangle = $regs[2]+ (($regs[3]-$regs[2])*(2*($x-$regs[2]))/(($regs[3]-$regs[2])*($trend-$regs[2])));
                        } else if($x == $trend){
                            $triangle = $regs[2]+ (($regs[3]-$regs[2])*(2/($regs[3]-$regs[2])));
                        }else if($trend < $x && $x <= $regs[3]){
                            $triangle = $regs[2]+ (($regs[3]-$regs[2])*(2*($regs[3]-$x))/(($regs[3]-$regs[2])*($regs[3]-$trend)));
                        }
                        if($triangle > $regs[2] && $triangle < $regs[3]) {
                            $flag2=false;
                        }

                    }while($flag2);

                    $object_triangle[$defid][$i-1] = round($triangle, $regs[4]);
        
                }

                // Verifying the formula and intermediate validations.
                $valido_triangle = $this->validation_formulas_with_distributions($object_triangle, $i, $questionid);

            }while($valido_triangle && $flag_inf_triangle < 30);

            // If not valid, place the array with NAN.
            if($valido_triangle){
                foreach ($datasetdefs as $defid => $variable) {
                    $object_triangle[$defid][$i-1] = NAN;    
                }
            }

        }

        // Comparing the data with the calculated distributions to choose the best.
        $ideal_distribution = $this->comparation_results_distributions($object_uniform, $object_loguniform, $object_normal, $object_triangle, $questionid);
        
        // With each result obtained.
        foreach($ideal_distribution['distribution_results'][1] as $result){
            // If the result is numeric, save the number
            if(is_numeric($result)){
                $resp = new stdClass();
                $resp->question = $questionid;
                $resp->distribution = $ideal_distribution['distribution_select'];
                $resp->result = $result;
                $DB->insert_record('question_randomdata_results', $resp);
            // If the result is not numeric, save error
            }else{
                $resp = new stdClass();
                $resp->question = $questionid;
                $resp->distribution = $ideal_distribution['distribution_select'];
                $resp->result = 'Error';
                $DB->insert_record('question_randomdata_results', $resp);
            }

        }

        return $ideal_distribution;

    }


     /**
     * Function to validate that the data generated by the distributions 
     * comply with the validations of the formulas.
     * 
     * @param object $variables data generated by distribution.
     * @param object $position.
     * @param object $questionid.
     * @return boolean.
     */
    public function validation_formulas_with_distributions($variables, $position, $questionid){

        global $DB;

        // Get the validations formulas.
        $validations = $DB->get_records('question_randomdata_valid', array('question' => $questionid));
        $dataset = [];

        // For that saves in the dataset array the variables placed with their value generated in the distribution.
        foreach($variables as $var => $value){
            $name = explode("-", $var);
            $dataset[$name[2]]= $value[$position-1];
        }

        // Validation formulas.
        foreach($validations as $formula) {

            $answer = $this->substitute_variables_and_eval($formula->formula, $dataset);

            // Validating that it is not infinite.
            if(is_infinite(abs($answer)) || ((string)($answer) === "-INF")){
                return true;
            }

            // If the validation of the formula does not want the result to be zero, 
            // but it did give zero, it returns true to re-execute the distribution.
            if($formula->zero==2 && $answer==0){
                return true;
                
            // If you meet the condition, the following is valid.
            }else{
            
                // If the validation of the formula does not want the result to be positive, 
                // but if it was positive, it returns true to re-execute the distribution.
                if($formula->positive==2 && $answer>0){ 
                    return true;

                // If you meet the condition, the following is valid.
                }else{
                    
                    // If the validation of the formula does not want the result to be negative, 
                    // but if it was negative, it returns true to re-execute the distribution.
                    if($formula->negative==2 && $answer<0){
                        return true;

                    // If you meet the condition, the following is valid.
                    }else{
                        
                        // If in the range of values the max or min is NOT equal to empty, 
                        // which is the default, run the check.
                        if(!($formula->max=="") || !($formula->min=="")){

                            // Check that the result meets the max condition, with min empty.
                            if($formula->min==""){
                                $max = $this->substitute_variables_and_eval($formula->max, $dataset);
                                if($max<=$answer){
                                    return true;
                                }

                            // Check that the result meets the min condition, with max empty.
                            }elseif($formula->max==""){
                                $min = $this->substitute_variables_and_eval($formula->min, $dataset);
                                if($min>=$answer){
                                    return true;
                                }

                            // Check that the result meets the min and max condition.
                            }elseif ($formula->max!="" && $formula->min!=""){
                                $max = $this->substitute_variables_and_eval($formula->max, $dataset);
                                $min = $this->substitute_variables_and_eval($formula->min, $dataset);
                                if($min>=$answer || $max<=$answer){
                                    return true;
                                }
                            }

                            
                        }
                        
                    }
                }


            }

        }

        return false;

    }


    /**
    * Function that compares the results generated by the distributions, 
    * to choose the best one that made the results more distributed.
    * @param object $uniform data with uniform distribution.
    * @param object $loguniform data with loguniform distribution.
    * @param object $normal data with normal distribution.
    * @param object $triangle data with triangle distribution.
    * @param object $questionid.
    */
    public function comparation_results_distributions ($uniform, $loguniform, $normal, $triangle, $questionid){

        global $DB;

        // Obtain the formulas of the answers with 100% qualification.
        $answers100 = $DB->get_records_select('question_answers', "question = ? AND fraction = 1.0000000 ", array($questionid));

        // Array to ponderation the distributions.
        $ponderation_distributions = [0, 0, 0, 0];

        $ans_uniform = [];
        $ans_loguniform = [];
        $ans_normal = [];
        $ans_triangle = [];
        $iterador = 0;

        foreach($answers100 as $answer){

            $iterador += 1;
            $flag = true;

            // For that saves in the dataset array the variables placed 
            // with their value generated by the uniform distribution.
            foreach($uniform as $id => $value){
                if($flag){
                    for($i = 0; $i < count($uniform[$id]); $i++){
        
                        $dataset = [];
        
                        foreach($uniform as $var => $value){
                            $name = explode("-", $var);
                            $dataset[$name[2]]= $value[$i];
                        }
        
                        $ans_uniform[$iterador][$i] = $this->substitute_variables_and_eval($answer->answer, $dataset);
                     
                    }
                }
                $flag = false;
            }
        
            $flag = true;

            // For that saves in the dataset array the variables placed 
            // with their value generated by the loguniform distribution.
            foreach($loguniform as $id => $value){
                if($flag){
                    for($i = 0; $i < count($loguniform[$id]); $i++){
    
                        $dataset = [];

                        foreach($loguniform as $var => $value){
                            $name = explode("-", $var);
                            $dataset[$name[2]]= $value[$i];
                        }
        
                        $ans_loguniform[$iterador][$i] = $this->substitute_variables_and_eval($answer->answer, $dataset);
                     
                    }
                }
                $flag = false;
            }
        
            $flag = true;

            // For that saves in the dataset array the variables placed 
            // with their value generated by the normal distribution.
            foreach($normal as $id => $value){
                if($flag){
                    for($i = 0; $i < count($normal[$id]); $i++){
        
                        $dataset = [];
                        
                        foreach($normal as $var => $value){
                            $name = explode("-", $var);
                            $dataset[$name[2]]= $value[$i];
                        }
        
                        $ans_normal[$iterador][$i] = $this->substitute_variables_and_eval($answer->answer, $dataset);
                     
                    }
                }
                $flag = false;
            }
        
            $flag = true;

            // For that saves in the dataset array the variables placed 
            // with their value generated by the triangle distribution.
            foreach($triangle as $id => $value){
                if($flag){
                    for($i = 0; $i < count($triangle[$id]); $i++){

                        $dataset = [];
                        
                        foreach($triangle as $var => $value){
                            $name = explode("-", $var);
                            $dataset[$name[2]]= $value[$i];
                        }
        
                        $ans_triangle[$iterador][$i] = $this->substitute_variables_and_eval($answer->answer, $dataset);
                     
                    }
                }
                $flag = false;
            }
        
        }

        // For each response calculated by distributions.
        foreach($ans_uniform as $id => $value){

            // Calculating the range of variation of each distribution.
            
            // Range of variation: uniform.
            $min_unif = min($ans_uniform[$id]);
            $max_unif = max($ans_uniform[$id]);
            // Range of variation: loguniform.
            $min_logunif = min($ans_loguniform[$id]);
            $max_logunif = max($ans_loguniform[$id]);
            // Range of variation: normal.
            $min_normal = min($ans_normal[$id]);
            $max_normal = max($ans_normal[$id]);
            $range_normal = $max_normal - $min_normal;
            // Range of variation: triangle.
            $min_triangle = min($ans_triangle[$id]);
            $max_triangle = max($ans_triangle[$id]);
            $range_triangle = $max_triangle - $min_triangle;
            
            // Calculating the rest of measures.

            // Variance: uniform.
            $sum=0;

            for($i=0;$i<count($ans_uniform[$id]);$i++){
                $sum+=$ans_uniform[$id][$i];
            }

            $media = $sum/count($ans_uniform[$id]);
            $sum2=0;

            for($i=0;$i<count($ans_uniform[$id]);$i++){
                $sum2+=($ans_uniform[$id][$i]-$media)*($ans_uniform[$id][$i]-$media);
            }

            // Check if all the response values of the uniform array are numeric to establish the measures of dispersion.
            if($ans_uniform[$id] === array_filter($ans_uniform[$id], 'is_numeric')){
                $range_unif = $max_unif - $min_unif;
                $var_unif = $sum2/count($ans_uniform[$id]);
                $des_unif = sqrt($var_unif);
                $coef_unif = $des_unif / $media;
                
            // If they are not, but no other distribution generated them complete, the measurements of the incomplete arrays are compared.
            }elseif(!($ans_loguniform[$id] === array_filter($ans_loguniform[$id], 'is_numeric')) && !($ans_normal[$id] === array_filter($ans_normal[$id], 'is_numeric')) && !($ans_triangle[$id] === array_filter($ans_triangle[$id], 'is_numeric'))){
                $range_unif = $max_unif - $min_unif;
                $var_unif = $sum2/count($ans_uniform[$id]);
                $des_unif = sqrt($var_unif);
                $coef_unif = $des_unif / $media;
                
            // If any of the other arrays are complete, set the measures to 0 so that this distribution is not compared.
            }else{
                $range_unif = 0;
                $var_unif = 0;
                $des_unif = 0;
                $coef_unif = 0;

            }

            // Variance: loguniform.
            $sum=0;

            for($i=0;$i<count($ans_loguniform[$id]);$i++){
                $sum+=$ans_loguniform[$id][$i];
            }

            $media = $sum/count($ans_loguniform[$id]);
            $sum2=0;

            for($i=0;$i<count($ans_loguniform[$id]);$i++){
                $sum2+=($ans_loguniform[$id][$i]-$media)*($ans_loguniform[$id][$i]-$media);
            }

            // Check if all the response values of the loguniform array are numeric to establish the measures of dispersion.
            if($ans_loguniform[$id] === array_filter($ans_loguniform[$id], 'is_numeric')){
                $range_logunif = $max_logunif - $min_logunif;
                $var_logunif = $sum2/count($ans_loguniform[$id]);
                $des_logunif = sqrt($var_logunif);
                $coef_logunif = $des_logunif / $media;
                
            // If they are not, but no other distribution generated them complete, the measurements of the incomplete arrays are compared.
            }elseif(!($ans_uniform[$id] === array_filter($ans_uniform[$id], 'is_numeric')) && !($ans_normal[$id] === array_filter($ans_normal[$id], 'is_numeric')) && !($ans_triangle[$id] === array_filter($ans_triangle[$id], 'is_numeric'))){
                $range_logunif = $max_logunif - $min_logunif;
                $var_logunif = $sum2/count($ans_loguniform[$id]);
                $des_logunif = sqrt($var_logunif);
                $coef_logunif = $des_logunif / $media;
                
            // If any of the other arrays are complete, set the measures to 0 so that this distribution is not compared.
            }else{
                $range_logunif = 0;
                $var_logunif = 0;
                $des_logunif = 0;
                $coef_logunif = 0;

            }

            // Variance: normal.
            $sum=0;

            for($i=0;$i<count($ans_normal[$id]);$i++){
                $sum+=$ans_normal[$id][$i];
            }

            $media = $sum/count($ans_normal[$id]);
            $sum2=0;

            for($i=0;$i<count($ans_normal[$id]);$i++){
                $sum2+=($ans_normal[$id][$i]-$media)*($ans_normal[$id][$i]-$media);
            }
            
            // Check if all the response values of the normal array are numeric to establish the measures of dispersion.
            if($ans_normal[$id] === array_filter($ans_normal[$id], 'is_numeric')){
                $range_normal = $max_normal - $min_normal;
                $var_normal = $sum2/count($ans_normal[$id]);
                $des_normal = sqrt($var_normal);
                $coef_normal = $des_normal / $media;
                
            // If they are not, but no other distribution generated them complete, the measurements of the incomplete arrays are compared.
            }elseif(!($ans_uniform[$id] === array_filter($ans_uniform[$id], 'is_numeric')) && !($ans_loguniform[$id] === array_filter($ans_loguniform[$id], 'is_numeric')) && !($ans_triangle[$id] === array_filter($ans_triangle[$id], 'is_numeric'))){
                $range_normal = $max_normal - $min_normal;
                $var_normal = $sum2/count($ans_normal[$id]);
                $des_normal = sqrt($var_normal);
                $coef_normal = $des_normal / $media;
                
            // If any of the other arrays are complete, set the measures to 0 so that this distribution is not compared.
            }else{
                $range_normal = 0;
                $var_normal = 0;
                $des_normal = 0;
                $coef_normal = 0;

            }

            // Variance: triangle.
            $sum=0;

            for($i=0;$i<count($ans_triangle[$id]);$i++){

                $sum+=$ans_triangle[$id][$i];

            }

            $media = $sum/count($ans_triangle[$id]);
            $sum2=0;
            
            for($i=0;$i<count($ans_triangle[$id]);$i++){
                $sum2+=($ans_triangle[$id][$i]-$media)*($ans_triangle[$id][$i]-$media);
            }
            
            // Check if all the response values of the triangle array are numeric to establish the measures of dispersion.
            if($ans_triangle[$id] === array_filter($ans_triangle[$id], 'is_numeric')){
                $range_triangle = $max_triangle - $min_triangle;
                $var_triangle = $sum2/count($ans_triangle[$id]);
                $des_triangle = sqrt($var_triangle);
                $coef_triangle = $des_triangle / $media;
                
            // If they are not, but no other distribution generated them complete, the measurements of the incomplete arrays are compared.
            }elseif(!($ans_uniform[$id] === array_filter($ans_uniform[$id], 'is_numeric')) && !($ans_loguniform[$id] === array_filter($ans_loguniform[$id], 'is_numeric')) && !($ans_normal[$id] === array_filter($ans_normal[$id], 'is_numeric'))){
                $range_triangle = $max_triangle - $min_triangle;
                $var_triangle = $sum2/count($ans_triangle[$id]);
                $des_triangle = sqrt($var_triangle);
                $coef_triangle = $des_triangle / $media;
                
            // If any of the other arrays are complete, set the measures to 0 so that this distribution is not compared.
            }else{
                $range_triangle = 0;
                $var_triangle = 0;
                $des_triangle = 0;
                $coef_triangle = 0;

            }

            // Get the largest range and increase a point to the distribution that has the largest range.
            $max_range = max(abs($range_unif), abs($range_logunif), abs($range_normal), abs($range_triangle));

            if($max_range == $range_unif){
                $ponderation_distributions[0] += 1;
            }elseif ($max_range == $range_logunif){
                $ponderation_distributions[1] += 1;
            }elseif($max_range == $range_normal){
                $ponderation_distributions[2] += 1;
            }else{
                $ponderation_distributions[3] += 1;
            }

            // Get the largest variance and add a point to the distribution with the largest variance.
            $max_var = max($var_unif, $var_logunif, $var_normal, $var_triangle);

            if($max_var == $var_unif){
                $ponderation_distributions[0] += 1;
            }elseif ($max_var == $var_logunif){
                $ponderation_distributions[1] += 1;
            }elseif($max_var == $var_normal){
                $ponderation_distributions[2] += 1;
            }else{
                $ponderation_distributions[3] += 1;
            }

            // Get the largest standard deviation and add a point to the distribution with the largest standard deviation.
            $max_des = max($des_unif, $des_logunif, $des_normal, $des_triangle);

            if($max_des == $des_unif){
                $ponderation_distributions[0] += 1;
            }elseif ($max_des == $des_logunif){
                $ponderation_distributions[1] += 1;
            }elseif($max_des == $des_normal){
                $ponderation_distributions[2] += 1;
            }else{
                $ponderation_distributions[3] += 1;
            }

            // Get the largest coefficient of variation and increase a point to the distribution with the largest coefficient of variation.
            $max_coef = max($coef_unif, $coef_logunif, $coef_normal, $coef_triangle);

            if($max_coef == $coef_unif){
                $ponderation_distributions[0] += 1;
            }elseif ($max_coef == $coef_logunif){
                $ponderation_distributions[1] += 1;
            }elseif($max_coef == $coef_normal){
                $ponderation_distributions[2] += 1;
            }else{
                $ponderation_distributions[3] += 1;
            }
            
        }

        // Select the most sparse distribution that got the most points from the array of points.
        $max_dispersion = max($ponderation_distributions);

        // Sets the response and distribution values to return the distribution with the greatest spread.
        if($max_dispersion == $ponderation_distributions[0]){

            $uniform['distribution_results'] = $ans_uniform;
            $uniform['distribution_select'] = 'Uniform';
            return $uniform;

        }elseif ($max_dispersion == $ponderation_distributions[1]){

            $loguniform['distribution_results'] = $ans_loguniform;
            $loguniform['distribution_select'] = 'Log-Uniform';
            return $loguniform;

        }elseif($max_dispersion == $ponderation_distributions[2]){

            $normal['distribution_results'] = $ans_normal;
            $normal['distribution_select'] = 'Normal';
            return $normal;

        }else{

            $triangle['distribution_results'] = $ans_triangle;
            $triangle['distribution_select'] = 'Triangle';
            return $triangle;

        }

    }


    public function comment_header($question) {
        $strheader = '';
        $delimiter = '';

        $answers = $question->options->answers;

        foreach ($answers as $key => $answer) {
            $ans = shorten_text($answer->answer, 17, true);
            $strheader .= $delimiter.$ans;
            $delimiter = '<br/><br/><br/>';
        }
        return $strheader;
    }


    public function comment_on_datasetitems($qtypeobj, $questionid, $questiontext, $answers, $data, $number) {
        global $DB;
        
        $comment = new stdClass();
        $comment->stranswers = array();
        $comment->outsidelimit = false;
        $comment->answers = array();

        // Find a default unit.
        $unit = '';
        if (!empty($questionid)) {
            $units = $DB->get_records('question_numerical_units', array('question' => $questionid, 'multiplier' => 1.0), 'id ASC', '*', 0, 1);
            if ($units) {
                $unit = reset($units);
                $unit = $unit->unit;
            }
        }

        $answers = fullclone($answers);
        $delimiter = ': ';
        $virtualqtype =  $qtypeobj->get_virtual_qtype();
        foreach ($answers as $key => $answer) {
            $error = qtype_randomdata_find_formula_errors($answer->answer);
            if ($error) {
                $comment->stranswers[$key] = $error;
                continue;
            }
            $formula = $this->substitute_variables($answer->answer, $data);
            $formattedanswer = qtype_randomdata_calculate_answer($answer->answer, $data, $answer->tolerance, $answer->tolerancetype, $answer->correctanswerlength, $answer->correctanswerformat, $unit);
            if ($formula === '*') {
                $answer->min = ' ';
                $formattedanswer->answer = $answer->answer;
            } else {
                eval('$ansvalue = '.$formula.';');
                $ans = new qtype_numerical_answer(0, $ansvalue, 0, '', 0, $answer->tolerance);
                $ans->tolerancetype = $answer->tolerancetype;
                list($answer->min, $answer->max) = $ans->get_tolerance_interval($answer);
            }

            if ($answer->min === '') {
                // This should mean that something is wrong.
                $comment->stranswers[$key] = " {$formattedanswer->answer}".'<br/><br/>';
            } else if ($formula === '*') {
                $comment->stranswers[$key] = $formula . ' = ' .
                        get_string('anyvalue', 'qtype_randomdata') . '<br/><br/><br/>';
            } else {
                $formula = shorten_text($formula, 57, true);
                $comment->stranswers[$key] = $formula . ' = ' . $formattedanswer->answer . '<br/>';
                $correcttrue = new stdClass();
                $correcttrue->correct = $formattedanswer->answer;
                $correcttrue->true = '';
                if ($formattedanswer->answer < $answer->min || $formattedanswer->answer > $answer->max) {
                    $comment->outsidelimit = true;
                    $comment->answers[$key] = $key;
                    $comment->stranswers[$key] .= get_string('trueansweroutsidelimits', 'qtype_randomdata', $correcttrue);
                } else {
                    $comment->stranswers[$key] .= get_string('trueanswerinsidelimits', 'qtype_randomdata', $correcttrue);
                }

                $comment->stranswers[$key] .= '<br/>';
                $comment->stranswers[$key] .= get_string('min', 'qtype_randomdata') . $delimiter . $answer->min . ' --- ';
                $comment->stranswers[$key] .= get_string('max', 'qtype_randomdata') . $delimiter . $answer->max;

            }
        }

        return fullclone($comment);

    }


    /**
    * Function to set tolerance types for responses
    * @return array of strings of tolerance types
    */
    public function tolerance_types() {
        return array(
            '1' => get_string('relative', 'qtype_numerical'),
            '2' => get_string('nominal', 'qtype_numerical'),
            '3' => get_string('geometric', 'qtype_numerical')
        );
    }


    public function dataset_options($form, $name, $mandatory = true, $renameabledatasets = false) {

        // DB call to fetch all variables.
        list($options, $selected) = $this->dataset_options_from_database(
                $form, $name, '', 'qtype_randomdata');

        // Save variables that match the ones being worked on.
        foreach ($options as $key => $whatever) {
            if (!preg_match('~^1-~', $key) && $key != '0') {
                unset($options[$key]);
            }
        }
        
        if (!$selected) {
            if ($mandatory) {
                $selected =  "1-0-{$name}"; // Default.
            } else {
                $selected = '0'; // Default.
            }
        }

        return array($options, $selected);

    }


    public function construct_dataset_menus($form, $mandatorydatasets, $optionaldatasets) {
        
        global $OUTPUT;
        $datasetmenus = array();

        foreach ($mandatorydatasets as $datasetname) {
            if (!isset($datasetmenus[$datasetname])) {
                list($options, $selected) = $this->dataset_options($form, $datasetname);
                unset($options['0']);
                $datasetmenus[$datasetname] = html_writer::select($options, 'dataset[]', $selected, null);
            }
        }

        foreach ($optionaldatasets as $datasetname) {
            if (!isset($datasetmenus[$datasetname])) {
                list($options, $selected) = $this->dataset_options($form, $datasetname);
                $datasetmenus[$datasetname] = html_writer::select($options, 'dataset[]', $selected, null);
            }
        }

        return $datasetmenus;

    }


    /**
    * Function that replaces the values of variables in a text
    * @param $str text where the variables are to be replaced
    * @param $dataset array of values and keys of the variables to replace
    * @return $str text with the values of the variables already replaced
    */
    public function substitute_variables($str, $dataset) {
        
        global $OUTPUT;
        // Testing for wrong numerical values.
        // All calculations used this function so testing here should be OK.

        foreach ($dataset as $name => $value) {
            $val = $value;
            if (! is_numeric($val)) {
                $a = new stdClass();
                $a->name = '{'.$name.'}';
                $a->value = $value;
                echo $OUTPUT->notification(get_string('notvalidnumber', 'qtype_randomdata', $a));
                $val = 1.0;
            }
            if ($val <= 0) {
                $str = str_replace('{'.$name.'}', '('.$val.')', $str);
            } else {
                $str = str_replace('{'.$name.'}', $val, $str);
            }
        }

        return $str;

    }


    /**
    * Function that evaluate the values of variables in a text
    * @param $str text where the variables are to be evaluated
    * @param $dataset array of values and keys of the variables to evaluate
    * @return $str text with the values of the variables already evaluate
    */
    public function evaluate_equations($str, $dataset) {
        $formula = $this->substitute_variables($str, $dataset);
        if ($error = qtype_randomdata_find_formula_errors($formula)) {
            return $error;
        }
        return $str;
    }


    /**
    * Function that substitute and evaluate the values of variables in a text
    * @param $str text where the variables are to be replaced and evaluated
    * @param $dataset array of values and keys of the variables to replace and evaluate
    * @return $str text with the values of the variables already replace and evaluate
    */
    public function substitute_variables_and_eval($str, $dataset) {

        $formula = $this->substitute_variables($str, $dataset);

        if ($error = qtype_randomdata_find_formula_errors($formula)) {
            return $error;
        }

        // Calculate the correct answer.
        if (empty($formula)) {
            $str = '';
        } else if ($formula === '*') {
            $str = '*';
        } else {
            $str = null;
            eval('$str = '.$formula.';');
        }

        return $str;
    }


    /**
    * Function to obtain the information of the variables of the question
    * @param $questionid id of the question
    * @param $newdatasets 
    * @return $datasetdefs 
    */
    public function get_dataset_definitions($questionid, $newdatasets) {
        global $DB;
        // Get the existing datasets for this question.
        $datasetdefs = array();

        if (!empty($questionid)) {
            global $CFG;

            $sql = "SELECT i.*
                      FROM {question_datasets} d, {question_dataset_definitions} i
                     WHERE d.question = ? AND d.datasetdefinition = i.id
                  ORDER BY i.id";

            if ($records = $DB->get_records_sql($sql, array($questionid))) {
                foreach ($records as $r) {
                    $datasetdefs["{$r->type}-{$r->category}-{$r->name}"] = $r;
                }
            }
        }

        foreach ($newdatasets as $dataset) {
            if (!$dataset) {
                continue; // The no dataset case...
            }

            if (!isset($datasetdefs[$dataset])) {
                // Make new datasetdef.
                list($type, $category, $name) = explode('-', $dataset, 3);
                $datasetdef = new stdClass();
                $datasetdef->type = $type;
                $datasetdef->name = $name;
                $datasetdef->category  = $category;
                $datasetdef->itemcount = 0;
                $datasetdef->options   = 'uniform:1.0:10.0:1';
                $datasetdefs[$dataset] = clone($datasetdef);
            }
        }

        return $datasetdefs;
    }


    /** 
    * Function to save the information of the variables of the question
    * @param $form form data of the variables 
    */
    public function save_dataset_definitions($form) {
        global $DB;
        
        // Save synchronize.
        if (empty($form->dataset)) {
            $form->dataset = array();
        }

        // Save datasets.
        $datasetdefinitions = $this->get_dataset_definitions($form->id, $form->dataset);
        $tmpdatasets = array_flip($form->dataset);
        $defids = array_keys($datasetdefinitions);
        foreach ($defids as $defid) {
            $datasetdef = &$datasetdefinitions[$defid];

            if (isset($datasetdef->id)) {
                if (!isset($tmpdatasets[$defid])) {
                    // This dataset is not used any more, delete it.
                    $DB->delete_records('question_datasets', array('question' => $form->id, 'datasetdefinition' => $datasetdef->id));
                    if ($datasetdef->category == 0) {
                        // Question local dataset.
                        $DB->delete_records('question_dataset_definitions', array('id' => $datasetdef->id));
                        $DB->delete_records('question_dataset_items', array('definition' => $datasetdef->id));
                    }
                }
                // This has already been saved or just got deleted.
                unset($datasetdefinitions[$defid]);
                continue;
            }

            $datasetdef->id = $DB->insert_record('question_dataset_definitions', $datasetdef);

            if (0 != $datasetdef->category) {
                // We need to look for already existing datasets in the category.
                // First creating the datasetdefinition above,
                // then we can manage to automatically take care of some possible realtime concurrence.

                if ($olderdatasetdefs = $DB->get_records_select('question_dataset_definitions',
                        'type = ? AND name = ? AND category = ? AND id < ?
                        ORDER BY id DESC',
                        array($datasetdef->type, $datasetdef->name,
                                $datasetdef->category, $datasetdef->id))) {

                    while ($olderdatasetdef = array_shift($olderdatasetdefs)) {
                        $DB->delete_records('question_dataset_definitions', array('id' => $datasetdef->id));
                        $datasetdef = $olderdatasetdef;
                    }
                }
            }

            // Create relation to this dataset.
            $questiondataset = new stdClass();
            $questiondataset->question = $form->id;
            $questiondataset->datasetdefinition = $datasetdef->id;
            $DB->insert_record('question_datasets', $questiondataset);
            unset($datasetdefinitions[$defid]);
        }

        // Remove local obsolete datasets as well as relations
        // to datasets in other categories.
        if (!empty($datasetdefinitions)) {
            foreach ($datasetdefinitions as $def) {
                $DB->delete_records('question_datasets', array('question' => $form->id, 'datasetdefinition' => $def->id));

                if ($def->category == 0) { // Question local dataset.
                    $DB->delete_records('question_dataset_definitions', array('id' => $def->id));
                    $DB->delete_records('question_dataset_items', array('definition' => $def->id));
                }
            }
        }
    }


    /** This function create a copy of the datasets (definition and dataitems)
     * from the preceding question if they remain in the new question
     * otherwise its create the datasets that have been added as in the
     * save_dataset_definitions()
     */
    public function save_as_new_dataset_definitions($form, $initialid) {

        global $CFG, $DB;

        // Get the datasets from the intial question.
        $datasetdefinitions = $this->get_dataset_definitions($initialid, $form->dataset);

        // Param $tmpdatasets contains those of the new question.
        $tmpdatasets = array_flip($form->dataset);
        $defids = array_keys($datasetdefinitions);// New datasets.

        foreach ($defids as $defid) {
            $datasetdef = &$datasetdefinitions[$defid];

            if (isset($datasetdef->id)) {
                // This dataset exist in the initial question.
                if (!isset($tmpdatasets[$defid])) {
                    // Do not exist in the new question so ignore.
                    unset($datasetdefinitions[$defid]);
                    continue;
                }

                // Create a copy but not for category one.
                if (0 == $datasetdef->category) {
                    $olddatasetid = $datasetdef->id;
                    $olditemcount = $datasetdef->itemcount;
                    $datasetdef->itemcount = 0;
                    $datasetdef->id = $DB->insert_record('question_dataset_definitions', $datasetdef);
                    // Copy the dataitems.
                    $olditems = $this->get_database_dataset_items($olddatasetid);
                   
                    if (count($olditems) > 0) {
                        $itemcount = 0;
                        foreach ($olditems as $item) {
                            $item->definition = $datasetdef->id;
                            $DB->insert_record('question_dataset_items', $item);
                            $itemcount++;
                        }

                        // Update item count to olditemcount if
                        // at least this number of items has been recover from the database.
                        if ($olditemcount <= $itemcount) {
                            $datasetdef->itemcount = $olditemcount;
                        } else {
                            $datasetdef->itemcount = $itemcount;
                        }

                        $DB->update_record('question_dataset_definitions', $datasetdef);
                    } // End of  copy the dataitems.

                }// End of  copy the datasetdef.

                // Create relation to the new question with this
                // copy as new datasetdef from the initial question.
                $questiondataset = new stdClass();
                $questiondataset->question = $form->id;
                $questiondataset->datasetdefinition = $datasetdef->id;
                $DB->insert_record('question_datasets', $questiondataset);
                unset($datasetdefinitions[$defid]);
                continue;

            }// End of datasetdefs from the initial question.
            // Really new one code similar to save_dataset_definitions().

            $datasetdef->id = $DB->insert_record('question_dataset_definitions', $datasetdef);

            if (0 != $datasetdef->category) {
                // We need to look for already existing datasets in the category.
                // By first creating the datasetdefinition above we
                // can manage to automatically take care of
                // some possible realtime concurrence.
                if ($olderdatasetdefs = $DB->get_records_select('question_dataset_definitions',
                        "type = ? AND " . $DB->sql_equal('name', '?') . " AND category = ? AND id < ?
                        ORDER BY id DESC",
                        array($datasetdef->type, $datasetdef->name, $datasetdef->category, $datasetdef->id))) {

                    while ($olderdatasetdef = array_shift($olderdatasetdefs)) {
                        $DB->delete_records('question_dataset_definitions', array('id' => $datasetdef->id));
                        $datasetdef = $olderdatasetdef;
                    }
                }
            }

            // Create relation to this dataset.
            $questiondataset = new stdClass();
            $questiondataset->question = $form->id;
            $questiondataset->datasetdefinition = $datasetdef->id;
            $DB->insert_record('question_datasets', $questiondataset);
            unset($datasetdefinitions[$defid]);
        }

        // Remove local obsolete datasets as well as relations
        // to datasets in other categories.
        if (!empty($datasetdefinitions)) {
            foreach ($datasetdefinitions as $def) {
                $DB->delete_records('question_datasets', array('question' => $form->id, 'datasetdefinition' => $def->id));

                if ($def->category == 0) { // Question local dataset.
                    $DB->delete_records('question_dataset_definitions', array('id' => $def->id));
                    $DB->delete_records('question_dataset_items', array('definition' => $def->id));
                }
            }
        }
    }


    // Dataset functionality.
    public function pick_question_dataset($question, $datasetitem) {

        // Select a dataset in the following format:
        // an array indexed by the variable names (d.name) pointing to the value
        // to be substituted.
        global $CFG, $DB;

        if (!$dataitems = $DB->get_records_sql(
                "SELECT i.id, d.name, i.value
                   FROM {question_dataset_definitions} d,
                        {question_dataset_items} i,
                        {question_datasets} q
                  WHERE q.question = ?
                    AND q.datasetdefinition = d.id
                    AND d.id = i.definition
                    AND i.itemnumber = ?
               ORDER BY i.id DESC ", array($question->id, $datasetitem))) {
            $a = new stdClass();
            $a->id = $question->id;
            $a->item = $datasetitem;
            print_error('cannotgetdsfordependent', 'question', '', $a);
        }

        $dataset = Array();
        foreach ($dataitems as $id => $dataitem) {
            if (!isset($dataset[$dataitem->name])) {
                $dataset[$dataitem->name] = $dataitem->value;
            }
        }

        return $dataset;
    }


    public function dataset_options_from_database($form, $name, $prefix = '', $langfile = 'qtype_randomdata') {
        global $CFG, $DB;

        $type = 1; // Only type = 1 (i.e. old 'LITERAL') has ever been used.
        // First options - it is not a dataset...
        $options['0'] = get_string($prefix.'nodataset', $langfile);

        // New question no local.
        if (!isset($form->id) || $form->id == 0) {
            $key = "{$type}-0-{$name}";
            $options[$key] = get_string($prefix."newlocal{$type}", $langfile);
            $currentdatasetdef = new stdClass();
            $currentdatasetdef->type = '0';
        } else {
            // Construct question local options.
            $sql = "SELECT a.*
                FROM {question_dataset_definitions} a, {question_datasets} b
               WHERE a.id = b.datasetdefinition AND a.type = '1' AND b.question = ? AND " . $DB->sql_equal('a.name', '?');
            $currentdatasetdef = $DB->get_record_sql($sql, array($form->id, $name));
            if (!$currentdatasetdef) {
                $currentdatasetdef = new stdClass();
                $currentdatasetdef->type = '0';
            }
            $key = "{$type}-0-{$name}";
            
            $options[$key] = get_string($prefix."newlocal{$type}", $langfile);

        }
        // Construct question category options.
        $categorydatasetdefs = $DB->get_records_sql(
            "SELECT b.question, a.*
            FROM {question_datasets} b,
            {question_dataset_definitions} a
            WHERE a.id = b.datasetdefinition
            AND a.type = '1'
            AND a.category = ?
            AND " . $DB->sql_equal('a.name', '?'), array($form->category, $name));

        $type = 1;
        $key = "{$type}-{$form->category}-{$name}";
     
        return array($options, $currentdatasetdef->type ? "{$currentdatasetdef->type}-{$currentdatasetdef->category}-{$name}" : '');
    }


    /**
     * Find the names of all datasets mentioned in a piece of question content like the question text.
     * @param $text the text to analyse.
     * @return array with dataset name for both key and value.
     */
    public function find_dataset_names($text) {
        preg_match_all(self::PLACEHODLER_REGEX, $text, $matches);

        return array_combine($matches[1], $matches[1]);
    }


    /**
     * Find all the formulas in a bit of text.
     *
     * @param $text text to analyse.
     * @return array where they keys an values are the formulas.
     */
    public function find_formulas($text) {
        preg_match_all(self::FORMULAS_IN_TEXT_REGEX, $text, $matches);

        return array_combine($matches[1], $matches[1]);
    }

    
    /**
     * This function retrieve the item count of the available category shareable
     * wild cards that is added as a comment displayed when a wild card with
     * the same name is displayed in datasetdefinitions_form.php
     */
    public function get_dataset_definitions_category($form) {
        global $CFG, $DB;

        $datasetdefs = array();
        $lnamemax = 30;

        if (!empty($form->category)) {
            $sql = "SELECT i.*, d.*
                      FROM {question_datasets} d, {question_dataset_definitions} i
                     WHERE i.id = d.datasetdefinition AND i.category = ?";
            if ($records = $DB->get_records_sql($sql, array($form->category))) {
                foreach ($records as $r) {
                    if (!isset ($datasetdefs["{$r->name}"])) {
                        $datasetdefs["{$r->name}"] = $r->itemcount;
                    }
                }
            }
        }

        return $datasetdefs;
    }

    
    /**
     * This function shortens a question name if it exceeds the character limit.
     *
     * @param string $stringtoshorten the string to be shortened.
     * @param int $characterlimit the character limit.
     * @return string
     */
    public function get_short_question_name($stringtoshorten, $characterlimit)
    {
        if (!empty($stringtoshorten)) {
            $returnstring = format_string($stringtoshorten);
            if (strlen($returnstring) > $characterlimit) {
                $returnstring = shorten_text($returnstring, $characterlimit, true);
            }
            return $returnstring;
        } else {
            return '';
        }
    }

    public function get_virtual_qtype() {
        return question_bank::get_qtype('numerical');
    }


    public function get_possible_responses($questiondata) {
        $responses = array();

        $virtualqtype = $this->get_virtual_qtype();
        $unit = $virtualqtype->get_default_numerical_unit($questiondata);

        $tolerancetypes = $this->tolerance_types();

        $starfound = false;
        foreach ($questiondata->options->answers as $aid => $answer) {
            $responseclass = $answer->answer;

            if ($responseclass === '*') {
                $starfound = true;
            } else {
                $a = new stdClass();
                $a->answer = $virtualqtype->add_unit($questiondata, $responseclass, $unit);
                $a->tolerance = $answer->tolerance;
                $a->tolerancetype = $tolerancetypes[$answer->tolerancetype];

                $responseclass = get_string('answerwithtolerance', 'qtype_randomdata', $a);
            }

            $responses[$aid] = new question_possible_response($responseclass,
                    $answer->fraction);
        }

        if (!$starfound) {
            $responses[0] = new question_possible_response(
            get_string('didnotmatchanyanswer', 'question'), 0);
        }

        $responses[null] = question_possible_response::no_response();

        return array($questiondata->id => $responses);
    }


    public function move_files($questionid, $oldcontextid, $newcontextid) {
        $fs = get_file_storage();
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_answers($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }


    protected function delete_files($questionid, $contextid) {
        $fs = get_file_storage();
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_answers($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }
}


function qtype_randomdata_calculate_answer($formula, $individualdata, $tolerance, $tolerancetype, $answerlength, $answerformat = '1', $unit = '') {
    // The return value has these properties:
    // ->answer    the correct answer.
    // ->min       the lower bound for an acceptable response.
    // ->max       the upper bound for an accetpable response.
    $randomdata = new stdClass();
    // Exchange formula variables with the correct values.
    $answer = question_bank::get_qtype('randomdata')->substitute_variables_and_eval($formula, $individualdata);
    
    if (!is_numeric($answer)) {
        // Something went wrong, so just return NaN.
        $randomdata->answer = NAN;
        return $randomdata;
    }
    if ('1' == $answerformat) { // Answer is to have $answerlength decimals.
        // Decimal places.
        $randomdata->answer = sprintf('%.' . $answerlength . 'F', $answer);

    } else if ($answer) { // Significant figures does only apply if the result is non-zero.

        // Convert to positive answer.
        if ($answer < 0) {
            $answer = -$answer;
            $sign = '-';
        } else {
            $sign = '';
        }

        // Determine the format 0.[1-9][0-9]* for the answer.
        $p10 = 0;
        while ($answer < 1) {
            --$p10;
            $answer *= 10;
        }
        while ($answer >= 1) {
            ++$p10;
            $answer /= 10;
        }
        // ... and have the answer rounded of to the correct length.
        $answer = round($answer, $answerlength);

        // If we rounded up to 1.0, place the answer back into 0.[1-9][0-9]* format.
        if ($answer >= 1) {
            ++$p10;
            $answer /= 10;
        }

        // Have the answer written on a suitable format: either scientific or plain numeric.
        if (-2 > $p10 || 4 < $p10) {
            // Use scientific format.
            $exponent = 'e'.--$p10;
            $answer *= 10;
            if (1 == $answerlength) {
                $randomdata->answer = $sign.$answer.$exponent;
            } else {
                // Attach additional zeros at the end of $answer.
                $answer .= (1 == strlen($answer) ? '.' : '')
                    . '00000000000000000000000000000000000000000x';
                $randomdata->answer = $sign
                    .substr($answer, 0, $answerlength +1).$exponent;
            }
        } else {
            // Stick to plain numeric format.
            $answer *= "1e{$p10}";
            if (0.1 <= $answer / "1e{$answerlength}") {
                $randomdata->answer = $sign.$answer;
            } else {
                // Could be an idea to add some zeros here.
                $answer .= (preg_match('~^[0-9]*$~', $answer) ? '.' : '')
                    . '00000000000000000000000000000000000000000x';
                $oklen = $answerlength + ($p10 < 1 ? 2-$p10 : 1);
                $randomdata->answer = $sign.substr($answer, 0, $oklen);
            }
        }

    } else {
        $randomdata->answer = 0.0;
    }
    if ($unit != '') {
            $randomdata->answer = $randomdata->answer . ' ' . $unit;
    }

    // Return the result.
    return $randomdata;
}


/**
 * Validate a formula.
 * @param string $formula the formula to validate.
 * @return string|boolean false if there are no problems. Otherwise a string error message.
 */
function qtype_randomdata_find_formula_errors($formula) {

    foreach (['//', '/*', '#', '<?', '?>'] as $commentstart) {
        if (strpos($formula, $commentstart) !== false) {
            return get_string('illegalformulasyntax', 'qtype_randomdata', $commentstart);
        }
    }

    // Validates the formula submitted from the question edit page.
    // Returns false if everything is alright,
    // otherwise it constructs an error message.
    // Strip away dataset names. Use 1.0 to catch illegal concatenation like {a}{b}.
    $formula = preg_replace(qtype_randomdata::PLACEHODLER_REGEX, '1.0', $formula);

    // Strip away empty space and lowercase it.
    $formula = strtolower(str_replace(' ', '', $formula));

    $safeoperatorchar = '-+/*%>:^\~<?=&|!'; /* */
    $operatorornumber = "[{$safeoperatorchar}.0-9eE]";

    while (preg_match("~(^|[{$safeoperatorchar},(])([a-z0-9_]*)" .
            "\\(({$operatorornumber}+(,{$operatorornumber}+((,{$operatorornumber}+)+)?)?)?\\)~",
            $formula, $regs)) {
        switch ($regs[2]) {
            // Simple parenthesis.
            case '':
                if ((isset($regs[4]) && $regs[4]) || strlen($regs[3]) == 0) {
                    return get_string('illegalformulasyntax', 'qtype_randomdata', $regs[0]);
                }
                break;

                // Zero argument functions.
            case 'pi':
                if (array_key_exists(3, $regs)) {
                    return get_string('functiontakesnoargs', 'qtype_randomdata', $regs[2]);
                }
                break;

                // Single argument functions (the most common case).
            case 'abs': case 'acos': case 'acosh': case 'asin': case 'asinh':
            case 'atan': case 'atanh': case 'bindec': case 'ceil': case 'cos':
            case 'cosh': case 'decbin': case 'decoct': case 'deg2rad':
            case 'exp': case 'expm1': case 'floor': case 'is_finite':
            case 'is_infinite': case 'is_nan': case 'log10': case 'log1p':
            case 'octdec': case 'rad2deg': case 'sin': case 'sinh': case 'sqrt':
            case 'tan': case 'tanh':
                if (!empty($regs[4]) || empty($regs[3])) {
                    return get_string('functiontakesonearg', 'qtype_randomdata', $regs[2]);
                }
                break;

                // Functions that take one or two arguments.
            case 'log': case 'round':
                if (!empty($regs[5]) || empty($regs[3])) {
                    return get_string('functiontakesoneortwoargs', 'qtype_randomdata', $regs[2]);
                }
                break;

                // Functions that must have two arguments.
            case 'atan2': case 'fmod': case 'pow':
                if (!empty($regs[5]) || empty($regs[4])) {
                    return get_string('functiontakestwoargs', 'qtype_randomdata', $regs[2]);
                }
                break;

                // Functions that take two or more arguments.
            case 'min': case 'max':
                if (empty($regs[4])) {
                    return get_string('functiontakesatleasttwo', 'qtype_randomdata', $regs[2]);
                }
                break;

            default:
                return get_string('unsupportedformulafunction', 'qtype_randomdata', $regs[2]);
        }

        // Exchange the function call with '1.0' and then check for another function call.
        if ($regs[1]) {
            // The function call is proceeded by an operator.
            $formula = str_replace($regs[0], $regs[1] . '1.0', $formula);
        } else {
            // The function call starts the formula.
            $formula = preg_replace('~^' . preg_quote($regs[2], '~') . '\([^)]*\)~', '1.0', $formula);
        }
    }

    if (preg_match("~[^{$safeoperatorchar}.0-9eE]+~", $formula, $regs)) {
        return get_string('illegalformulasyntax', 'qtype_randomdata', $regs[0]);
    } else {
        // Formula just might be valid.
        return false;
    }
}


/**
 * Validate all the forumulas in a bit of text.
 * @param string $text the text in which to validate the formulas.
 * @return string|boolean false if there are no problems. Otherwise a string error message.
 */
function qtype_randomdata_find_formula_errors_in_text($text) {

    $formulas = question_bank::get_qtype('randomdata')->find_formulas($text);
    $errors = array();
    foreach ($formulas as $match) {
        $error = qtype_randomdata_find_formula_errors($match);
        if ($error) {
            $errors[] = $error;
        }
    }

    if ($errors) {
        return implode(' ', $errors);
    }

    return false;
}