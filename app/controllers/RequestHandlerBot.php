<?php

namespace App\Controllers;

use App\Core\BaseRequestHandler;
use App\Models\InlineButtons;
use App\Models\Menu;
use App\Models\Text;

class RequestHandlerBot extends BaseRequestHandler
{
    use MethodsFromGroupAndChat;

    public function __construct()
    {
        parent::__construct();
    }

    public function start()
    {
        // EXAMPLE
        echo $this->send(Text::example(), InlineButtons::custom(
            [[
                'id' => 1,
                'name' => 'Test1'
            ], [
                'id' => 2,
                'name' => 'Test2'
            ]], 2, 'start', 'name', null, 'id'
        ));
        // END EXAMPLE
    }
}
