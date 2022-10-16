<?php

use phpDocumentor\Reflection\Types\Boolean;

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

    public function getAllFiles()
    {
        $result = [];
        $tab = glob('./database/tables/' . "*.{json}", GLOB_BRACE);
        foreach ($tab as $key => $value) {
            $array = explode('/', $value);
            $file = $array[count($array) - 1];
            $result[] = $file;
        }
        return $result;
    }

    public function getStringColumn($name, $type, $isPrimary = false, $size = 255, $default = null)
    {
        $isPrimary = ($isPrimary) ? 'primary key' : '';
        $value = '';
        switch ($type) {
            case 'integer':
                $value = $name . ' int ' . $isPrimary . ',';
                break;
            case 'string':
                $value = $name . ' varchar(255) ' . $isPrimary . ',';
                break;
            case 'number':
                $value = $name . ' float ' . $isPrimary . ',';
                break;

            default:
                $value = $name . ' text ';
                break;
        }
       

        return $value;
    }
}
