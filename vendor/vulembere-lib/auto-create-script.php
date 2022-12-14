<?php

include './vendor/vulembere-lib/vars-traitement.php';
include './vendor/vulembere-lib/dbo.php';

class AutoCreateScript extends Dbo_vulembere_lib
{
    private $containtScripts = [];
    private $all_files = [];

    public function Start($current_table = null)
    { 
        $this->all_files = $this->getAllFiles();

        $this->getContaintFiles($current_table);

        // var_dump($this->containtScripts);

        foreach ($this->containtScripts as $key => $value) {

            // Vérifier si la table existe dans la base de données
            $table = $value["table"];
            $db = $this->config->database;

            if ($this->existValue("SHOW  TABLES where Tables_in_$db='$table' ;") == null) {
                // Si la table n 'existe, nous la créons
     
                $this->create_mysql_element($value["script_create"]);
            } else {
                // Si la table existe, nous modifions les attributs
                if (strlen($value["script_update"]) > 1) {

                    $this->execute($value["script_update"]);
                }
            }
        }
    }

    public function getContaintFiles($current_table)
    {
        foreach ($this->all_files as $key => $table_json) {

            $table = substr($table_json, 0, strpos($table_json, '.'));
            
                $vars = file_get_contents("./database/tables/" . $table_json);

                if (strlen($vars) > 0) {

                    $script_genereted = $this->generateScript($table, $vars);
                    $this->containtScripts[] = [
                        "table" => $table,
                        "script_create" =>  $script_genereted[0],
                        "script_update" =>  $script_genereted[1],
                    ];
                }
            
        }
    }

    private function generateScript($table, $string)
    {
        $all = json_decode($string, false);

        $script = "CREATE TABLE IF NOT EXISTS " . $table . '(';
        $script_alter = "";

        foreach ($all as $key => $value) {

            $script .= $this->getString_Create_Column($key, $value);
            $db = $this->config->database;
 
            if ($this->existValue("SHOW  TABLES where Tables_in_$db='$table' ;") != null) {
                $isExist = $this->columnInTable($table, $key);

                $script_alter .= $this->getString_Alter_Column($table, $key, $value->type, $value->isPrimary, $isExist, $value->default);
            }
        }

        $script = substr($script, 0, strlen($script) - 1) . ');';
         
        return [$script, $script_alter];
    }
}
