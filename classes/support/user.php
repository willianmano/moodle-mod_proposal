<?php

namespace mod_proposal\support;

class user {
    /**
     * Get all users enrolled in a course by id
     *
     * @param int $userid
     * @param context_course $context
     *
     * @return \stdClass
     * @throws \dml_exception
     */
    public function get_by_id($userid) {
        global $DB;

        $userfieldsapi = \core_user\fields::for_userpic();
        $allnamefields = $userfieldsapi->get_sql('u', false, '', '', false)->selects;

        $sql = "SELECT $allnamefields
                FROM {user} u
                WHERE u.id = :userid";

        $params = ['userid' => $userid];

        return $DB->get_record_sql($sql, $params, MUST_EXIST);
    }

    public function get_user_image_or_avatar($user) {
        global $PAGE;

        $userpicture = new \core\output\user_picture($user);
        $userpicture->size = 1;
        $userpicture = $userpicture->get_url($PAGE);

        if ($userpicture instanceof \moodle_url) {
            return $userpicture->out();
        }

        return $userpicture;
    }
}