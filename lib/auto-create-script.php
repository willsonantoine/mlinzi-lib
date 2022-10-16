<?php

include './lib/config/dbo.php';
class AutoCreateScript extends Dbo
{
    private $containtScripts = [];
    private $all_files = [];

    public function Start()
    {
        $this->all_files = $this->getAllFiles();
        $this->getContaintFiles();
        var_dump($this->containtScripts);
    }

    public function getContaintFiles()
    {
        foreach ($this->all_files as $key => $table) {
            $vars = file_get_contents("./database/tables/" . $table);

            if (strlen($vars) > 0) {
                $table = substr($table, 0, strpos($table, '.'));
                $this->containtScripts[] = [
                    "table" => $table,
                    "script" =>  $this->generateScript($table, $vars)
                ];
            }
        }
    }

    private function generateScript($table, $string)
    {
        $all = json_decode($string, false);
        $script = "CREATE TABLE IF NOT EXISTS " . $table . '(';

        foreach ($all as $key => $value) {  
            $script .= $this->getStringColumn($key,$value->type,$value->isPrimary);
        }

        $script = substr($script,0, strlen($script) - 1).');';

        return $script;
    }

   
}
