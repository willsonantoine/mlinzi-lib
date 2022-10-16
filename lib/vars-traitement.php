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

    public function getString_Create_Column($name, $type, $isPrimary = false, $size = 255, $default = null)
    {
        $isPrimary = ($isPrimary) ? 'primary key' : '';
        $value = '';
        switch ($type) {
            case 'integer':
                $value = $name . ' int ' . $isPrimary . ',';
                break;
            case 'string':
                $value = $name . " varchar($size) " . $isPrimary . ',';
                break;
            case 'text':
                $value = $name . " longtext " . $isPrimary . ',';
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

    public function getString_Alter_Column($table, $name, $type, $isPrimary = false, $isExist = false, $size = 255, $default = null)
    {
        $isPrimary = ($isPrimary) ? 'primary key' : '';
        
        if (!$isExist) {
            return  $this->getScriptColumn_create($table, $type, $name);
        } else {
            return $this->getScriptColumn($table, $type, $name);
        }
    }

    public function getScriptColumn($table, $type, $name)
    {
        switch ($type) {
            case 'integer':
                $value = "ALTER TABLE $table CHANGE $name $name integer;";
                break;
            case 'string':
                $value = "ALTER TABLE $table CHANGE $name $name varchar(255);";
                break;
            case 'text':
                $value = "ALTER TABLE $table CHANGE $name $name longtext;";
                break;
            case 'number':
                $value = "ALTER TABLE $table CHANGE $name $name float;";
                break;

            default:
                $value = "ALTER TABLE $table CHANGE $name $name $type;";
                break;
        }

        return $value;
    }

    public function getScriptColumn_create($table, $type, $name)
    {
        switch ($type) {
            case 'integer':
                $value = "ALTER TABLE $table ADD $name int;";
                break;
            case 'string':
                $value = "ALTER TABLE $table ADD $name varchar(255);";
                break;
            case 'text':
                $value = "ALTER TABLE $table ADD $name longtext;";
                break;
            case 'number':
                $value = "ALTER TABLE $table ADD $name float;";
                break;

            default:
                $value = "ALTER TABLE $table ADD $name $type";
                break;
        }

        return $value;
    }
}
