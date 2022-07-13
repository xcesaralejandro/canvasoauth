<?php
namespace xcesaralejandro\canvasoauth\DataStructures;

class CanvasUser {
    public string $id;
    public string $name;
    public string $global_id;
    public ?string $effective_locale;
}