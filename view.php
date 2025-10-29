<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Prints an instance of mod_proposal.
 *
 * @package     mod_proposal
 * @copyright   2025 Willian Mano <willianmano@conecti.me>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course module id.
$id = optional_param('id', 0, PARAM_INT);

$cm = get_coursemodule_from_id('proposal', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$proposal = $DB->get_record('proposal', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);

$context = \core\context\module::instance($cm->id);

$event = \mod_proposal\event\course_module_viewed::create([
    'objectid' => $proposal->id,
    'context' => $context,
]);
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('proposal', $proposal);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/proposal/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($proposal->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo $OUTPUT->header();

$renderer = $PAGE->get_renderer('mod_proposal');

$contentrenderable = new \mod_proposal\output\view($proposal, $context);

echo $renderer->render($contentrenderable);

echo $OUTPUT->footer();
