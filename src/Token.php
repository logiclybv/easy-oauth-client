<?php declare(strict_types=1);

namespace Logicly\EasyOAuthClient;

enum Token: string
{
    case ACCESS_TOKEN = 'access';
    case REFRESH_TOKEN = 'refresh';
}
