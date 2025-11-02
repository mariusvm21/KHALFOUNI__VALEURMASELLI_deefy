<?php
declare(strict_types=1);

use iutnc\deefy\dispatch\Dispatcher;

require_once 'vendor/autoload.php';

session_start();

$action = (isset($_GET['action'])) ? $_GET['action'] : 'default';

$app = new Dispatcher($action);
$app->run();