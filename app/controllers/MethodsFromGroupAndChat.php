<?php

namespace App\Controllers;

trait MethodsFromGroupAndChat
{
    public function methodFromGroupAndChat() {
        $type = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $this->getType()))));
        if (method_exists($this, $type)) {
            $this->$type();
        } else {
            $this->groupAndChatUnknownTeam();
        }
    }

    public function newChatParticipant() {
        $data = $this->getDataByType();
    }

    public function leftChatParticipant() {
        $data = $this->getDataByType();
    }

    public function callbackQuery() {
        $data = $this->getDataByType();
    }

    public function groupAndChatUnknownTeam()
    {
        $this->unknownTeam();
    }
}
