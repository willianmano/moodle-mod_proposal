<?php

/**
 * Submit proposal entry.
 *
 * @package     mod_proposal
 * @copyright   2023 World Bank Group <https://worldbank.org>
 * @author      Willian Mano <willianmanoaraujo@gmail.com>
 */

require(__DIR__.'/../../config.php');
require_once($CFG->dirroot . '/repository/lib.php');

// Course module id.
$id = required_param('id', PARAM_INT);
$entryid = optional_param('entryid', null, PARAM_INT);

list ($course, $cm) = get_course_and_cm_from_cmid($id, 'proposal');

require_course_login($course, true);

$proposal = $DB->get_record('proposal', ['id' => $cm->instance], '*', MUST_EXIST);

if ($entryid) {
    $entry = $DB->get_record('proposal_entries', ['id' => $entryid, 'userid' => $USER->id], '*', MUST_EXIST);
}

$context = \core\context\module::instance($id);

$url = new moodle_url('/mod/proposal/submit.php', ['id' => $id]);

$PAGE->set_url($url);
$PAGE->set_title(format_string($proposal->name));
$PAGE->set_heading(format_string($proposal->name));
$PAGE->set_context($context);

$formdata = [
    'courseid' => $course->id,
    'proposalid' => $proposal->id
];

if ($entryid) {
    $formdata['entryid'] = $entryid;
}

$form = new \mod_proposal\form\submit($url, $formdata, $context);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/mod/proposal/view.php', ['id' => $id]));
} else if ($formdata = $form->get_data()) {
    try {
        unset($formdata->submitbutton);

        if (isset($formdata->entryid)) {
            $entry = $DB->get_record('proposal_entries', ['id' => $formdata->entryid, 'userid' => $USER->id], '*', MUST_EXIST);

            $entry->difficulty = $formdata->difficulty;
            $entry->content = $formdata->content;
            $entry->timemodified = time();

            $DB->update_record('proposal_entries', $entry);

            // Process event.
            $params = array(
                'context' => $context,
                'objectid' => $entry->id,
                'relateduserid' => $entry->userid
            );
            $event = \mod_proposal\event\entry_updated::create($params);
            $event->add_record_snapshot('proposal_entries', $entry);
            $event->trigger();

            $redirectstring = get_string('entry:update_success', 'mod_proposal');
        } else {
            $entry = new \stdClass();
            $entry->proposalid = $proposal->id;
            $entry->userid = $USER->id;
            $entry->difficulty = $formdata->difficulty;
            $entry->content = $formdata->content;
            $entry->timecreated = time();
            $entry->timemodified = time();

            $entryid = $DB->insert_record('proposal_entries', $entry);
            $entry->id = $entryid;

            // Processe event.
            $params = array(
                'context' => $context,
                'objectid' => $entryid,
                'relateduserid' => $entry->userid
            );
            $event = \mod_proposal\event\entry_added::create($params);
            $event->add_record_snapshot('proposal_entries', $entry);
            $event->trigger();

            // Completion progress.
            $completion = new completion_info($course);
            $completion->update_state($cm, COMPLETION_COMPLETE);

            $redirectstring = get_string('entry:add_success', 'mod_proposal');
        }

        $url = new moodle_url('/mod/proposal/view.php', ['id' => $cm->id]);

        redirect($url, $redirectstring, null, \core\output\notification::NOTIFY_SUCCESS);
    } catch (\Exception $e) {
        redirect($url, $e->getMessage(), null, \core\output\notification::NOTIFY_ERROR);
    }
} else {
    echo $OUTPUT->header();

    $form->display();

    echo $OUTPUT->footer();
}
