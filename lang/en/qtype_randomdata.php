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
 * Strings for component 'qtype_randomdata', language 'en' (English), branch 'MOODLE_20_STABLE'.
 *
 * @package    qtype randomdata
 * @subpackage randomdata
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


$string['add'] = 'Data Generation';
$string['additem'] = 'Number of answers to generate';
$string['addmoreanswerblanks'] = 'Add another answer blank.';
$string['addmorechoiceblanks'] = 'Add another answer blank.';
$string['addmorechoicevalidblanks'] = 'Add another intermediate validation';
$string['addsets'] = 'Add set(s)';
$string['affirmation'] = 'Yes';
$string['answerformula'] = 'Answer  {$a} formula';
$string['answerdisplay'] = 'Answer display';
$string['answerhdr'] = 'Answer';
$string['answerstoleranceparam'] = 'Check tolerance parameters';
$string['answervalidationhdr'] = 'Validation of intermediate formulas';
$string['answerwithtolerance'] = '{$a->answer} (±{$a->tolerance} {$a->tolerancetype})';
$string['anyvalue'] = 'Any value';
$string['atleastoneanswer'] = 'You need to provide at least one answer.';
$string['atleastonerealdataset'] = 'There should be at least one real dataset in question text';
$string['atleastonewildcard'] = 'There should be at least one wild card in answer formula or question text';
$string['calcdistribution'] = 'Distribution';
$string['calclength'] = 'Decimal places';
$string['calcmax'] = 'Maximum';
$string['calcmin'] = 'Minimum';
$string['chart_title'] = 'Distribution of results';
$string['choosedatasetproperties'] = 'Wildcards dataset properties';
$string['choosedatasetproperties_help'] = 'A dataset is a set of values inserted in place of a wildcard. You can create a private dataset for a specific question, or a shared dataset that can be used for other random data questions within the category.';
$string['coef_result'] = 'Coefficient of variation: <strong>{$a}</strong>';
$string['correctanswerformula'] = 'Correct answer formula';
$string['correctanswershows'] = 'Correct answer shows';
$string['correctanswershowsformat'] = 'Format';
$string['correctfeedback'] = 'For any correct response';
$string['dataitemdefined'] = 'with {$a} numerical values already defined is available';
$string['datasetrole'] = ' The wild cards <strong>{x..}</strong> will be substituted by a numerical value from their dataset';
$string['decimals'] = 'with {$a}';
$string['deletebtninfo'] = 'generated data';
$string['deleteinfo'] = 'If you are not satisfied with the information generated, please press "Delete" button below. Then, reselect the number of evaluations you want to generate and press "Generate Data" button.';
$string['deleteitem'] = 'Delete item';
$string['deletelastitem'] = 'Delete last item';
$string['des_result'] = 'Standard deviation: <strong>{$a}</strong>';
$string['distribution'] = 'Distribution: <strong>{$a}</strong>';
$string['distributionoption'] = 'Select distribution option';
$string['editdatasets'] = 'Edit the wildcards datasets';
$string['editdatasets_help'] = 'Wildcard values may be created by entering a number in each wild card field then clicking the add button. To automatically generate 10 or more values, select the number of values required before clicking the add button. A uniform distribution means any value between the limits is equally likely to be generated; a loguniform distribution means that values towards the lower limit are more likely.';
$string['editdatasets_link'] = 'question/type/randomdata';
$string['errormodif'] = 'Error: the formula can not be modified. Please put back the original formula.';
$string['errornumbers'] = '<strong>Warning:</strong> The desired number of evaluations could not be generated. Please delete the generated data and try the data again, reducing the number of evaluations or modifying the ranges of the variables.';
$string['exam'] = 'Example of an exercise of the generated data:';
$string['exampletolerance'] = 'Example of tolerance to apply';
$string['existingcategory1'] = 'will use an already existing shared dataset';
$string['existingcategory2'] = 'a file from an already existing set of files that are also used by other questions in this category';
$string['existingcategory3'] = 'a link from an already existing set of links that are also used by other questions in this category';
$string['forceregeneration'] = 'force regeneration';
$string['forceregenerationall'] = 'forceregeneration of all wildcards';
$string['forceregenerationshared'] = 'forceregeneration of only non-shared wildcards';
$string['functiontakesatleasttwo'] = 'The function {$a} must have at least two arguments';
$string['functiontakesnoargs'] = 'The function {$a} does not take any arguments';
$string['functiontakesonearg'] = 'The function {$a} must have exactly one argument';
$string['functiontakesoneortwoargs'] = 'The function {$a} must have either one or two arguments';
$string['functiontakestwoargs'] = 'The function {$a} must have exactly two arguments';
$string['generatedata'] = 'Generate data';
$string['generatedataheader'] = 'Generation of the desired amount of data';
$string['generatevalue'] = 'Generate a new value between';
$string['getnextnow'] = 'Get new \'Item to Add\' now';
$string['hexanotallowed'] = 'Dataset <strong>{$a->name}</strong> hexadecimal format value {$a->value} is not allowed';
$string['illegalformulasyntax'] = 'Illegal formula syntax starting with \'{$a}\'';
$string['incorrectfeedback'] = 'For any incorrect response';
$string['info'] = '<strong>Important information:</strong> if you are not satisfied with the scattered results obtained below, go back to the "Data generation" section, select the number of evaluations you want, and press "Generate data" button again.';
$string['item(s)'] = 'item(s)';
$string['itemno'] = 'Item {$a}';
$string['itemscount'] = 'Items<br />Count';
$string['itemtoadd'] = 'Item to add';
$string['keptcategory1'] = 'will use the same existing shared dataset as before';
$string['keptcategory2'] = 'a file from the same category reusable set of files as before';
$string['keptcategory3'] = 'a link from the same category reusable set of links as before';
$string['keptlocal1'] = 'will use the same existing private dataset as before';
$string['keptlocal2'] = 'a file from the same question private set of files as before';
$string['keptlocal3'] = 'a link from the same question private set of links as before';
$string['label_x'] = 'Results Range';
$string['label_y'] = 'Number of results';
$string['lastitem(s)'] = 'last items(s)';
$string['lengthoption'] = 'Select length option';
$string['loguniform'] = 'Loguniform';
$string['Log-Uniform'] = 'Log-Uniform';
$string['loguniformbit'] = 'digits, from a loguniform distribution';
$string['makecopynextpage'] = 'Next page (new question)';
$string['mandatoryhdr'] = 'Mandatory wild cards present in answers';
$string['max'] = 'Max';
$string['max_result'] = 'Maximum result: <strong>{$a}</strong>';
$string['min'] = 'Min';
$string['minmax'] = 'Range of Values';
$string['minmaxformula'] = 'Range of Values in the formula {$a}';
$string['missingformula'] = 'Missing formula';
$string['missingname'] = 'Missing question name';
$string['missingquestiontext'] = 'Missing question text';
$string['min_result'] = 'Minimum result: <strong>{$a}</strong>';
$string['modifanswers'] = '<strong>Important information:</strong> the formula(s) presented below should not be modified, only the sections: qualification, tolerance, response operations, and feedback can be changed.';
$string['msgerrorintermediatevalidation'] = 'Error in intermediate formula: this intermediate formula to validate is not found in a general formula';
$string['mustenteraformulaorstar'] = 'You must enter a formula or \'*\'.';
$string['negation'] = 'No';
$string['newcategory1'] = 'will use a new shared dataset';
$string['newcategory2'] = 'a file from a new set of files that may also be used by other questions in this category';
$string['newcategory3'] = 'a link from a new set of links that may also be used by other questions in this category';
$string['newlocal1'] = 'will use a new private dataset';
$string['newlocal2'] = 'a file  from a new set of files that will only be used by this question';
$string['newlocal3'] = 'a link from a new set of links that will only be used by this question';
$string['nextitemtoadd'] = 'Variable configuration';
$string['nextpage'] = 'Next page';
$string['nocoherencequestionsdatyasetcategory'] = 'For question id {$a->qid}, the category id {$a->qcat} is not identical with the shared wild card {$a->name} category id {$a->sharedcat}. Edit the question.';
$string['nocommaallowed'] = 'The , cannot be used, use . as in 0.013 or 1.3e-2';
$string['nodataset'] = 'it is not a variable, it is part of the question text';
$string['Normal'] = 'Normal';
$string['nosharedwildcard'] = 'No shared wild card in this category';
$string['notvalidnumber'] = 'Wild card value is not a valid number ';
$string['numberofvariables'] = 'Amount of evaluations';
$string['oneanswertrueansweroutsidelimits'] = '<strong>ERROR RELATED TO TOLERANCE:</strong><br />At least one correct answer outside the limits of the actual value of the tolerance.<br /><br />Modify the available answer tolerance settings as advanced parameters in the "Tolerance example" section found in one of the previous sections.';
$string['param'] = 'Param {<strong>{$a}</strong>}';
$string['partiallycorrectfeedback'] = 'For any partially correct response';
$string['pluginname'] = 'Scattered responses (Random Data)';
$string['pluginname_help'] = 'Random Data questions enable individual numerical questions to be created using wildcards in curly brackets that are substituted with individual values when the quiz is taken. For example, the question "What is the area of a rectangle of length {l} and width {w}?" would have correct answer formula "{l}*{w}" (where * denotes multiplication).';
$string['pluginname_link'] = 'question/type/randomdata';
$string['pluginnameadding'] = 'Adding a Random Data question';
$string['pluginnameediting'] = 'Editing a Random Data question';
$string['pluginnamesummary'] = 'Random data questions are numerical questions with variables where the numbers used are selected at random, complying with the given restrictions and generated with a distribution that provides sufficiently disperse results to ensure that the answers to each questionnaire are different.';
$string['privacy:metadata'] = 'The Random Data question type plugin does not store any personal data.';
$string['possiblehdr'] = 'Possible wild cards present only in the question text';
$string['questiondatasets'] = 'Question datasets';
$string['questiondatasets_help'] = 'Question datasets of wild cards that will be used in each individual question';
$string['questionstoredname'] = 'Question stored name';
$string['range_result'] = 'Results Range: <strong>{$a}</strong>';
$string['replacewithrandom'] = 'Replace with a random value';
$string['reuseifpossible'] = 'reuse previous value if available';
$string['sharedwildcard'] = 'Shared wild card {<strong>{$a}</strong>}';
$string['sharedwildcardname'] = 'Shared wild card ';
$string['sharedwildcards'] = 'Shared wild cards';
$string['significantfigures'] = 'with {$a}';
$string['significantfiguresformat'] = 'significant figures';
$string['synchronize'] = 'Data from datasets shared with other questions in a quiz will not be synced';
$string['synchronizeno'] = 'Do not synchronise';
$string['synchronizeyes'] = 'Synchronise';
$string['synchronizeyesdisplay'] = 'Synchronise and display the shared datasets name as prefix of the question name';
$string['tittleconditions'] = 'Validity conditions in the above formula';
$string['tolerance'] = 'Tolerance ±';
$string['tolerancetype'] = 'Type';
$string['Triangle'] = 'Triangle';
$string['trueanswerinsidelimits'] = 'Correct answer : {$a->correct}<br>Within limits: {$a->true}';
$string['trueansweroutsidelimits'] = '<span class="error">ERROR Correct answer : {$a->correct} outside limits of true value {$a->true}</span>';
$string['uniform'] = 'Uniform';
$string['Uniform'] = 'Uniform';
$string['uniformbit'] = 'decimals, from a uniform distribution';
$string['updatecategory'] = 'Update the category';
$string['updatedatasetparam'] = 'Update the datasets parameters';
$string['updatetolerancesparam'] = 'Update the answers tolerance parameters';
$string['usedinquestion'] = 'Used in Question';
$string['youmustaddatleastoneitem'] = 'You must add at least one dataset item before you can save this question.';
$string['youmustaddatleastonevalue'] = 'You must add at least one set of wild card(s) values before you can save this question.';
$string['newsetwildcardvalues'] = 'new set(s) of wild card(s) values';
$string['setno'] = 'Information of the {$a} generated data';
$string['setwildcardvalues'] = 'set(s) of wild card(s) values';
$string['showitems'] = 'Display';
$string['updatewildcardvalues'] = 'Update the wild card(s) values';
$string['unsupportedformulafunction'] = 'The function {$a} is not supported';
$string['useadvance'] = 'Use the advance button to see the errors';
$string['validationceroformula'] = 'Validation of zero in the formula {$a}';
$string['validationnegative'] = 'Can the result be negative?';
$string['validationnegativeformula'] = 'Validation of negative result in the formula {$a}';
$string['validationpositive'] = 'Can the result be positive?';
$string['validationpositiveformula'] = 'Validation of positive result in the formula {$a}';
$string['validationzero'] = 'Can the result be equal to zero?';
$string['var_result'] = 'Variance: <strong>{$a}</strong>';
$string['wildcard'] = 'Wild card {<strong>{$a}</strong>}';
$string['wildcardparam'] = 'Wild cards parameters used to generate the values';
$string['wildcardrole'] = 'The wild cards <strong>{x..}</strong> will be substituted by a numerical value from the generated values';
$string['wildcards'] = 'Wild cards {a}...{z}';
$string['wildcardvalues'] = 'Wild card(s) values';
$string['wildcardvaluesgenerated'] = 'Wild card(s) values generated';
$string['zerosignificantfiguresnotallowed'] = 'The correct answer cannot have zero significant figures!';