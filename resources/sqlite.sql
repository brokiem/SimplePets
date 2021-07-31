-- # !mysql
-- # {simplepets
-- #    {init
-- #        {info
CREATE TABLE IF NOT EXISTS simplepets_info
(
    id         TINYINT UNSIGNED PRIMARY KEY,
    db_version TINYINT UNSIGNED NOT NULL DEFAULT 1
);
-- #        }
-- #        {data
CREATE TABLE IF NOT EXISTS simplepets_pets
(
    id       INT UNSIGNED AUTO_INCREMENT,
    petType  VARCHAR(64) NOT NULL,
    petName  VARCHAR(32) NOT NULL,
    petOwner VARCHAR(32) NOT NULL,
    petSize  FLOAT(2)    NOT NULL,
    PRIMARY KEY (id)
)
    -- #        }
-- #    }
-- #    {get-version
-- #        :id int 1
SELECT db_version
FROM simplepets_info
WHERE id = :id;
-- #    }
-- #    {set-version
-- #        :id int 1
-- #        :version int
INSERT INTO simplepets_info (id, db_version)
VALUES (:id, :version)
ON DUPLICATE KEY UPDATE db_version = :version;
-- #    }
-- #    {savepet
-- #        :petType string
-- #        :petName string
-- #        :petOwner string
-- #        :petSize float
INSERT INTO simplepets_pets (petType, petName, petOwner, petSize)
VALUES (:petType, :petName, :petOwner, :petSize);
-- #    }
-- #    {getpet
-- #        :petName string
SELECT id
FROM simplepets_pets
WHERE petName = :petName;
-- #    }
-- #    {removepet
-- #        :id int
DELETE
FROM simplepets_pets
WHERE id = :id;
-- #    }
-- #}