<?php


class Vars_traitement
{
    public $error = ["code" => 200, "message" => "success", "log" => null, "querry" => null];

    public function setError($code, String $message, $log = null, $querry = null)
    {
        $this->error["code"] = $code;
        $this->error["message"] = $message;
        $this->error["log"] = $log;
        $this->error["querry"] = $querry;
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



        $default = 'NOT NULL';
        $isPrimary = '';
        if ($value->isPrimary === true) {
            $isPrimary = 'PRIMARY KEY ';
        } else {

            if ($value->default === null || $value->default === '') {

                $default =  "DEFAULT NULL";
            } else {
                $default = "DEFAULT $value->default";
            }
        }
 

        $value = $name . " $value->type  $isPrimary $default ,";


        return $value;
    }

    public function getString_Alter_Column($table, $name, $type, $isPrimary = false, $isExist = false, $default)
    {


        if ($default === null) {
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
