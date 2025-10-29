<?php

declare(strict_types=1);

namespace mod_proposal\completion;

use core_completion\activity_custom_completion;

class custom_completion extends activity_custom_completion {
    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $userid = $this->userid;
        $proposalid = $this->cm->instance;

        if (!$proposal = $DB->get_record('proposal', ['id' => $proposalid])) {
            throw new \moodle_exception('Unable to find proposal with id ' . $proposalid);
        }

        if ($rule == 'completionsubmit') {
            $submissionutil = new \mod_proposal\support\entry();

            if ($submissionutil->user_has_entry($proposal->id, $userid)) {
                return COMPLETION_COMPLETE;
            }
        }

        return COMPLETION_INCOMPLETE;
    }

    /**
     * Fetch the list of custom completion rules that this module defines.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return ['completionsubmit'];
    }

    /**
     * Returns an associative array of the descriptions of custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        return [
            'completionsubmit' => get_string('completionsubmit', 'mod_proposal')
        ];
    }

    /**
     * Returns an array of all completion rules, in the order they should be displayed to users.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionsubmit',
        ];
    }
}
