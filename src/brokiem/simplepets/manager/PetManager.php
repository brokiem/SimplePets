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
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\player\Player;
use pocketmine\world\World;

final class PetManager {

    private array $default_pets = [
        "GoatPet" => GoatPet::class,
        "WolfPet" => WolfPet::class
    ];

    private array $registered_pets = [];

    private array $active_pets = [];

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
            if (is_a($entityClass, CustomPet::class, true)) {
                EntityFactory::getInstance()->register($entityClass, function(World $world, CompoundTag $nbt) use ($entityClass): Entity {
                    return new $entityClass(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
                }, array_merge([$entityClass], $saveNames));
            } else {
                EntityFactory::getInstance()->register($entityClass, function(World $world, CompoundTag $nbt) use ($entityClass): Entity {
                    return new $entityClass(EntityDataHelper::parseLocation($nbt, $world), $nbt);
                }, array_merge([$entityClass], $saveNames));
            }
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

    public function spawnPet(Player $owner, string $petType, string $petName, float $petSize = 1): void {
        $nbt = $this->createBaseNBT($owner->getPosition());
        $pet = $this->createEntity($petType, $owner->getLocation(), $nbt);

        if ($pet !== null) {
            $pet->setPetOwner($owner->getXuid());
            $pet->setPetName($petName);
            $pet->setPetSize($petSize);
            $pet->spawnToAll();

            $this->active_pets[$owner->getName()][$pet->getPetName()] = $pet->getId();
            SimplePets::getInstance()->getDatabaseManager()->registerPet($pet);
        }
    }

    public function respawnPet(Player $owner, string $petType, string $petName, float $petSize = 1): void {
        $nbt = $this->createBaseNBT($owner->getPosition());
        $nbt->setString("petOwner", $owner->getXuid())->setString("petName", $petName)->setInt("petSize", $petSize);
        $pet = $this->createEntity($petType, $owner->getLocation(), $nbt);

        if ($pet !== null) {
            $pet->spawnToAll();

            $this->active_pets[$owner->getName()][$pet->getPetName()] = $pet->getId();
        }
    }

    public function despawnPet(BasePet|CustomPet $pet): void {
        if (!$pet->isFlaggedForDespawn()) {
            $pet->flagForDespawn();
        }
    }

    public function getRegisteredPets(): array {
        return $this->registered_pets;
    }

    public function getActivePets(): array {
        return $this->active_pets;
    }

    public function removeActivePet(Player $owner, string $petName): bool {
        if (isset($this->active_pets[$owner->getName()][$petName])) {
            unset($this->active_pets[$owner->getName()][$petName]);
            return true;
        }

        return false;
    }

    public function saveInventory(BasePet $pet, ListTag $petInventoryTag): void {
        $nbt = CompoundTag::create()->setTag("PetInventory", $petInventoryTag);
        $file = SimplePets::getInstance()->getDataFolder() . "pets_inventory/" . $pet->getPetOwner() . "-" . $pet->getName() . ".dat";
        file_put_contents($file, zlib_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($nbt)), ZLIB_ENCODING_GZIP));
    }

    public function getSavedInventory(BasePet $pet): ?CompoundTag {
        $file = SimplePets::getInstance()->getDataFolder() . "pets_inventory/" . $pet->getPetOwner() . "-" . $pet->getName() . ".dat";

        if (is_file($file)) {
            $decompressed = @zlib_decode(file_get_contents($file));
            return (new LittleEndianNbtSerializer())->read($decompressed)->mustGetCompoundTag();
        }

        return null;
    }

    /**
     * Helper function which creates minimal NBT needed to spawn an entity.
     */
    public function createBaseNBT(Vector3 $pos, ?Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0): CompoundTag {
        return CompoundTag::create()
            ->setTag("Pos", new ListTag([
                new DoubleTag($pos->x),
                new DoubleTag($pos->y),
                new DoubleTag($pos->z)
            ]))
            ->setTag("Motion", new ListTag([
                new DoubleTag($motion !== null ? $motion->x : 0.0),
                new DoubleTag($motion !== null ? $motion->y : 0.0),
                new DoubleTag($motion !== null ? $motion->z : 0.0)
            ]))
            ->setTag("Rotation", new ListTag([
                new FloatTag($yaw),
                new FloatTag($pitch)
            ]));
    }

    public function createEntity(string $type, Location $location, CompoundTag $nbt): null|BasePet|CustomPet {
        if (isset($this->registered_pets[$type])) {
            /** @var BasePet|CustomPet $class */
            $class = $this->registered_pets[$type];

            if (is_a($class, BasePet::class, true)) {
                return new $class($location, $nbt);
            }

            if (is_a($class, CustomPet::class, true)) {
                return new $class($location, Human::parseSkinNBT($nbt), $nbt);
            }
        }

        return null;
    }
}