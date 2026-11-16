<?php

namespace Vbq23\TopLayerBundle\Toast;

enum ToastType: string
{
    case Success = 'success';
    case Error = 'error';
    case Warning = 'warning';
    case Info = 'info';

    /**
     * ARIA role of the toast: errors and warnings interrupt the screen reader, the rest waits its turn.
     */
    public function role(): string
    {
        return match ($this) {
            self::Error, self::Warning => 'alert',
            self::Success, self::Info => 'status',
        };
    }
}
