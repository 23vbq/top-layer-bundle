<?php

namespace Vbq23\TopLayerBundle\Toast;

enum ToastType: string
{
    case Success = 'success';
    case Error = 'error';
    case Warning = 'warning';
    case Info = 'info';
}
