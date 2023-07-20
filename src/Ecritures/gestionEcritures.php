<?php
use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;
use Ramsey\Uuid\Uuid;
use Slim\App;


return function ($app,$conn) {
    // -- Exercice 2 : récupérer la liste des écritures pour **UN** compte -- //
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

    
    // -- Exercice 3 : ajout d'une ecriture **DANS UN** compte -- //
    $app->post('/comptes/{uuid}/ecritures', function (Request $request, Response $response, array $args) use ($conn) {
        $data = $request->getParsedBody();
        $compte_uuid = $args['uuid'];
        $label = $data["label"];
        $date = $data["date"];
        $type = $data["type"];
        $amount = $data["amount"];

        $sql = "INSERT INTO ecritures (uuid, compte_uuid, label, date, type, amount) VALUES (:uuid, :compte_uuid, :label, :date, :type, :amount)";
        
        // Vérification de la validité de la date
        $formattedDate = formatDate($date);
        if ($formattedDate === false) {
            $error = array(
                "message" => "Invalid date format. Please provide a date in the format 'dd/mm/yyyy'."
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        // Vérification du montant non négatif
        if ($amount < 0) {
            $error = array(
                "message" => "Amount cannot be negative"
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        try {
            $stmt = $conn->prepare($sql);
            $uuid = Uuid::uuid4(); 
            $stmt->bindParam(':uuid', $uuid);
            $stmt->bindParam(':compte_uuid', $compte_uuid);
            $stmt->bindParam(':label', $label);
            $stmt->bindParam(':date', $formattedDate);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':amount', $amount);

            $stmt->execute();

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

    function formatDate($date) {
        $datePattern = "/^(\d{2})\/(\d{2})\/(\d{4})$/";
        if (!preg_match($datePattern, $date, $dateParts)) {
            return false;
        }
        $day = intval($dateParts[1]);
        $month = intval($dateParts[2]);
        $year = intval($dateParts[3]);
        if (!checkdate($month, $day, $year)) {
            return false;
        }
        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
    // -- 3 -- //

    // -- Exercice 4 : modifier une écriture -- //
    $app->put('/comptes/{compte_uuid}/ecritures/{ecriture_uuid}', function (Request $request, Response $response, array $args) use ($conn) {
        $compte_uuid = $args['compte_uuid'];
        $ecriture_uuid = $args['ecriture_uuid'];
        $data = $request->getParsedBody();
    
        // Récupérer tous les champs du corps de la requête
        $uuid = $data['uuid'];
        $label = $data['label'];
        $date = $data["date"];
        $type = $data["type"];
        $amount = $data["amount"];
        // Autres champs à récupérer
    
        $sql = "UPDATE ecritures SET uuid = :uuid, label = :label,date = :date,type = :type,amount = :amount WHERE compte_uuid = :compte_uuid AND uuid = :uuid";

        // Vérification uuid == ecriture_uuid
        if (strcmp($uuid, $ecriture_uuid)) {
            $error = array(
                "message" => "Invalid uuid. ". $uuid." != " . $ecriture_uuid 
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

    // Vérification de la date
        $formattedDate = formatDate($date);
        if ($formattedDate === false) {
            $error = array(
                "message" => "Invalid date format. Please provide a date in the format 'dd/mm/yyyy'."
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }

        // Vérification du montant non négatif
        if ($amount < 0) {
            $error = array(
                "message" => "Amount cannot be negative"
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);
        }
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':uuid', $uuid);
            $stmt->bindParam(':label', $label);
            $stmt->bindParam(':date', $formattedDate);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':amount', $amount);
            $stmt->bindParam(':compte_uuid', $compte_uuid);
        
            $result = $stmt->execute();

            $db = null;

            if ($result) {
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(204);
            } else {
                $error = array(
                    "message" => "Failed to update ecriture"
                );
                $response->getBody()->write(json_encode($error));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(500);
            }

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

    // -- 4 -- //

    // -- Exercice 5 : supprimer une écriture -- //
    $app->delete('/comptes/{compte_uuid}/ecritures/{ecriture_uuid}', function (Request $request, Response $response, array $args) use ($conn) {
        $compte_uuid = $args['compte_uuid'];
        $ecriture_uuid = $args['ecriture_uuid'];
    
        $sql = "DELETE FROM ecritures WHERE compte_uuid = :compte_uuid AND uuid = :ecriture_uuid";
    
        try {
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':compte_uuid', $compte_uuid);
            $stmt->bindParam(':ecriture_uuid', $ecriture_uuid);
        
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
    // -- 5 -- //    
};