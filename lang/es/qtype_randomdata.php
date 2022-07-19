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
// You should havspanishe received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Strings for component 'qtype_randomdata', language 'es'(Spanish), branch 'MOODLE_20_STABLE'.
 *
 * @package    qtype randomdata
 * @subpackage randomdata
 * @copyright  2022 Nicole Brito nicole.brito@correo.unimet.edu.ve, Giselle Ferreira giselle.ferreira@correo.unimet.edu.ve
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['add'] = 'Generación de datos';
$string['additem'] = 'Cantidad de respuestas a generar';
$string['addmoreanswerblanks'] = 'Agregar otro espacio en blanco';
$string['addmorechoiceblanks'] = 'Agregar otra respuesta';
$string['addmorechoicevalidblanks'] = 'Agregar otra validación intermedia';
$string['addsets'] = 'Agregar conjunto(s)';
$string['affirmation'] = 'Si';
$string['answerformula'] = 'Fórmula de la respuesta {$a}';
$string['answerdisplay'] = 'Opcciones de la respuesta';
$string['answerhdr'] = 'Responder';
$string['answerstoleranceparam'] = 'Verificar parámetros de tolerancia';
$string['answervalidationhdr'] = 'Validación de fórmulas intermedias';
$string['answerwithtolerance'] = '{$a->answer} (±{$a->tolerance} {$a->tolerancetype})';
$string['anyvalue'] = 'Algún valor';
$string['atleastoneanswer'] = 'Debe proporcionar al menos una respuesta.';
$string['atleastonerealdataset'] = 'Debe haber al menos un conjunto de datos reales en el texto de la pregunta';
$string['atleastonewildcard'] = 'Debe haber al menos una variable en la fórmula de respuesta o en el texto de la pregunta';
$string['calcdistribution'] = 'Distribución';
$string['calclength'] = 'Cantidad de decimales';
$string['calcmax'] = 'Máximo';
$string['calcmin'] = 'Mínimo';
$string['chart_title'] = 'Distribución de los resultados';
$string['choosedatasetproperties'] = 'Propiedades del conjunto de datos variables';
$string['choosedatasetproperties_help'] = 'Un conjunto de datos es un conjunto de valores insertados en lugar de una variable. Puede crear un conjunto de datos privado para una pregunta específica o un conjunto de datos compartido que se puede usar para otras preguntas de datos aleatorios dentro de la categoría.';
$string['coef_result'] = 'Coeficiente de variación: <strong>{$a}</strong>';
$string['correctanswerformula'] = 'Fórmula de respuesta correcta';
$string['correctanswershows'] = 'La respuesta correcta muestra';
$string['correctanswershowsformat'] = 'Formato';
$string['correctfeedback'] = 'Para cualquier respuesta correcta';
$string['dataitemdefined'] = 'para {$a} los valores numéricos definidos están disponible';
$string['datasetrole'] = ' Las variables <strong>{x..}</strong> serán sustituidas por un valor numérico de su conjunto de datos';
$string['decimals'] = 'Para {$a}';
$string['deletebtninfo'] = 'dato(s) generado(s)';
$string['deleteinfo'] = 'Si no se encuentra satisfecho con la información generada, presione el botón "Borrar" que se encuentra a continuación. Luego, vuelva a seleccionar la cantidad de evaluaciones que desea generar y presione el botón "Generar Data".';
$string['deleteitem'] = 'Eliminar item';
$string['deletelastitem'] = 'Eliminar último item';
$string['des_result'] = 'Desviación estándar: <strong>{$a}</strong>';
$string['distribution'] = 'Distribución: <strong>{$a}</strong>';
$string['distributionoption'] = 'Seleccione la opción de distribución';
$string['editdatasets'] = 'Editar los conjuntos de datos de variables';
$string['editdatasets_help'] = 'Los valores variables se pueden crear ingresando un número en cada campo de variable y luego haciendo click en el botón Agregar. Para generar automáticamente 10 o más valores, seleccione la cantidad de valores requeridos antes de hacer click en el botón Agregar. Una distribución uniforme significa que es igualmente probable que se genere cualquier valor entre los límites; una distribución logarítmica significa que los valores hacia el límite inferior son más probables.';
$string['editdatasets_link'] = 'pregunta/tipo/datosRandoms';
$string['errormodif'] = 'Error: la fórmula no puede ser modificada. Por favor, vuelva a poner la fórmula original.';
$string['errornumbers'] = '<strong>Advertencia:</strong> No se pudo generar la cantidad de evaluaciones deseadas. Por favor, borre los datos generados y pruebe nuevamente generando los datos, disminuyendo la cantidad de evaluaciones o modificando los rangos de las variables.';
$string['exam'] = 'Ejemplo de un ejercicio de los datos generados:';
$string['exampletolerance'] = 'Ejemplo de tolerancia a aplicar';
$string['existingcategory1'] = 'utilizará un conjunto de datos compartido ya existente';
$string['existingcategory2'] = 'un archivo de un conjunto de archivos ya existentes que también son utilizados por otras preguntas en esta categoría';
$string['existingcategory3'] = 'un enlace de un conjunto de enlaces ya existentes que también son utilizados por otras preguntas en esta categoría';
$string['forceregeneration'] = 'forzar la regeneración';
$string['forceregenerationall'] = 'forzar la regeneración de todas las variables';
$string['forceregenerationshared'] = 'forzar la regeneración de solo variables no compartidas';
$string['functiontakesatleasttwo'] = 'La función {$a} debe tener al menos dos argumentos';
$string['functiontakesnoargs'] = 'La función {$a} no toma ningún argumento';
$string['functiontakesonearg'] = 'La función {$a} debe tener exactamente un argumento';
$string['functiontakesoneortwoargs'] = 'La función {$a} debe tener uno o dos argumentos';
$string['functiontakestwoargs'] = 'La función {$a} debe tener exactamente dos argumentos';
$string['generatedata'] = 'Generar data';
$string['generatedataheader'] = 'Generación de la cantidad de data deseada';
$string['generatevalue'] = 'Generar un nuevo valor entre';
$string['getnextnow'] = 'Obtener nuevo \'Elemento para agregar\' ahora';
$string['hexanotallowed'] = 'El conjunto de datos <strong>{$a->name}</strong> valor de formato hexadecimal {$a->value} no está permitido';
$string['illegalformulasyntax'] = 'Sintaxis de fórmula ilegal que comienza con \'{$a}\'';
$string['incorrectfeedback'] = 'Por cualquier respuesta incorrecta';
$string['info'] = '<strong>Información importante:</strong> si usted no se encuentra conforme con los resultados dispersos obtenidos a continuación vuelva al apartado "Generación de datos", seleccione la cantidad de evaluaciones que desea y vuelva a presionar el botón "Generar data".';
$string['item(s)'] = 'item(s)';
$string['itemno'] = 'Item {$a}';
$string['itemscount'] = 'Items<br />Recuento';
$string['itemtoadd'] = 'Variable(s) a agregar';
$string['keptcategory1'] = 'utilizará el mismo conjunto de datos compartido existente que antes';
$string['keptcategory2'] = 'un archivo de la misma categoría conjunto de archivos reutilizables que antes';
$string['keptcategory3'] = 'un enlace de la misma categoría conjunto reutilizable de enlaces que antes';
$string['keptlocal1'] = 'utilizará el mismo conjunto de datos privado existente que antes';
$string['keptlocal2'] = 'un archivo de la misma pregunta conjunto privado de archivos que antes';
$string['keptlocal3'] = 'un enlace de la misma pregunta conjunto privado de enlaces que antes';
$string['label_x'] = 'Intervalo de resultados';
$string['label_y'] = 'Cantidad de resultados';
$string['lastitem(s)'] = 'últimos items(s)';
$string['lengthoption'] = 'Seleccione la opción de longitud';
$string['loguniform'] = 'loguniforme';
$string['Log-Uniform'] = 'Log-Uniforme';
$string['loguniformbit'] = 'dígitos, de una distribución loguniforme';
$string['makecopynextpage'] = 'Página siguiente (nueva pregunta)';
$string['mandatoryhdr'] = 'Variables obligatorias presentes en las respuestas';
$string['max'] = 'Max';
$string['max_result'] = 'Resultado máximo: <strong>{$a}</strong>';
$string['min'] = 'Min';
$string['minmax'] = 'Rango de valores';
$string['minmaxformula'] = 'Rango de valores de la fórmula {$a}';
$string['min_result'] = 'Resultado mínimo: <strong>{$a}</strong>';
$string['missingformula'] = 'Fórmula faltante';
$string['missingname'] = 'Falta el nombre de la pregunta';
$string['missingquestiontext'] = 'Falta el texto de la pregunta';
$string['modifanswers'] = '<strong>Información importante:</strong> la(s) fórmula(s) que se presenta(n) a continuación no deben ser modificadas, solo se pueden cambiar los apartados: calificación, tolerancia, operaciones de la respuesta y retroalimentación.';
$string['msgerrorintermediatevalidation'] = 'Error en fórmula intermedia: esta fórmula intermedia para validar no se encuentra en una fórmula general';
$string['mustenteraformulaorstar'] = 'Debe introducir una fórmula o \'*\'.';
$string['negation'] = 'No';
$string['newcategory1'] = 'utilizará un nuevo conjunto de datos compartido';
$string['newcategory2'] = 'un archivo de un nuevo conjunto de archivos que también pueden ser utilizados por otras preguntas en esta categoría';
$string['newcategory3'] = 'un enlace de un nuevo conjunto de enlaces que también pueden ser utilizados por otras preguntas en esta categoría';
$string['newlocal1'] = 'utilizará un nuevo conjunto de datos privado';
$string['newlocal2'] = 'un archivo de un nuevo conjunto de archivos que solo será utilizado por esta pregunta';
$string['newlocal3'] = 'un enlace de un nuevo conjunto de enlaces que solo será utilizado por esta pregunta';
$string['nextitemtoadd'] = 'Configuración de variables';
$string['nextpage'] = 'Siguiente página';
$string['nocoherencequestionsdatyasetcategory'] = 'Para el id de pregunta {$a->qid}, el id de categoría {$a->qcat} no es idéntico a la variable compartida {$a->name} id de categoría {$a->sharedcat}. Edite la pregunta.';
$string['nocommaallowed'] = 'El , no se puede usar, use . como en 0.013 o 1.3e-2';
$string['nodataset'] = 'no es una variable, es parte del texto de la pregunta';
$string['Normal'] = 'Normal';
$string['nosharedwildcard'] = 'No hay variables compartidas en esta categoría';
$string['notvalidnumber'] = 'El valor de la variable no es un número válido ';
$string['numberofvariables'] = 'Cantidad de evaluaciones';
$string['oneanswertrueansweroutsidelimits'] = '<strong>ERROR CON RELACIÓN A LA TOLERANCIA:</strong><br />Al menos una respuesta correcta fuera de los límites del valor real de la tolerancia.<br /><br />Modifique la configuración de tolerancia de respuestas disponible como parámetros avanzados en la sección de "Ejemplo de tolerancia" que se encuentra en una de las secciones anteriores.';
$string['param'] = 'Parámetro {<strong>{$a}</strong>}';
$string['partiallycorrectfeedback'] = 'Para cualquier respuesta parcialmente correcta';
$string['pluginname'] = 'Respuestas dispersas (Datos Aleatorios)';
$string['pluginname_help'] = 'Las preguntas de datos aleatorios permiten crear preguntas numéricas individuales utilizando variables entre corchetes que se sustituyen por valores individuales cuando se realiza la prueba. Por ejemplo, la pregunta "¿Cuál es el área de un rectángulo de largo {l} y ancho {w}?" tendría la fórmula de respuesta correcta "{l}*{w}" (donde * denota multiplicación).';
$string['pluginname_link'] = 'pregunta/tipo/datos aleatorios';
$string['pluginnameadding'] = 'Agregar una pregunta de datos aleatorios';
$string['pluginnameediting'] = 'Edición de una pregunta de datos aleatorios';
$string['pluginnamesummary'] = 'Las preguntas de datos aleatorios son preguntas numéricas con variables donde los números utilizados son seleccionados al azar cumpliendo con las restricciones dadas y generados con una distribución que brinda resultados lo suficientemente dispersos para asegurar que las respuestas de cada cuestionario sean diferentes.';
$string['privacy:metadata'] = 'El complemento de tipo de pregunta Random Data no almacena ningún dato personal.';
$string['possiblehdr'] = 'Posibles variables presentes solo en el texto de la pregunta';
$string['questiondatasets'] = 'Conjuntos de datos de preguntas';
$string['questiondatasets_help'] = 'Conjuntos de datos de variables que se utilizarán en cada pregunta individual';
$string['questionstoredname'] = 'Nombre de la pregunta almacenado';
$string['range_result'] = 'Rango de los resultados: <strong>{$a}</strong>';
$string['replacewithrandom'] = 'Reemplazar con un valor aleatorio';
$string['reuseifpossible'] = 'reutilizar el valor anterior si está disponible';
$string['sharedwildcard'] = 'Variable compartida {<strong>{$a}</strong>}';
$string['sharedwildcardname'] = 'Variable compartida ';
$string['sharedwildcards'] = 'Variables compartidas';
$string['significantfigures'] = 'con {$a}';
$string['significantfiguresformat'] = 'cifras significativas';
$string['synchronize'] = 'Los datos de conjuntos de datos compartidos con otras preguntas en un cuestionario no serán sincronizadas';
$string['synchronizeno'] = 'No sincronizar';
$string['synchronizeyes'] = 'Sincronizar';
$string['synchronizeyesdisplay'] = 'Sincronice y muestre el nombre de los conjuntos de datos compartidos como prefijo del nombre de la pregunta';
$string['tolerance'] = 'Tolerancia ±';
$string['tolerancetype'] = 'Tipo';
$string['trueanswerinsidelimits'] = 'Respuesta correcta : {$a->correct} <br>Dentro de los límites: {$a->true}';
$string['trueansweroutsidelimits'] = '<span class="error">ERROR Respuesta correcta : {$a->correct} fuera de los límites del valor verdadero {$a->true}</span>';
$string['uniform'] = 'Uniforme';
$string['Uniform'] = 'Uniforme';
$string['uniformbit'] = 'decimales, de una distribución uniforme';
$string['updatecategory'] = 'Actualizar la categoría';
$string['updatedatasetparam'] = 'Actualizar los parámetros de los conjuntos de datos';
$string['updatetolerancesparam'] = 'Actualizar los parámetros de tolerancia de las respuestas';
$string['usedinquestion'] = 'Usado en la pregunta';
$string['youmustaddatleastoneitem'] = 'Debe agregar al menos un elemento del conjunto de datos antes de poder guardar esta pregunta.';
$string['youmustaddatleastonevalue'] = 'Debe agregar al menos un conjunto de valores de variables antes de poder guardar esta pregunta.';
$string['newsetwildcardvalues'] = 'nuevo(s) conjunto(s) de variable(s)';
$string['setno'] = 'Información de los {$a} datos generados';
$string['setwildcardvalues'] = 'conjunto(s) de variable(s)';
$string['showitems'] = 'Mostrar';
$string['tittleconditions'] = 'Condiciones de validez en la fórmula anterior';
$string['Triangle'] = 'Triangular';
$string['updatewildcardvalues'] = 'Actualice los valores de las variables';
$string['unsupportedformulafunction'] = 'La función {$a} no es soportada';
$string['useadvance'] = 'Usa el botón de avanzar para ver los errores';
$string['validationceroformula'] = 'Validación de cero en la fórmula {$a}';
$string['validationnegative'] = '¿El resultado puede ser negativo?';
$string['validationnegativeformula'] = 'Validación de resultado negativo en la fórmula {$a}';
$string['validationpositive'] = '¿El resultado puede ser positivo?';
$string['validationpositiveformula'] = 'Validación de resultado positivo en la fórmula {$a}';
$string['validationzero'] = '¿El resultado puede ser igual a cero?';
$string['var_result'] = 'Varianza: <strong>{$a}</strong>';
$string['wildcard'] = 'Variable {<strong>{$a}</strong>}';
$string['wildcardparam'] = 'Parámetros de variables utilizados para generar los valores';
$string['wildcardrole'] = 'Las variables <strong>{x..}</strong> serán sustituidas por un valor numérico de los valores generados';
$string['wildcards'] = 'Variables {a}...{z}';
$string['wildcardvalues'] = 'Valores de variable(s)';
$string['wildcardvaluesgenerated'] = 'Valores de variable(s) generados';
$string['zerosignificantfiguresnotallowed'] = '¡La respuesta correcta no puede tener cero cifras significativas!';