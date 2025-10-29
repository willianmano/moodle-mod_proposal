<?php

namespace mod_proposal\support;

class entry {
    public function get_all(int $proposalid): array {
        global $DB;

        $entries = $DB->get_records('proposal_entries', ['proposalid' => $proposalid]);

        if (!$entries) {
            return [];
        }

        $usersupport = new user();
        foreach ($entries as $entry) {
            $user = $usersupport->get_by_id($entry->userid);

            $entry->userfullname = fullname($DB->get_record('user', ['id' => $entry->userid]));
            $entry->userimage = $usersupport->get_user_image_or_avatar($user);
            $entry->timemodified = userdate($entry->timemodified);
            $entry->difficultystr = get_string($entry->difficulty, 'mod_proposal');

            $entry->rate = $this->get_rating_avg($entry->id);
            $entry->userrate = $this->get_user_rating($entry->id);

            $optionshtml = '';
            for ($i = 5; $i > 0; $i--) {
                $optionshtml .= "<input type='radio' id='star-rating-{$entry->id}-{$i}'";
                if ($entry->userrate && $entry->userrate == $i) {
                    $optionshtml .= " checked='checked' aria-checked='true' ";
                }
                $optionshtml .= "name='rating-{$entry->id}' value='{$i}'>";
                $optionshtml .= "<label data-entryid='{$entry->id}' data-rate='{$i}' for='star-rating-{$entry->id}-{$i}' class='fa-solid fa-star'></label>";
            }

            $entry->optionshtml = $optionshtml;
        }

        $randomizer = new \Random\Randomizer();

        return array_values($randomizer->shuffleArray($entries));
    }

    public function update_or_save(int $userid, int $entryid, int $rate): float {
        global $DB;

        $entry = $DB->get_record('proposal_entries', ['id' => $entryid]);
        if ($entry->userid == $userid) {
            throw new \moodle_exception('cannotrateyourownentry', 'mod_proposal');
        }

        $data = $DB->get_record('proposal_entry_ratings', [
            'entryid' => $entryid,
            'userid' => $userid,
        ]);

        if ($data) {
            $data->rate = $rate;
            $data->timemodified = time();
            $DB->update_record('proposal_entry_ratings', $data);

            return $this->get_rating_avg($data->entryid);
        }

        $data = new \stdClass();
        $data->entryid = $entryid;
        $data->userid = $userid;
        $data->rate = $rate;
        $data->timecreated = time();
        $data->timemodified = time();

        $DB->insert_record('proposal_entry_ratings', $data);

        $cm = get_coursemodule_from_instance('proposal', $entry->proposalid);
        $context = \context_module::instance($cm->id);

        \mod_proposal\event\rate_added::create([
            'objectid' => $data->entryid,
            'context' => $context,
        ]);

        return $this->get_rating_avg($data->entryid);
    }

    public function get_rating_avg(int $entryid): float {
        global $DB;

        $sql = 'SELECT AVG(rate) as avg FROM {proposal_entry_ratings} WHERE entryid = :entryid';
        $avg = $DB->get_record_sql($sql, ['entryid' => $entryid]);

        if (!$avg) {
            return 0;
        }

        $ratingavg = floor($avg->avg*100)/100;
        $ratingavg = number_format($ratingavg, 2);

        return (float) $ratingavg;
    }

    public function get_user_rating(int $entryid): float {
        global $DB, $USER;

        $sql = 'SELECT rate FROM {proposal_entry_ratings} WHERE entryid = :entryid AND userid = :userid';
        $rate = $DB->get_record_sql($sql, ['entryid' => $entryid, 'userid' => $USER->id]);

        if (!$rate) {
            return 0;
        }

        return $rate->rate;
    }

    public function user_has_entry($proposalid): bool {
        global $DB, $USER;

        $entry = $DB->get_records('proposal_entries', ['proposalid' => $proposalid, 'userid' => $USER->id]);

        if ($entry) {
            return true;
        }

        return false;
    }
}
