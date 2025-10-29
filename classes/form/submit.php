<?php

namespace mod_proposal\form;

require_once($CFG->libdir. '/formslib.php');

class submit extends \moodleform
{
    protected $context;
    private $editordata = null;

    public function __construct($url, $formdata, $context)
    {
        $this->context = $context;

        parent::__construct($url, $formdata);
    }

    protected function definition() {
        $mform = $this->_form;

        if (isset($this->_customdata['entryid'])) {
            $mform->addElement('hidden', 'entryid', $this->_customdata['entryid']);
            $mform->setType('entryid', PARAM_INT);
        }

        $mform->addElement('hidden', 'proposalid', $this->_customdata['proposalid']);
        $mform->setType('proposalid', PARAM_INT);

        $data = new \stdClass();
        if (isset($this->_customdata['entryid'])) {
            $data = $this->get_entry_content($this->_customdata['entryid']);
        }

        $difficulties = [
            'easy' => get_string('easy', 'mod_proposal'),
            'medium' => get_string('medium', 'mod_proposal'),
            'hard' => get_string('hard', 'mod_proposal')
        ];

        $mform->addElement('select', 'difficulty', get_string('difficulty', 'mod_proposal'), $difficulties);
        $mform->addRule('difficulty', null, 'required', null, 'client');
        $mform->setType('difficulty', PARAM_ALPHANUM);

        $mform->addElement('textarea', 'content', get_string('content', 'mod_proposal'), ['rows' => 10]);
        $mform->addRule('content', null, 'required', null, 'client');
        $mform->setType('content_editor', PARAM_RAW);

        $this->add_action_buttons();
    }

    private function get_entry_content($id) {
        global $DB;

        return $DB->get_record('proposal_entries', ['id' => $id], 'id, content, contentformat', MUST_EXIST);
    }
}
