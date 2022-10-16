<?php

class Vars_traitement
{
    public $error = ["code" => 200, "message" => "success", "log" => null];

    public function setError(int $code, String $message, $log = null)
    {
        $this->error["code"] = $code;
        $this->error["message"] = $message;
        $this->error["log"] = $log;
    }

    public function getError(): array
    {
        return $this->error;
    }
}
