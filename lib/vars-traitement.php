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

        $result = '';
        switch ($value->type) {
            case 'integer':
                $result = $name . " int " . $isPrimary;
                break;
            case 'string':
                $result = $name . " varchar(255) " . $isPrimary;
                break;
            case 'text':
                $result = $name . " longtext " . $isPrimary;
                break;
            case 'number':
                $result = $name . ' float ' . $isPrimary;
                break;

            default:
                $value = $name . " longtext  $isPrimary";
                break;
        }

        return $result;
    }

    public function getString_Alter_Column($table, $name, $type, $isPrimary = false, $isExist = false, $default)
    {
        if ($isPrimary) {
            $default = '';
        } else {
            if ($default == null) {
                $default == 'DEFAULT null';
            }
        }

        if (!$isExist) {
            return  $this->getScriptColumn_create($table, $type, $name, $default);
        } else {
            return $this->getScriptColumn_alter($table, $type, $name, $default);
        }
    }

    public function getScriptColumn_alter($table, $type, $name, $default)
    {
        switch ($type) {
            case 'integer':
                $value = "ALTER TABLE $table CHANGE $name $name integer  $default;";
                break;
            case 'string':
                $value = "ALTER TABLE $table CHANGE $name $name varchar(255)  $default;";
                break;
            case 'text':
                $value = "ALTER TABLE $table CHANGE $name $name longtext  $default;";
                break;
            case 'number':
                $value = "ALTER TABLE $table CHANGE $name $name float  $default;";
                break;

            default:
                $value = "ALTER TABLE $table CHANGE $name $name $type  $default;";
                break;
        }

        return $value;
    }

    public function getScriptColumn_create($table, $type, $name, $default)
    {
        switch ($type) {
            case 'integer':
                $value = "ALTER TABLE $table ADD $name int  $default;";
                break;
            case 'string':
                $value = "ALTER TABLE $table ADD $name varchar(255)  $default;";
                break;
            case 'text':
                $value = "ALTER TABLE $table ADD $name longtext  $default;";
                break;
            case 'number':
                $value = "ALTER TABLE $table ADD $name float  $default;";
                break;

            default:
                $value = "ALTER TABLE $table ADD $name $type  $default";
                break;
        }

        return $value;
    }
}
