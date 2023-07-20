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

// -- Exercice 10 : récupérer la liste de TOUS les comptes avec ses écritures -- //
$app->get('/comptes-with-ecritures', function (Request $request, Response $response) {
    $sql = "SELECT c.uuid AS compte_uuid, c.login, c.password, c.created_at, c.updated_at, c.name AS compte_name,
            JSON_ARRAYAGG(JSON_OBJECT('ecriture_uuid', e.uuid, 'label', e.label, 'date', e.date, 'type', e.type, 'amount', e.amount,'created_at',e.created_at,'updated_at',e.updated_at)) AS ecritures
            FROM comptes c
            LEFT JOIN ecritures e ON c.uuid = e.compte_uuid
            GROUP BY c.uuid";
   
    try {
        $db = new Db();
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $db = null;
        
        $comptes = array();
        foreach ($result as $row) {
            $compte_uuid = $row['compte_uuid'];
            $ecritures = json_decode($row['ecritures'], true);
            
            $comptes[] = array(
                'compte_uuid' => $compte_uuid,
                'login' => $row['login'],
                'password' => $row['password'],
                'name' => $row['compte_name'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at'],
                'ecritures' => $ecritures
            );
        }
     
        $response->getBody()->write(json_encode($comptes));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );
   
        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});
// -- 10 -- //

$app->run();
