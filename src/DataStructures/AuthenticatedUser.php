<?php

namespace xcesaralejandro\canvasoauth\DataStructures;


class AuthenticatedUser {
    public CanvasUser $standard;
    public ?CanvasUser $supplanted_by;
    
    function __construct() {
        $this->standard = new CanvasUser();
        $this->supplanted_by = null;
    }
}