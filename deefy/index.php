<?php
declare(strict_types=1);

use iutnc\deefy\repository\DeefyRepository;
use iutnc\deefy\dispatch\Dispatcher;

require_once __DIR__ . '/vendor/autoload.php';

DeefyRepository::setConfig('/users/home/valeurma1u/conf/db.config.ini');

session_start();

$action = $_GET['action'] ?? 'default';

$app = new Dispatcher($action);
$app->run();
    