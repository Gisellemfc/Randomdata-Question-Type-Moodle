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
 * @package    qtype randomdata
 * @subpackage randomdata
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @copyright  Based on work by 2011 David Mudrak <david@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Random Data question type conversion handler.
 */
class moodle1_qtype_randomdata_handler extends moodle1_qtype_handler {

    /**
     * @return array
     */
    public function get_question_subpaths() {
        return array(
            'ANSWERS/ANSWER',
            'RANDOMDATA',
            'RANDOMDATA/NUMERICAL_UNITS/NUMERICAL_UNIT',
            'RANDOMDATA/DATASET_DEFINITIONS/DATASET_DEFINITION',
            'RANDOMDATA/DATASET_DEFINITIONS/DATASET_DEFINITION/DATASET_ITEMS/DATASET_ITEM'
        );
    }

    /**
     * Appends the random data specific information to the question.
     */
    public function process_question(array $data, array $raw) {

        // Convert and write the answers first.
        if (isset($data['answers'])) {
            $this->write_answers($data['answers'], $this->pluginname);
        }

        // Convert and write the numerical units and numerical options.
        if (isset($data['randomdata'][0]['numerical_units'])) {
            $numericalunits = $data['randomdata'][0]['numerical_units'];
        } else {
            $numericalunits = array();
        }
        $numericaloptions = $this->get_default_numerical_options(
                $data['oldquestiontextformat'], $numericalunits);

        $this->write_numerical_units($numericalunits);
        $this->write_numerical_options($numericaloptions);

        // Write dataset_definitions.
        if (isset($data['randomdata'][0]['dataset_definitions']['dataset_definition'])) {
            $datasetdefinitions = $data['randomdata'][0]['dataset_definitions']['dataset_definition'];
        } else {
            $datasetdefinitions = array();
        }
        $this->write_dataset_definitions($datasetdefinitions);

        // Write randomdata_records.
        $this->xmlwriter->begin_tag('randomdata_records');
        foreach ($data['randomdata'] as $randomdatarecord) {
            $record = array(
                'id'                  => $this->converter->get_nextid(),
                'answer'              => $randomdatarecord['answer'],
                'tolerance'           => $randomdatarecord['tolerance'],
                'tolerancetype'       => $randomdatarecord['tolerancetype'],
                'correctanswerlength' => $randomdatarecord['correctanswerlength'],
                'correctanswerformat' => $randomdatarecord['correctanswerformat']
            );
            $this->write_xml('randomdata_record', $record, array('/randomdata_record/id'));
        }
        $this->xmlwriter->end_tag('randomdata_records');

        // Write randomdata_options.
        $options = array(
            'calculate_option' => array(
                'id'                             => $this->converter->get_nextid(),
                'synchronize'                    => 0,
                'single'                         => 0,
                'shuffleanswers'                 => 0,
                'correctfeedback'                => null,
                'correctfeedbackformat'          => FORMAT_HTML,
                'partiallycorrectfeedback'       => null,
                'partiallycorrectfeedbackformat' => FORMAT_HTML,
                'incorrectfeedback'              => null,
                'incorrectfeedbackformat'        => FORMAT_HTML,
                'answernumbering'                => 'abc'
            )
        );
        $this->write_xml('randomdata_options', $options, array('/randomdata_options/calculate_option/id'));
    }
}
