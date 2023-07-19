-- Supprimer tous les éléments de la table "ecritures"
DELETE FROM ecritures;

-- Supprimer tous les éléments de la table "comptes"
DELETE FROM comptes;

-- Supprimer la table "ecritures"
DROP TABLE IF EXISTS ecritures;

-- Supprimer la table "comptes"
DROP TABLE IF EXISTS comptes;

-- Création de la table "comptes"
CREATE TABLE comptes (
  uuid VARCHAR(36) PRIMARY KEY,
  login VARCHAR(255) NOT NULL DEFAULT '',
  password VARCHAR(255) NOT NULL,
  name VARCHAR(255),
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
);

-- Création de la table "ecritures"
CREATE TABLE ecritures (
  uuid VARCHAR(36),
  compte_uuid VARCHAR(36),
  label VARCHAR(255) NOT NULL DEFAULT '',
  date DATE NULL,
  type ENUM('C', 'D'),
  amount DOUBLE(14,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (uuid, compte_uuid),
  FOREIGN KEY (compte_uuid) REFERENCES comptes(uuid) ON UPDATE RESTRICT ON DELETE CASCADE
);


-- Jeu de données pour la table "comptes"
INSERT INTO comptes (uuid, login, password, name, created_at, updated_at)
VALUES ('12345678-1234-1234-1234-123456789abc', 'john', 'mdp1', 'John Doe', NOW(), NOW());

INSERT INTO comptes (uuid, login, password, name, created_at, updated_at)
VALUES ('87654321-4321-4321-4321-987654321cba', 'amy', 'mdp2', 'Amy Smith', NOW(), NOW());

-- Jeu de données pour la table "écritures"
INSERT INTO ecritures (uuid, compte_uuid, label, date, type, amount, created_at, updated_at)
VALUES ('11111111-1111-1111-1111-111111111111', '12345678-1234-1234-1234-123456789abc', 'Libellé 1', '2023-07-01', 'C', 100.00, NOW(), NOW());

INSERT INTO ecritures (uuid, compte_uuid, label, date, type, amount, created_at, updated_at)
VALUES ('22222222-2222-2222-2222-222222222222', '12345678-1234-1234-1234-123456789abc', 'Libellé 2', '2023-07-02', 'D', 50.00, NOW(), NOW());

INSERT INTO ecritures (uuid, compte_uuid, label, date, type, amount, created_at, updated_at)
VALUES ('33333333-3333-3333-3333-333333333333', '87654321-4321-4321-4321-987654321cba', 'Libellé 3', '2023-07-03', 'C', 75.00, NOW(), NOW());

INSERT INTO ecritures (uuid, compte_uuid, label, date, type, amount, created_at, updated_at)
VALUES ('44444444-4444-4444-4444-444444444444', '87654321-4321-4321-4321-987654321cba', 'Libellé 4', '2023-07-04', 'D', 30.00, NOW(), NOW());
