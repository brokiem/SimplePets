<?php

declare(strict_types=1);

namespace brokiem\simplepets\database;

use brokiem\simplepets\entity\pets\base\BasePet;
use brokiem\simplepets\entity\pets\base\CustomPet;
use brokiem\simplepets\SimplePets;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use SOFe\AwaitGenerator\Await;

final class Database {
    use SingletonTrait;

    public function registerPet(BasePet|CustomPet $pet): void {
        $db = SimplePets::getInstance()->getDatabase();
        $db->executeInsert(DatabaseQuery::SIMPLEPETS_REGISTERPET, [
            "petType" => $pet->getPetType(),
            "petName" => $pet->getPetName(),
            "petOwner" => $pet->getPetOwner(),
            "petSize" => $pet->getPetSize()
        ]);
    }

    public function savePet(BasePet|CustomPet $pet): void {
        Await::f2c(function() use ($pet) {
            $rows = yield $this->asyncSelect(DatabaseQuery::SIMPLEPETS_GETPET, [
                "petName" => $pet->getPetName(),
                "petOwner" => $pet->getPetOwner()
            ]);

            foreach ($rows as $row) {
                yield $this->asyncInsert(DatabaseQuery::SIMPLEPETS_SAVEPET, [
                    "id" => $row["id"],
                    "petType" => $pet->getPetType(),
                    "petName" => $pet->getPetName(),
                    "petOwner" => $pet->getPetOwner(),
                    "petSize" => $pet->getPetSize()
                ]);
            }
        });
    }

    public function asyncSelect(string $query, array $args): \Generator {
        SimplePets::getInstance()->getDatabase()->executeSelect($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    public function asyncInsert(string $query, array $args): \Generator {
        SimplePets::getInstance()->getDatabase()->executeInsert($query, $args, yield, yield Await::REJECT);
        return yield Await::ONCE;
    }

    public function removePet(BasePet|CustomPet $pet): void {
        $db = SimplePets::getInstance()->getDatabase();

        Await::f2c(function() use ($db, $pet) {
            $rows = yield $this->asyncSelect(DatabaseQuery::SIMPLEPETS_GETPET, [
                "petName" => $pet->getPetName(),
                "petOwner" => $pet->getPetOwner()
            ]);

            foreach ($rows as $row) {
                $db->executeGeneric(DatabaseQuery::SIMPLEPETS_REMOVEPET, ["id" => $row["id"]]);
            }
        });
    }

    public function respawnPet(Player $owner): void {
        SimplePets::getInstance()->getDatabase()->executeSelect(DatabaseQuery::SIMPLEPETS_GETALLPETS, [
            "petOwner" => $owner->getXuid()
        ], function(array $rows) use ($owner) {
            foreach ($rows as $row) {
                $type = $row["petType"];
                $name = $row["petName"];
                $size = $row["petSize"];

                SimplePets::getInstance()->getPetManager()->respawnPet($owner, $type, $name, $size);
            }
        });
    }

    public function getPetData(BasePet|CustomPet $pet, $callable): void {
        $db = SimplePets::getInstance()->getDatabase();
        $db->executeSelect(DatabaseQuery::SIMPLEPETS_GETPET, [
            "petName" => $pet->getPetName(),
            "petOwner" => $pet->getPetOwner()
        ], $callable);
    }
}