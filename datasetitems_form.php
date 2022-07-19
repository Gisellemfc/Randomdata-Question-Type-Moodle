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
 * Defines the editing form for the randomdata question data set items.
 * In this PHP file all the elements for the third page of the plugin are defined.
 *
 * @package    qtype randomdata
 * @subpackage randomdata
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/question/type/edit_question_form.php');


/**
 * Randomdata question data set items editing form definition.
 *
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_dataset_dependent_items_form extends question_wizard_form {
    /**
     * Question object with options and answers already loaded by get_question_options.
     *
     * @var object
     */
    public $question;
    /**
     * Reference to question type object.
     *
     * @var question_dataset_dependent_questiontype
     */
    public $qtypeobj;

    /** @var stdClass the question category. */
    protected $category;

    /** @var context the context of the question category. */
    protected $categorycontext;


    public $datasetdefs;
    public $maxnumber = -1;
    public $regenerate;
    public $noofitems;
    public $outsidelimit = false;
    public $commentanswers = array();


    /**
     * Add question-type specific form fields.
     *
     * @param MoodleQuickForm $mform the form being built.
     */


    // Construct.
    public function __construct($submiturl, $question, $regenerate) {

        global $SESSION, $CFG, $DB;
        $this->regenerate = $regenerate;
        $this->question = $question;
        $this->qtypeobj = question_bank::get_qtype($this->question->qtype);
        // Validate the question category.
        if (!$category = $DB->get_record('question_categories',
                array('id' => $question->category))) {
            print_error('categorydoesnotexist', 'question', $returnurl);
        }
        $this->category = $category;
        $this->categorycontext = context::instance_by_id($category->contextid);
        // Get the dataset defintions for the question.
        if (empty($question->id)) {
            $this->datasetdefs = $this->qtypeobj->get_dataset_definitions(
                    $question->id, $SESSION->randomdata->definitionform->dataset);
        } else {
            if (empty($question->options)) {
                $this->get_question_options($question);
            }
            $this->datasetdefs = $this->qtypeobj->get_dataset_definitions(
                    $question->id, array());
        }

        foreach ($this->datasetdefs as $datasetdef) {
            // Get maxnumber.
            if ($this->maxnumber == -1 || $datasetdef->itemcount < $this->maxnumber) {
                $this->maxnumber = $datasetdef->itemcount;
            }
        }
        foreach ($this->datasetdefs as $defid => $datasetdef) {
            if (isset($datasetdef->id)) {
                $this->datasetdefs[$defid]->items =
                        $this->qtypeobj->get_database_dataset_items($datasetdef->id);
            }
        }
        parent::__construct($submiturl);

    }


    /**
     * Function of definition of the third page of the form.
     * 
     */
    protected function definition() {

        global $PAGE;

        $mform = $this->_form;
        $mform->setDisableShortforms();
        $strquestionlabel = $this->qtypeobj->comment_header($this->question);

        if ($this->maxnumber != -1 ) {
            $this->noofitems = $this->maxnumber;
        } else {
            $this->noofitems = 0;
        }
        
        // Space where constraints for variables are managed.
        $mform->addElement('header', 'additemhdr',
                get_string('itemtoadd', 'qtype_randomdata'));

        $idx = 1;
        $j = (($this->noofitems) * count($this->datasetdefs))+1;

        foreach ($this->datasetdefs as $defkey => $datasetdef) {

            if ($datasetdef->category |= 0 ) {
                $name = get_string('sharedwildcard', 'qtype_randomdata', $datasetdef->name);
            } else {
                $name = get_string('wildcard', 'qtype_randomdata', $datasetdef->name);
            }

            // Name of wildcards.
            $mform->addElement('static', $j, $name);

            // Generation of the fields for the constraints.
            $this->qtypeobj->custom_generator_tools_part($mform, $idx, $j);
            $idx++;

            $mform->addElement('hidden', "definition[{$j}]");
            $mform->setType("definition[{$j}]", PARAM_RAW);
            $mform->addElement('hidden', "itemid[{$j}]");
            $mform->setType("itemid[{$j}]", PARAM_RAW);
            $mform->addElement('static', "divider[{$j}]", '', '<hr />');
            $mform->setType("divider[{$j}]", PARAM_RAW);
            $j++;

        }

        // Space showing an example of tolerance for the proposed exercise.
        $mform->addElement('header', 'additemhdr', get_string('exampletolerance', 'qtype_randomdata'));
        $answers = fullclone($this->question->options->answers);
        $key1 =1;

        foreach ($answers as $key => $answer) {

            $ans = shorten_text($answer->answer, 17, true);
           
            if ($ans === '*') {
                $mform->addElement('static',
                        'answercomment[' . ($this->noofitems+$key1) . ']', $ans);
                $mform->addElement('hidden', 'tolerance['.$key.']', '');
                $mform->setType('tolerance['.$key.']', PARAM_FLOAT);
                $mform->setAdvanced('tolerance['.$key.']', true);
                $mform->addElement('hidden', 'tolerancetype['.$key.']', '');
                $mform->setType('tolerancetype['.$key.']', PARAM_RAW);
                $mform->setAdvanced('tolerancetype['.$key.']', true);
                $mform->addElement('hidden', 'correctanswerlength['.$key.']', '');
                $mform->setType('correctanswerlength['.$key.']', PARAM_RAW);
                $mform->setAdvanced('correctanswerlength['.$key.']', true);
                $mform->addElement('hidden', 'correctanswerformat['.$key.']', '');
                $mform->setType('correctanswerformat['.$key.']', PARAM_RAW);
                $mform->setAdvanced('correctanswerformat['.$key.']', true);
            } else if ( $ans !== '' ) {
                $mform->addElement('static', 'answercomment[' . ($this->noofitems+$key1) . ']', $ans);
                $mform->addElement('float', 'tolerance['.$key.']', get_string('tolerance', 'qtype_randomdata'));
                $mform->setAdvanced('tolerance['.$key.']', true);
                $mform->addElement('select', 'tolerancetype['.$key.']',get_string('tolerancetype', 'qtype_numerical'), $this->qtypeobj->tolerance_types());
                $mform->setAdvanced('tolerancetype['.$key.']', true);
                $mform->addElement('select', 'correctanswerlength['.$key.']', get_string('correctanswershows', 'qtype_randomdata'), range(0, 9));
                $mform->setAdvanced('correctanswerlength['.$key.']', true);
                $answerlengthformats = array(
                    '1' => get_string('decimalformat', 'qtype_numerical'),
                    '2' => get_string('significantfiguresformat', 'qtype_randomdata')
                );
                $mform->addElement('select', 'correctanswerformat['.$key.']', get_string('correctanswershowsformat', 'qtype_randomdata'), $answerlengthformats);
                $mform->setAdvanced('correctanswerformat['.$key.']', true);
                $mform->addElement('static', 'dividertolerance', '', '<hr />');
                $mform->setAdvanced('dividertolerance', true);
            }
            $key1++;

        }
          
        // Button that allows modify the selected tolerance, after seeing the example.
        $mform->addElement('submit', 'updateanswers',
                get_string('updatetolerancesparam', 'qtype_randomdata'));
        $mform->setAdvanced('updateanswers', true);
        $mform->registerNoSubmitButton('updateanswers');

        // Options for the number of evaluations to generate.
        $addremoveoptions = array();
        for ($i=10; $i<=50; $i+=10) {
             $addremoveoptions["{$i}"] = "{$i}";
        }

        // Generate section.
        $mform->addElement('header', 'addhdr', get_string('add', 'qtype_randomdata'));
        
        if($this->noofitems == 0){

            $addgrp = array();
            $addgrp[] =& $mform->createElement('select', "selectadd", get_string('numberofvariables', 'qtype_randomdata'), $addremoveoptions);
            $addgrp[] =& $mform->createElement('submit', 'addbutton', get_string('generatedata', 'qtype_randomdata'));
            $mform->addGroup($addgrp, 'addgrp', get_string('additem', 'qtype_randomdata'), ' ', false);
            
        }
        
        // 
        // Below is the section created after generating the desired number of evaluations.
        // 

        $j = $this->noofitems * count($this->datasetdefs);
        $k = optional_param('selectshow', 1, PARAM_INT);
        
        for ($i = $this->noofitems; $i >= 1; $i--) {
            
            if ($k > 0) {
                $mform->addElement('header', 'setnoheader' . $this->noofitems, "<b>" .
                get_string('setno', 'qtype_randomdata', $this->noofitems)."</b>&nbsp;&nbsp;");
                $example = '<p>' . get_string('exam', 'qtype_randomdata') . '</p>';
                $mform->addElement('html', $example);
            }
            
        //    Space where an example of the created exercise is shown.
            foreach ($this->datasetdefs as $defkey => $datasetdef) {
                    
                    $mform->addElement('hidden', "number[{$j}]" , '');
                    $mform->setType("number[{$j}]", PARAM_LOCALISEDFLOAT);
                
                    $mform->addElement('hidden', "itemid[{$j}]");
                    $mform->setType("itemid[{$j}]", PARAM_INT);
    
                    $mform->addElement('hidden', "definition[{$j}]");
                    $mform->setType("definition[{$j}]", PARAM_NOTAGS);
                    $data[$datasetdef->name] =$datasetdef->items[$i]->value;
                    
                    $j--;
                    
                }
    
                if ('' != $strquestionlabel && ($k > 0 )) {

                    $repeated[] = $mform->addElement('static', "answercomment[{$i}]", $strquestionlabel);
                    $qtext = $this->qtypeobj->substitute_variables($this->question->questiontext, $data);
                    $textequations = $this->qtypeobj->find_formulas($qtext);
                    if ($textequations != '' && count($textequations) > 0 ) {

                        $mform->addElement('static', "divider1[{$j}]", '','Formulas {=..} in question text');
                       
                        foreach ($textequations as $key => $equation) {
                            if ($formulaerrors = qtype_calculated_find_formula_errors($equation)) {
                                $str = $formulaerrors;
                            } else {
                                eval('$str = '.$equation.';');
                            }
                            $equation = shorten_text($equation, 17, true);
                            $mform->addElement('static', "textequation", "{={$equation}}", "=".$str);

                        }
                    }
    
                }

                $k--;

        }

       // To see if assessments were created. If so, they show the characteristics of the generated data.
        if ($this->noofitems > 0) {

            $mform->addElement('static', 'outsidelimit', '', '');

            // Obtaining information of the selected distribution.
            $distribution_info = $this->get_info_results();
            
            if($distribution_info['complete'] == 0){
                $incomplete = '<p>' . get_string('errornumbers', 'qtype_randomdata'). '</p>';
                $mform->addElement('html', $incomplete);
            }

            // Showing information of interest.
            $distribution = '<p>' . get_string('distribution', 'qtype_randomdata', get_string($distribution_info['distribution'], 'qtype_randomdata')). '</p>';
            $min = '<p>' . get_string('min_result', 'qtype_randomdata', $distribution_info['min']). '</p>';
            $max = '<p>' . get_string('max_result', 'qtype_randomdata', $distribution_info['max']). '</p>';
            $range = '<p>' . get_string('range_result', 'qtype_randomdata', $distribution_info['range']). '</p>';
            $var = '<p>' . get_string('var_result', 'qtype_randomdata', $distribution_info['var']). '</p>';
            $des = '<p>' . get_string('des_result', 'qtype_randomdata', $distribution_info['des']). '</p>';
            $coef = '<p>' . get_string('coef_result', 'qtype_randomdata', $distribution_info['coef']). '</p>';
            
            $mform->addElement('html', $distribution);
            $mform->addElement('html', $min);
            $mform->addElement('html', $max);
            $mform->addElement('html', $range);
            $mform->addElement('html', $var);
            $mform->addElement('html', $des);
            $mform->addElement('html', $coef);

            // Bar graphic.
            $chart = $this->get_chart_bar($distribution_info);
            $output = $PAGE->get_renderer('qtype_randomdata');
            $mform->addElement('html', $output->render($chart, false));
        }
        
        $addremoveoptions = array();
        $addremoveoptions['1']=$this->noofitems;
        
        // If evaluations were created, show section to delete evaluations if you are not satisfied.
        if ($this->noofitems > 0) {
            
            $mform->addElement('header', 'deleteitemhdr', get_string('delete', 'moodle'));
            $deleteinfo = '<p>' . get_string('deleteinfo', 'qtype_randomdata') . '</p>';
            $mform->addElement('html', $deleteinfo);
            $deletegrp = array();
            $deletegrp[] = $mform->createElement('submit', 'deletebutton', get_string('delete', 'moodle'));
            $deletegrp[] = $mform->createElement('select', 'selectdelete', '', $addremoveoptions);
            $deletegrp[] = $mform->createElement('static', "stat", "Items", get_string('deletebtninfo', 'qtype_randomdata'));
            $mform->addGroup($deletegrp, 'deletegrp', '', '   ', false);

        } else {

            $mform->addElement('static', 'warning', '', '<span class="error">' . get_string('youmustaddatleastoneitem', 'qtype_calculated').'</span>');

        }

        // If evaluations were created, show submit buttons.
        if ($this->noofitems > 0) {

            $buttonarray = array();
            $buttonarray[] = $mform->createElement('submit', 'savechanges', get_string('savechanges'));
            $previewlink = $PAGE->get_renderer('core_question')->question_preview_link($this->question->id, $this->categorycontext, true);
            $buttonarray[] = $mform->createElement('static', 'previewlink', '', $previewlink);
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');

        }

        $this->add_hidden_fields();
        $mform->addElement('hidden', 'category');
        $mform->setType('category', PARAM_SEQUENCE);
        $mform->addElement('hidden', 'wizard', 'datasetitems');
        $mform->setType('wizard', PARAM_ALPHA);

    }


    /**
     * Function to obtain the information of the evaluation results.
     * 
     * @return array of distribution results information.
     */
    public function get_info_results(){

        global $DB;

        // Getting the calculated results.
        $results = $DB->get_records('question_randomdata_results', array('question' => $this->question->id));
        $array_results = [];
        $array_results['complete'] = 1;
        $i = 0;

        foreach($results as $res){

            if($res->result != 'Error'){
                $array_results['results'][$i] = $res->result;
                $array_results['distribution'] = $res->distribution;
                $i++;
            }else{
                $array_results['complete'] = 0;
            }
            
        }

        // Number of results generated.
        $array_results['number_results'] =$i;

        // Information regarding to results obtained by the distribution of the generated data
        $array_results['min'] = min($array_results['results']);
        $array_results['max'] = max($array_results['results']);
        $array_results['range'] = $array_results['max'] - $array_results['min'];

        $sum=0;

        for($i=0;$i<count($array_results['results']);$i++){
            $sum+=$array_results['results'][$i];
        }
        $media = $sum/count($array_results['results']);

        $sum2=0;

        for($i=0;$i<count($array_results['results']);$i++){
            $sum2+=($array_results['results'][$i]-$media)*($array_results['results'][$i]-$media);
        }
        $array_results['var'] = round($sum2/count($array_results['results']), 2);
        $array_results['des'] = round(sqrt($array_results['var']), 2);
        $array_results['coef'] = round($array_results['des'] / $media, 2);

        return $array_results;

    }


    /**
     * Function that obtains the bar graph of the generated results.
     * 
     * @param $distribution reference to array of information of distribution selected.
     * @return chart of results.
     */
    public function get_chart_bar($distribution){

        // Bar Chart configuration.
        $chart = new \core\chart_bar();
        $chart->set_title(get_string('chart_title', 'qtype_randomdata'));
        $chart->get_xaxis(0, true)->set_label(get_string('label_x', 'qtype_randomdata')); 
        $chart->get_yaxis(0, true)->set_label(get_string('label_y', 'qtype_randomdata'));

        // Labels range. Depends on the number of evaluations generated.
        $ranges = [];

        // One assessment generated.
        if($distribution['number_results'] == 1){

            $ranges = [strval(round($distribution['min'], 2))];

        // Up to 30 evaluations generated.
        } elseif ($distribution['number_results'] <= 30){

            $sumador = $distribution['range'] / 4; 
            $sig = 0;

            for($i = 0; $i < 4; $i++){
                
                if($i == 0){
                    $sig = $distribution['min'] + $sumador;
                    $ranges[$i] = strval(round($distribution['min'], 2)) . ' - ' . strval(round($sig, 2));
                }elseif($i < 3){
                    $ranges[$i] = strval(round($sig, 2)) . ' - ' . strval(round($sig + $sumador, 2));
                    $sig = $sig + $sumador;
                }else{
                    $ranges[$i] = strval(round($sig, 2)) . ' - ' . strval(round($distribution['max'], 2));
                }
            
            }

        // Up to 60 evaluations generated.
        }elseif ($distribution['number_results'] <= 60){

            $sumador = $distribution['range'] / 5; 
            $sig = 0;

            for($i = 0; $i < 5; $i++){
                
                if($i == 0){
                    $sig = $distribution['min'] + $sumador;
                    $ranges[$i] = strval(round($distribution['min'],2)) . ' - ' . strval(round($sig,2));
                }elseif($i < 4){
                    $ranges[$i] = strval(round($sig,2)) . ' - ' . strval(round($sig + $sumador,2));
                    $sig = $sig + $sumador;
                }else{
                    $ranges[$i] = strval(round($sig,2)) . ' - ' . strval(round($distribution['max'],2));
                }
            
            }

        // Another number of evaluations generated.
        }else{

            $sumador = $distribution['range'] / 6; 
            $sig = 0;

            for($i = 0; $i < 6; $i++){
                
                if($i == 0){
                    $sig = $distribution['min'] + $sumador;
                    $ranges[$i] = strval(round($distribution['min'],2)) . ' - ' . strval(round($sig,2));
                }elseif($i < 5){
                    $ranges[$i] = strval(round($sig,2)) . ' - ' . strval(round($sig + $sumador,2));
                    $sig = $sig + $sumador;
                }else{
                    $ranges[$i] = strval(round($sig,2)) . ' - ' . strval(round($distribution['max'],2));
                }
            
            }            
            
        }
        
        // Counting the number of results.
        $count_results = [];
        $i = 0;

        foreach($ranges as $range){

            $r = explode(" - ", $range);

            if($i == 0){
                $count_results[$i] = $this->countInRange($distribution['results'], $r[0], $r[1], true);
            }else{
                $count_results[$i] = $this->countInRange($distribution['results'], $r[0], $r[1], false);
            }

            $i++;

        }
        
        // Generating the bar chart.
        $serie  =  new  core\chart_series ('Cantidad de Resultados' ,  $count_results);
        $chart->add_series($serie);
        $chart->set_labels($ranges);
        
        return $chart;

    }


    /**
     * Function that obtains the number of evaluation results within a range.
     * 
     * @param $results results obtained by the distribution.
     * @param $min minimum number of results.
     * @param $max maximum number of results.
     * @param $is_start flag.
     * @return count number of results in that range.
     */
    public function countInRange($results, $min, $max, $is_start) {
        if($is_start){
            return count(array_filter($results, function($e) use($min,$max) {return ($e>=$min && $e<=$max);}));
        }else{
            return count(array_filter($results, function($e) use($min,$max) {return ($e>$min && $e<=$max);}));
        }
    }


    /**
     * Function that set data.
     * 
     * @param $question question being worked on in the form.
     */
    public function set_data($question) {

        $formdata = array();
        $fromform = new stdClass();

        // Space to update tolerance, if desired.
        if (isset($question->options)) {

            $answers = $question->options->answers;

            if (count($answers)) {

                if (optional_param('updateanswers', false, PARAM_BOOL) || optional_param('updatedatasets', false, PARAM_BOOL)) {
                    
                    foreach ($answers as $key => $answer) {

                        $fromform->tolerance[$key]= $this->_form->getElementValue('tolerance['.$key.']');
                        $answer->tolerance = $fromform->tolerance[$key];
                        $fromform->tolerancetype[$key]= $this->_form->getElementValue('tolerancetype['.$key.']');
                        if (is_array($fromform->tolerancetype[$key])) {

                            $fromform->tolerancetype[$key]= $fromform->tolerancetype[$key][0];

                        }
                        $answer->tolerancetype = $fromform->tolerancetype[$key];
                        $fromform->correctanswerlength[$key]= $this->_form->getElementValue('correctanswerlength['.$key.']');
                        if (is_array($fromform->correctanswerlength[$key])) {

                            $fromform->correctanswerlength[$key] = $fromform->correctanswerlength[$key][0];

                        }
                        $answer->correctanswerlength = $fromform->correctanswerlength[$key];
                        $fromform->correctanswerformat[$key] = $this->_form->getElementValue('correctanswerformat['.$key.']');
                        if (is_array($fromform->correctanswerformat[$key])) {

                            $fromform->correctanswerformat[$key] = $fromform->correctanswerformat[$key][0];

                        }
                        $answer->correctanswerformat = $fromform->correctanswerformat[$key];

                    }

                    $this->qtypeobj->save_question_randomdata($question, $fromform);

                } else {

                    foreach ($answers as $key => $answer) {

                        $formdata['tolerance['.$key.']'] = $answer->tolerance;
                        $formdata['tolerancetype['.$key.']'] = $answer->tolerancetype;
                        $formdata['correctanswerlength['.$key.']'] = $answer->correctanswerlength;
                        $formdata['correctanswerformat['.$key.']'] = $answer->correctanswerformat;

                    }

                }
            }
        }

        // Fill out all data sets and also the fields for the next item to add.
        $j = $this->noofitems * count($this->datasetdefs);

        for ($itemnumber = $this->noofitems; $itemnumber >= 1; $itemnumber--) {

            $data = array();
            foreach ($this->datasetdefs as $defid => $datasetdef) {

                if (isset($datasetdef->items[$itemnumber])) {
                    
                    $value = $datasetdef->items[$itemnumber]->value;
                    if ($this->_form->getElementType("number[{$j}]") == 'hidden') {
                        $value = format_float($value, -1);
                    }
                    $formdata["number[{$j}]"] = $value;
                    $formdata["definition[{$j}]"] = $defid;
                    $formdata["itemid[{$j}]"] = $datasetdef->items[$itemnumber]->id;
                    $data[$datasetdef->name] = $datasetdef->items[$itemnumber]->value;
                }

                $j--;
            }

            $comment = $this->qtypeobj->comment_on_datasetitems($this->qtypeobj, $question->id, $question->questiontext, $answers, $data, $itemnumber);
            if ($comment->outsidelimit) {
                $this->outsidelimit=$comment->outsidelimit;
            }
            $totalcomment='';
            foreach ($question->options->answers as $key => $answer) {
                $totalcomment .= $comment->stranswers[$key].'<br/>';
            }
            $formdata['answercomment['.$itemnumber.']'] = $totalcomment;
        }

        $formdata['nextpageparam[forceregeneration]'] = 2;
        $formdata['selectdelete'] = '1';
        $formdata['selectadd'] = '1';
        $j = $this->noofitems * count($this->datasetdefs)+1;
        $data = array(); // Data for comment_on_datasetitems later.

        // Dataset generation defaults.
        if ($this->qtypeobj->supports_dataset_item_generation()) {
            $itemnumber = $this->noofitems+1;

            foreach ($this->datasetdefs as $defid => $datasetdef) {

                if (!optional_param('updatedatasets', false, PARAM_BOOL) && !optional_param('updateanswers', false, PARAM_BOOL)) {
                    $value = $this->qtypeobj->generate_dataset_item($datasetdef->options);
                } else {
                    $value = $this->_form->getElementValue("number[{$j}]");                
                }
                
                if ($this->_form->getElementType("number[{$j}]") == 'hidden') {
                    // Some of the number elements are from the float type and some are from the hidden type. 
                    // Is needed to manually handle localised floats for hidden elements.
                    $value = format_float($value, -1);
                }

                $formdata["number[{$j}]"] = $value;
                $formdata["definition[{$j}]"] = $defid;
                $formdata["itemid[{$j}]"] = isset($datasetdef->items[$itemnumber]) ? $datasetdef->items[$itemnumber]->id : 0;
                $data[$datasetdef->name] = $formdata["number[{$j}]"];
                $j++;

            }
        }

        // Existing records override generated data depending on radio element.
        $j = $this->noofitems * count($this->datasetdefs) + 1;

        if (!$this->regenerate && !optional_param('updatedatasets', false, PARAM_BOOL) && !optional_param('updateanswers', false, PARAM_BOOL)) {
           
            $itemnumber = $this->noofitems + 1;
            foreach ($this->datasetdefs as $defid => $datasetdef) {

                if (isset($datasetdef->items[$itemnumber])) {
                    $formdata["number[{$j}]"] = $datasetdef->items[$itemnumber]->value;
                    $formdata["definition[{$j}]"] = $defid;
                    $formdata["itemid[{$j}]"] = $datasetdef->items[$itemnumber]->id;
                    $data[$datasetdef->name] = $datasetdef->items[$itemnumber]->value;
                }
                $j++;

            }
        }

        $comment = $this->qtypeobj->comment_on_datasetitems($this->qtypeobj, $question->id, $question->questiontext, $answers, $data, ($this->noofitems + 1));
        
        if (isset($comment->outsidelimit) && $comment->outsidelimit) {
            $this->outsidelimit=$comment->outsidelimit;
        }

        $key1 = 1;

        foreach ($question->options->answers as $key => $answer) {
            $formdata['answercomment['.($this->noofitems+$key1).']'] = $comment->stranswers[$key];
            $key1++;
        }

        if ($this->outsidelimit) {
            $formdata['outsidelimit']= '<span class="error">' . get_string('oneanswertrueansweroutsidelimits', 'qtype_randomdata') . '</span>';
        }

        $formdata = $this->qtypeobj->custom_generator_set_data($this->datasetdefs, $formdata);

        parent::set_data((object)($formdata + (array)$question));

    }


     /**
     * Validation of the third page of the form.
     * 
     * @param $data question data.
     * @param $files.
     * @return array error obtained in the form.
     */
    public function validation($data, $files) {

        $errors = array();

        if (isset($data['savechanges']) && ($this->noofitems==0) ) {
            $errors['warning'] = get_string('warning', 'mnet');
        }

        if ($this->outsidelimit) {
            $errors['outsidelimits'] = get_string('oneanswertrueansweroutsidelimits', 'qtype_randomdata');
        }

        return $errors;
    }
}
