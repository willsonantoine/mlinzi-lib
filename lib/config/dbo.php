<?php
require './lib/vars-traitement.php';
class Dbo extends Vars_traitement
{
    public $dbo = null;
    public $config = null;

    public function con()
    {
        $this->config = json_decode(file_get_contents('./lib/config/dbo_config.json'));

        try {

            date_default_timezone_set($this->config->time_zone);

            $now = new DateTime();
            $mins = $now->getOffset() / 60;
            $sgn = ($mins < 0 ? -1 : 1);
            $mins = abs($mins);
            $hrs = floor($mins / 60);
            $mins -= $hrs * 60;
            $offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);
            $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            $this->dbo = new PDO("mysql:host=" . $this->config->host . ";dbname=" . $this->config->database . "", $this->config->user, $this->config->password, $pdo_options);
            $this->dbo->exec("SET time_zone='$offset';");
            $this->dbo->query('SET NAMES ' . $this->config->encodage);
        } catch (Exception $exception) {
            $this->isError($exception);
        }

        return $this->dbo;
    }

    private function isError(Exception $exception)
    {
        $code = $exception->getCode();
        switch ($code) {
            case 1049:
                $this->setError($code, "La base de données n'existe pas", $exception);
                break;
            case 1045:
                $this->setError($code, "Veuillez vérifier le login de cet utilisateur. La connexion au serveur a échoué. ", $exception);
            default:
                $this->setError($code, "Erreut de traitement", $exception);
                break;
        }
    }
}
