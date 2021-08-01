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
    id           INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE,
    petType      VARCHAR(64) NOT NULL,
    petName      VARCHAR(32) NOT NULL,
    petOwner     VARCHAR(32) NOT NULL,
    petSize      FLOAT(2)    NOT NULL,
    petBaby      BOOL        NOT NULL,
    petVisible   INT         NOT NULL,
    enableInv    BOOL        NOT NULL,
    enableRiding BOOL        NOT NULL,
    extraData    VARCHAR(10000)
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
-- #    {registerpet
-- #        :petType string
-- #        :petName string
-- #        :petOwner string
-- #        :petSize float
-- #        :petBaby bool
-- #        :petVisible int
-- #        :extraData ?string
-- #        :enableInv bool
-- #        :enableRiding bool
INSERT INTO simplepets_pets (petType, petName, petOwner, petSize, petBaby, petVisible, enableInv, enableRiding,
                             extraData)
VALUES (:petType, :petName, :petOwner, :petSize, :petBaby, :petVisible, :enableInv, :enableRiding, :extraData);
-- #    }
-- #    {savepet
-- #        :id int
-- #        :petType string
-- #        :petName string
-- #        :petOwner string
-- #        :petSize float
-- #        :petBaby bool
-- #        :petVisible int
-- #        :extraData ?string
-- #        :enableInv bool
-- #        :enableRiding bool
UPDATE simplepets_pets
SET petType      = :petType,
    petName      = :petName,
    petOwner     = :petOwner,
    petSize      = :petSize,
    petBaby      = :petBaby,
    petVisible   = :petVisible,
    enableInv    = :enableInv,
    enableRiding = :enableRiding,
    extraData    = :extraData
WHERE id = :id;
-- #    }
-- #    {getpet
-- #        :petName string
-- #        :petOwner string
SELECT *
FROM simplepets_pets
WHERE petName = :petName
  AND petOwner = :petOwner;
-- #    }
-- #    {getallpets
-- #        :petOwner string
SELECT *
FROM simplepets_pets
WHERE petOwner = :petOwner;
-- #    }
-- #    {removepet
-- #        :id int
DELETE
FROM simplepets_pets
WHERE id = :id;
-- #    }
-- #}