<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Dbo_vulembere_lib extends Vars_traitement
{
    public $dbo_mater = null;
    public $dbo = null;
    public $config = null;
    public $pdo_options = null;
    public $offset = null;
    public $data_response = ["message" => 'success', "status" => true, "code" => 200, "data" => []];

    public function setResponse($message, $status, $data = [], $token = null)
    {

        if ($token == null) {

            $this->data_response = ["message" => $message, "status" => $status == 200, "code" => $status, "data" => $data];
        } else {
            $this->data_response = ["message" => $message, "status" => $status == 200, "code" => $status, "data" => $data, 'token' => $token];
        }

        return  $this->data_response;
    }

    public function __construct()
    {
        $this->config = json_decode(file_get_contents('./config/dbo_config.json'));
    }

    public function init()
    {
        $this->config = json_decode(file_get_contents('./config/dbo_config.json'));
    }

    public function getDbo()
    {
        if ($this->dbo == null) {
            $this->init();
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

    private function isError($exception, $rqt = null, $message_erreur = "Une erreur s'est produite")
    {

        $message = $exception->getMessage();

        $code = $exception->getCode();
        switch ($code) {
            case 1049:
                $this->setError($code, "La base de données n'existe pas", $exception, $rqt);
                $this->connect_to_database();
                break;
            case 1045:
                $this->setError($code, "Veuillez vérifier le login de cet utilisateur. La connexion au serveur a échoué. ", $exception, $rqt);
                break;
            case '42S02':
                $script = new AutoCreateScript();

                $script->Start($this->getTableInQuerry($rqt));

                $this->setError($code, "Cette table n'existe pas dans le système. Nous essayons de la créer. Veuillez réessayer plus tard ", $exception, $rqt);
                break;
            case '22001':
                $script = new AutoCreateScript();

                $script->Start($this->getTableInQuerry($rqt));

                $this->setError($code, "Cette table n'existe pas dans le système. Nous essayons de la créer. Veuillez réessayer plus tard ", $exception, $rqt);
                break;
            case '42S22':
                $script = new AutoCreateScript();
                $script->Start($this->getTableInQuerry($rqt));

                $this->setError($code, "Une colonne n'existe pas dans cette table. Assurez-vous de vérifier ou nous essayons de résoudre le problème.", $exception, $rqt);
                break;
            default:
                $this->setError($code, "Erreut de traitement", $exception, $rqt);
                break;
        }
        $this->setResponse($message_erreur, 201);
        $this->writeError($rqt, $exception);
    }

    public function getTableInQuerry($rqt)
    {

        if (strpos($rqt, 'INTO') !== false) {

            $table = substr($rqt, strpos($rqt, 'INSERT INTO') + 12, strlen($rqt));
            $table = substr($table, 0, strpos($table, ' '));
        } else {
            $table = substr($rqt, strpos($rqt, 'from') + 5, strlen($rqt));
            $table = substr($table, 0, strpos($table, ' '));
        }

        return $table;
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

            $this->isError($th,$querry);
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
            $this->isError($exception, $rqt);
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
    public function NotExist($rqt, $data = [])
    {
        try {
            $req = $this->getDbo()->prepare($rqt);
            $req->execute($data);
            while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
                return false;
            }
        } catch (Exception $exception) {
            $this->setError(201, "Une erreur s'est produite", $exception);
        }

        return true;
    }

    public  function execute($rqt, $data = [], $message_success = "Traitement réussie avec success", $message_erreur = "Une erreur s'est produite")
    {
        $bool = false;
        try {
            $req = $this->getDbo()->prepare($rqt);
            $bool = $req->execute($data);
            $this->setResponse($message_success, 200);
            return $bool;
        } catch (Throwable $th) {
            $this->isError($th, $rqt);
        }
        return false;
    }
    public function getAll($rqt, $data = [])
    {
        try {
            $var = [];
            $req = $this->getDbo()->prepare($rqt);
            $req->execute($data);
            while ($data = $req->fetch(PDO::FETCH_ASSOC)) {
                $var[] = (object) $data;
            }
            $req->closeCursor();
        } catch (Exception $th) {
            $this->isError($th, $rqt);
        }
        return $var;
    }

    function writeError($origin, $message)
    {
        $file = './config/api_long.log';
        $date = date("Y/m/d h:i:sa");
        try {
            $last = file_get_contents($file);
            file_put_contents($file, "");

            file_put_contents($file, array($last, ($last . '\n' . $origin . ' => ' . $date . "\n" . $message), PHP_EOL), FILE_APPEND);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }


    public function isEmpty($required_params, $request_params)
    {
        $error          = false;
        $error_params   = '';

        foreach ($required_params as $param) {

            if (!isset($request_params[$param]) || strlen($request_params[$param]) <= 0) {
                $error = true;

                $error_params .= '[ ' . $param . ' ], ';
            }
        }

        if ($error) {
            $this->setResponse('Required parameters ' . substr($error_params, 0, -2) . ' are empty or missing :(', 201);
        }

        return $error;
    }

    public function HashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function isPassword($password1, $password2)
    {

        return password_verify($password1, $password2);
    }

    public function isValidePassword($password)
    {
        // Validate password strength
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $specialChars = preg_match('@[^\w]@', $password);

        if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
            $this->setResponse("Le mot de passe doit comporter au moins 8 caractères et doit comprendre au moins une lettre majuscule, un chiffre et un caractère spécial.", 201);
            return false;
        } else {
            return true;
        }
    }

    public function isMail($email)
    {
        if ($email != null) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return true;
            } else {
                $this->setResponse("Votre adresse mail n'est pas valide", 201);
                return false;
            }
        }

        return true;
    }

    function isPhone(string $string, int $minDigits = 9, int $maxDigits = 13): bool
    {
        if ($string != null) {
            if (preg_match('/^[0-9]{' . $minDigits . ',' . $maxDigits . '}\z/', $string)) {
                return true;
            }
        } else {
            $this->setResponse("Votre numéro de téléphone n'est pas valide", 201);
            return false;
        }

        return true;
    }


    function sendEmail($id_user, $mail_to, $name, $object, $message, $fulltext = '')
    {

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        $etat = 0;
        try {

            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'mail56.lwspanel.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'admin@mlinzi-corporation.com';           //SMTP username
            $mail->Password   = '@Antoinewi7285';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('admin@mlinzi-corporation.com', 'Mlinzi corporation');
            $mail->addAddress($mail_to, $name);     //Add a recipient 
            $mail->addCC('mlinzirdc@gmail.com');

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $object;
            $mail->Body    = $message;
            $mail->AltBody = $fulltext;

            $mail->SMTPDebug = 0;

            $mail->send();
            $observation = "Envoie réussie";
            $etat = 1;
        } catch (Exception $e) {
            $observation =  $e->getMessage();
        }
        $id_ = random_int(100000, 900000);

        $this->execute(
            "INSERT INTO historique_email SET id=?, id_user=?,object=?,message=?,observation=?,etat=? ",
            [$id_, $id_user, $object, $message, $observation, $etat]
        );

        return $etat;
    }
}
