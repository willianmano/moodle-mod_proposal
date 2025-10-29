<?php

namespace mod_proposal\output;

use core\output\renderable;
use core\output\renderer_base;
use core\output\templatable;

class view implements renderable, templatable {

    protected $proposal;
    protected $context;

    public function __construct($proposal, \core\context\module $context) {
        $this->proposal = $proposal;
        $this->context = $context;
    }

    public function export_for_template(renderer_base $output)
    {
        $entrysupport = new \mod_proposal\support\entry();

        $hasentry = $entrysupport->user_has_entry($this->proposal->id);

        return [
            'cmid' => $this->context->instanceid,
            'entries' => $entrysupport->get_all($this->proposal->id),
            'canaddentry' => !$hasentry,
        ];
    }
}