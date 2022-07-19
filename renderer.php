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
 * Randomdata question renderer class.
 *
 * @package    qtype randomdata
 * @subpackage randomdata
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @copyright  Based on work by 2011 The Open University, 1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


require_once($CFG->dirroot . '/question/type/numerical/renderer.php');


/**
 * Generates the output for randomdata questions.
 *
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @copyright  Based on work 1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_randomdata_renderer extends qtype_numerical_renderer {

    public function correct_response(question_attempt $qa) {
        $question = $qa->get_question();
        $answer = $question->get_correct_response();
        if (!$answer) {
            return '';
        }

        $response = $answer['answer'];
        if ($question->unitdisplay != qtype_numerical::UNITNONE && $question->unitdisplay != qtype_numerical::UNITINPUT) {
            $response = $question->ap->add_unit($response);
        }

        return get_string('correctansweris', 'qtype_shortanswer', $response);
        
    }

}
