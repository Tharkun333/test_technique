<?php
use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Ramsey\Uuid\Uuid;
use Slim\App;

// -- Exercice 6,7,8,9 : comptes - GET , POST, PUT, DELETE -- //
return function ($app,$conn) {
    $app->get('/comptes/{uuid}', function (Request $request, Response $response, array $args) use ($conn) {
        $uuid = $args['uuid'];
        $sql = "SELECT * FROM comptes WHERE uuid = :uuid";
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

    $app->post('/comptes', function (Request $request, Response $response, array $args) use ($conn) {
        $data = $request->getParsedBody();
        $login = $data["login"];
        $password = $data["password"];
        $name = $data["name"];

        $sql = "INSERT INTO comptes (uuid, login, password, name) VALUES (:uuid, :login, :password, :name)";
       
        if (strlen($login) < 1 || strlen($password) < 1) {
            $error = array(
                "message" => "the login and password fields must not be null"
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        try {
            $stmt = $conn->prepare($sql);
            $uuid = Uuid::uuid4(); 
            $stmt->bindParam(':login', $login);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':uuid', $uuid);

            $result = $stmt->execute();

            $db = null;

            $responseBody = array(
                "uuid" => $uuid
            );

            $response->getBody()->write(json_encode($responseBody));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(201);
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

    $app->put('/comptes/{uuid}', function (Request $request, Response $response, array $args) use ($conn) {
        $uuid = $args['uuid'];
        $data = $request->getParsedBody();
        $password = $data["password"];
        $name = $data["name"];

        $sql = "UPDATE comptes SET uuid =:uuid, password=:password, name= :name WHERE uuid = :uuid";
       
        if (strlen($password) < 1) {
            $error = array(
                "message" => "the password field must not be null"
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':uuid', $uuid);

            $result = $stmt->execute();


            $db = null;

            
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(204);
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

    $app->delete('/comptes/{uuid}', function (Request $request, Response $response, array $args) use ($conn) {
        $uuid = $args['uuid'];
    
        // Vérifier s'il y a des écritures liées au compte
        $sqlCheckEcritures = "SELECT COUNT(*) as count FROM ecritures WHERE compte_uuid = :uuid";
        $stmtCheckEcritures = $conn->prepare($sqlCheckEcritures);
        $stmtCheckEcritures->bindParam(':uuid', $uuid);
        $stmtCheckEcritures->execute();
        $resultCheckEcritures = $stmtCheckEcritures->fetch(PDO::FETCH_ASSOC);
    
        if ($resultCheckEcritures['count'] > 0) {
            $error = array(
                "message" => "Le compte a des écritures liées et ne peut pas être supprimé."
            );
    
            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }
    
        // Supprimer le compte
        $sqlDeleteCompte = "DELETE FROM comptes WHERE uuid = :uuid";
    
        try {
            $stmtDeleteCompte = $conn->prepare($sqlDeleteCompte);
            $stmtDeleteCompte->bindParam(':uuid', $uuid);
            $resultDeleteCompte = $stmtDeleteCompte->execute();
    
            $response->getBody()->write('');
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(204);

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
};