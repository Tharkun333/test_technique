<?php

use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Ramsey\Uuid\Uuid;
use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';



$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));
$app->addErrorMiddleware(true, true, true);

$gestionEcritures = require __DIR__ . '/../src/Ecritures/gestionEcritures.php';
$gestionComptes = require __DIR__ . '/../src/Comptes/gestionComptes.php';
$db = new Db();
$conn = $db->connect();
$gestionEcritures($app,$conn);
$gestionComptes($app,$conn);

$app->run();
