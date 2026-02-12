<?php

namespace App\Enum;

use Filament\Support\Contracts\HasLabel;

enum NavigationGroup: string implements HasLabel
{
    case AuditManagement = 'Audit Management';
    case MasterData = 'Master Data';
    case Administration = 'Administration';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
