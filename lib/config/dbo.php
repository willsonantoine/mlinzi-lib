<?php
require './lib/vars-traitement.php';
class Dbo extends Vars_traitement
{
    public $dbo_mater = null;
    public $dbo = null;
    public $config = null;
    public $pdo_options = null;
    public $offset = null;

    public function __construct()
    {
        $this->config = json_decode(file_get_contents('./lib/config/dbo_config.json'));
    }

    public function getDbo()
    {
        if ($this->dbo == null) {
            $this->con();
        }

        return $this->dbo;
    }

    public function con()
    {
        try {

            date_default_timezone_set($this->config->time_zone);

            $now = new DateTime();
            $mins = $now->getOffset() / 60;
            $sgn = ($mins < 0 ? -1 : 1);
            $mins = abs($mins);
            $hrs = floor($mins / 60);
            $mins -= $hrs * 60;
            $this->offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);
            $this->pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;
            $this->dbo_mater = new PDO("mysql:host=" . $this->config->host . ";", $this->config->user, $this->config->password, $this->pdo_options);
            $this->dbo_mater->exec("SET time_zone='$this->offset';");
            $this->dbo_mater->query('SET NAMES ' . $this->config->encodage);
            $this->connect_to_database();
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
                $this->connect_to_database();
                break;
            case 1045:
                $this->setError($code, "Veuillez vérifier le login de cet utilisateur. La connexion au serveur a échoué. ", $exception);
                break;
            default:
                $this->setError($code, "Erreut de traitement", $exception);
                break;
        }
    }

    private function connect_to_database()
    {
        try {

            if ($this->create_mysql_element("CREATE DATABASE IF NOT EXISTS " . $this->config->database, $this->dbo_mater)) {
                $this->dbo = new PDO("mysql:host=" . $this->config->host . ";dbname=" . $this->config->database, $this->config->user, $this->config->password, $this->pdo_options);
                $this->dbo->exec("SET time_zone='$this->offset';");
                $this->dbo->query('SET NAMES ' . $this->config->encodage);
                $this->setError(200, "connexion réussie à la base de données ");
            } else {
                $this->setError(500, "Erreur de connexion à la base de données ");
            }
        } catch (\Throwable $th) {
            $this->setError($th->getCode(), "Erreut de traitement", $th);
        }
    }

    public function create_mysql_element($querry, $dbo = null)
    {
        try {
            $dbo = ($dbo == null) ? $this->getDbo() : $this->dbo_mater;

            $prepare = $dbo->prepare($querry);
            return $prepare->execute();
        } catch (\Throwable $th) {
            $this->setError($th->getCode(), "Erreut de traitement", $th);
            return false;
        }
    }

    public function getValue($rqt, $data = [])
    {
        $var = null;
        try {
            $req = $this->getDbo()->prepare($rqt);
            $req->execute($data);
            while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
                $var = $data['i'];
            }
        } catch (Exception $exception) {
            $this->setError(201, "Une erreur s'est produite", $exception);
        }
        return $var;
    }

    public function columnInTable($table, $name)
    {
        return $this->existValue("SHOW COLUMNS FROM $table where Field='$name';");
    }

    public function existValue($rqt, $data = [])
    {
        try {
            $req = $this->getDbo()->prepare($rqt);
            $req->execute($data);
            while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
                return true;
            }
        } catch (Exception $exception) {
            $this->setError(201, "Une erreur s'est produite", $exception);
        }

        return false;
    }

    public  function execute($rqt, $data = [], $message_success = "Traitement réussie avec success", $message_erreur = "Une erreur s'est produite")
    {
        $bool = false;
        try {
            $req = $this->getDbo()->prepare($rqt);
            $bool = $req->execute($data);
            return $bool;
        } catch (\Throwable $exception) {
            $this->setError(201, "Une erreur s'est produite", $exception);
        }
        return false;
    }
}
