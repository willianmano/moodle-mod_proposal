<?php

namespace mod_proposal\external;

use core_external\external_api;
use core_external\external_value;
use core_external\external_single_structure;
use core_external\external_function_parameters;

class rate extends external_api {
    /**
     * Choose avatar parameters
     *
     * @return external_function_parameters
     */
    public static function rating_parameters() {
        return new external_function_parameters([
            'entryid' => new external_value(PARAM_INT, 'The context id for the course module'),
            'rate' => new external_value(PARAM_INT, 'The avatar id'),
        ]);
    }

    /**
     * Choose avatar method
     *
     * @param int $entryid
     * @param int $rate
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \moodle_exception
     */
    public static function rating($entryid, $rate) {
        global $USER;

        // We always must pass webservice params through validate_parameters.
        $params = self::validate_parameters(self::rating_parameters(),
            ['entryid' => $entryid, 'rate' => $rate]);

        $entrysupport = new \mod_proposal\support\entry();
        $rate = $entrysupport->update_or_save($USER->id, $params['entryid'], $params['rate']);

        return [
            'rate' => $rate,
        ];
    }

    /**
     * Choose avatar return fields
     *
     * @return external_single_structure
     */
    public static function rating_returns() {
        return new external_single_structure([
            'rate' => new external_value(PARAM_FLOAT, 'Entry rate average'),
        ]);
    }
}
