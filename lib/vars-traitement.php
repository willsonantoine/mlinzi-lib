<?php


class Vars_traitement
{
    public $error = ["code" => 200, "message" => "success", "log" => null];

    public function setError($code, String $message, $log = null)
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

    public function getString_Create_Column($name, $value)
    {

        $default = ($value->default == null) ? 'null' : $value->default;

        $isPrimary = ($value->isPrimary) ? 'PRIMARY KEY NOT NULL, ' : " DEFAULT $default,";


        $value = $name . " $value->type  $isPrimary";


        return $value;
    }

    public function getString_Alter_Column($table, $name, $type, $isPrimary = false, $isExist = false, $default)
    {


        if ($default == null) {
            $default = "null";
        }

        if (!$isPrimary) {
            if (!$isExist) {
                return  $this->getScriptColumn_create($table, $type, $name, $default);
            } else {
                return $this->getScriptColumn_alter($table, $type, $name, $default);
            }
        }
    }

    public function getScriptColumn_alter($table, $type, $name, $default)
    {
        return  "ALTER TABLE $table CHANGE $name $name $type default $default;";
    }

    public function getScriptColumn_create($table, $type, $name, $default)
    {
        return "ALTER TABLE $table ADD $name $type default  $default;";
    }
}
