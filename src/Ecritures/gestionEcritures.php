<?php
use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Ramsey\Uuid\Uuid;
use Slim\App;


return function ($app,$conn) {
    // --Exercice 2 : récupérer la liste des écritures pour **UN** compte -- //
    $app->get('/comptes/{uuid}/ecritures', function (Request $request, Response $response, array $args) use ($conn) {
        $uuid = $args['uuid'];
        $sql = "SELECT * FROM ecritures WHERE compte_uuid = :uuid";

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':uuid', $uuid);
            $stmt->execute();
            $ecritures = $stmt->fetchAll(PDO::FETCH_OBJ);
            $db = null;

            $response->getBody()->write(json_encode($ecritures));
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
    // -- 2 -- //
};