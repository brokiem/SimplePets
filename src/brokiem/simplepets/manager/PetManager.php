<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\manager;

use brokiem\simplepets\pets\base\BasePet;
use brokiem\simplepets\pets\base\CustomPet;
use brokiem\simplepets\pets\GoatPet;
use brokiem\simplepets\pets\WolfPet;
use brokiem\simplepets\SimplePets;
use pocketmine\entity\Entity;
use pocketmine\level\Location;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

final class PetManager {

    private array $default_pets = [
        "GoatPet" => GoatPet::class,
        "WolfPet" => WolfPet::class
    ];

    private array $registered_pets = [];
    private array $active_pets = [];
    private array $ridden_pet = [];

    public const VISIBLE_TO_EVERYONE = 0;
    public const VISIBLE_TO_OWNER = 1;
    public const INVISIBLE_TO_EVERYONE = 3;

    public function __construct() {
        foreach ($this->default_pets as $type => $class) {
            self::registerEntity($class, [$type]);
            $this->registerPet($type, $class);
        }
    }

    public static function registerEntity(string $entityClass, array $saveNames = []): void {
        if (!class_exists($entityClass)) {
            throw new \RuntimeException("Class $entityClass not found.");
        }

        $refClass = new \ReflectionClass($entityClass);
        if (is_a($entityClass, BasePet::class, true) || is_a($entityClass, CustomPet::class, true) and !$refClass->isAbstract()) {
            Entity::registerEntity($entityClass, true, $saveNames);
        }
    }

    public function registerPet(string $type, string $class): void {
        if (!class_exists($class)) {
            throw new \RuntimeException("Class $class not found.");
        }

        $refClass = new \ReflectionClass($class);
        if (is_a($class, BasePet::class, true) || is_a($class, CustomPet::class, true) and !$refClass->isAbstract()) {
            $this->registered_pets[$type] = $class;
        }
    }

    public function spawnPet(Player $owner, string $petType, string $petName, float $petSize = 1, bool $petBaby = false, int $petVis = PetManager::VISIBLE_TO_EVERYONE, bool $enableInv = true, bool $enableRiding = true, ?string $extraData = "null"): void {
        $nbt = Entity::createBaseNBT($owner->getPosition());
        $nbt->setString("petOwner", $owner->getXuid());
        $nbt->setString("petName", $petName);
        $nbt->setFloat("petSize", $petSize);
        $nbt->setInt("petBaby", (int)$petBaby);
        $nbt->setInt("petVisibility", $petVis);
        $nbt->setInt("invEnabled", (int)$enableInv);
        $nbt->setInt("ridingEnabled", (int)$enableRiding);
        $nbt->setString("extraData", $extraData ?? "null");
        $pet = $this->createEntity($petType, $owner->getLocation(), $nbt);

        if ($pet !== null) {
            $pet->setPetName($petName);
            $pet->setPetBaby($petBaby);
            $pet->setPetVisibility($petVis);
            $pet->spawnToAll();

            $this->active_pets[$owner->getName()][$pet->getPetName()] = $pet->getId();
            SimplePets::getInstance()->getDatabaseManager()->registerPet($pet);
        }
    }

    public function respawnPet(Player $owner, string $petType, string $petName, float $petSize = 1, bool $petBaby = false, int $petVis = PetManager::VISIBLE_TO_EVERYONE, bool $enableInv = true, bool $enableRiding = true, ?string $extraData = "null"): void {
        $nbt = Entity::createBaseNBT($owner->getPosition());
        $nbt->setString("petOwner", $owner->getXuid());
        $nbt->setString("petName", $petName);
        $nbt->setFloat("petSize", $petSize);
        $nbt->setInt("petBaby", (int)$petBaby);
        $nbt->setInt("petVisibility", $petVis);
        $nbt->setInt("invEnabled", (int)$enableInv);
        $nbt->setInt("ridingEnabled", (int)$enableRiding);
        $nbt->setString("extraData", $extraData ?? "null");
        $pet = $this->createEntity($petType, $owner->getLocation(), $nbt);

        if ($pet !== null) {
            $pet->setPetName($petName);
            $pet->setPetBaby($petBaby);
            $pet->setPetVisibility($petVis);
            $pet->spawnToAll();

            $this->active_pets[$owner->getName()][$pet->getPetName()] = $pet->getId();
        }
    }

    public function addRiddenPet(Player $owner, BasePet|CustomPet $pet): void {
        $this->ridden_pet[$owner->getName()] = $pet;
    }

    public function getRegisteredPets(): array {
        return $this->registered_pets;
    }

    public function getActivePets(): array {
        return $this->active_pets;
    }

    public function getRiddenPet(Player $owner): null|BasePet|CustomPet {
        return $this->ridden_pet[$owner->getName()];
    }

    public function removeRiddenPet(Player $owner, BasePet|CustomPet $pet): void {
        $this->ridden_pet[$owner->getName()] = $pet;
    }

    public function removeActivePet(Player $owner, string $petName): bool {
        if (isset($this->active_pets[$owner->getName()][$petName])) {
            unset($this->active_pets[$owner->getName()][$petName]);
            return true;
        }

        return false;
    }

    public function createEntity(string $type, Location $location, CompoundTag $nbt): null|BasePet|CustomPet {
        if (isset($this->registered_pets[$type])) {
            /** @var BasePet|CustomPet $class */
            $class = $this->registered_pets[$type];

            if (is_a($class, BasePet::class, true) || is_a($class, CustomPet::class, true)) {
                return new $class($location, $nbt);
            }
        }

        return null;
    }
}