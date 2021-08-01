<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\database;

use brokiem\simplepets\pets\base\BasePet;
use brokiem\simplepets\pets\base\CustomPet;
use brokiem\simplepets\SimplePets;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class Database {
    use SingletonTrait;

    public const SIMPLEPETS_GET_VERSION = "simplepets.get-version";
    public const SIMPLEPETS_GETPET = "simplepets.getpet";
    public const SIMPLEPETS_INIT_DATA = "simplepets.init.data";
    public const SIMPLEPETS_INIT_INFO = "simplepets.init.info";
    public const SIMPLEPETS_REMOVEPET = "simplepets.removepet";
    public const SIMPLEPETS_REGISTERPET = "simplepets.registerpet";
    public const SIMPLEPETS_SAVEPET = "simplepets.savepet";
    public const SIMPLEPETS_SET_VERSION = "simplepets.set-version";
    public const SIMPLEPETS_GETALLPETS = "simplepets.getallpets";

    public function registerPet(BasePet|CustomPet $pet): void {
        $db = SimplePets::getInstance()->getDatabase();
        $db->executeInsert(self::SIMPLEPETS_REGISTERPET, [
            "petType" => $pet->getPetType(),
            "petName" => $pet->getPetName(),
            "petOwner" => $pet->getPetOwner(),
            "petSize" => $pet->getPetSize()
        ]);
    }

    public function savePet(BasePet|CustomPet $pet): void {
        $db = SimplePets::getInstance()->getDatabase();
        $db->executeSelect(self::SIMPLEPETS_GETPET, [
            "petName" => $pet->getPetName(),
            "petOwner" => $pet->getPetOwner()
        ], function(array $rows) use ($pet, $db) {
            foreach ($rows as $row) {
                $db->executeInsert(self::SIMPLEPETS_SAVEPET, [
                    "id" => $row["id"],
                    "petType" => $pet->getPetType(),
                    "petName" => $pet->getPetName(),
                    "petOwner" => $pet->getPetOwner(),
                    "petSize" => $pet->getPetSize()
                ]);
            }
        });
    }

    public function removePet(Player $owner, string $petName): void {
        $db = SimplePets::getInstance()->getDatabase();
        $db->executeSelect(self::SIMPLEPETS_GETPET, [
            "petName" => $petName,
            "petOwner" => $owner->getXuid()
        ], function(array $rows) use ($db) {
            foreach ($rows as $row) {
                $db->executeGeneric(self::SIMPLEPETS_REMOVEPET, ["id" => $row["id"]]);
            }
        });
    }

    public function respawnPet(Player $owner): void {
        SimplePets::getInstance()->getDatabase()->executeSelect(self::SIMPLEPETS_GETALLPETS, [
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
}