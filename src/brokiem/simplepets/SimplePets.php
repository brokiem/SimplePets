<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets;

use brokiem\simplepets\command\Command;
use brokiem\simplepets\database\Database;
use brokiem\simplepets\manager\PetManager;
use brokiem\simplepets\pets\base\BasePet;
use brokiem\simplepets\pets\base\CustomPet;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

final class SimplePets extends PluginBase {
    use SingletonTrait;

    private ?DataConnector $database = null;
    private PetManager $petManager;
    private Database $databaseManager;

    private array $players = [];

    protected function onEnable(): void {
        $this->getLogger()->debug("Checking virions");
        $missing = $this->checkVirion();
        if (!empty($missing)) {
            foreach ($missing as $class => $name) {
                $this->getLogger()->alert("Virion $class not found. ($name)");
            }

            $this->getLogger()->alert("Please install the virion or download the plugin from poggit! Disabling plugin...");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }

        self::setInstance($this);

        $this->getLogger()->debug("Registering listener");
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);

        $this->getLogger()->debug("Registering command");
        $this->getServer()->getCommandMap()->register("spet", new Command("spet", "SimplePet commands"));

        $this->getLogger()->debug("Loading database");
        $this->initDatabase();

        $this->getLogger()->debug("Loading pets");
        $this->initPets();

        $this->getLogger()->debug("Plugin successfully enabled");
    }

    private function checkVirion(): array {
        $virions = [
            libasynql::class => "libasynql",
            InvMenu::class => "InvMenu"
        ];
        $missing = [];

        foreach ($virions as $class => $name) {
            if (!class_exists($class)) {
                $missing[$class] = $name;
            }
        }

        return $missing;
    }

    private function initDatabase(): void {
        $this->database = libasynql::create($this, $this->getConfig()->get("database"), [
            "sqlite" => "sqlite.sql",
            "mysql" => "mysql.sql"
        ]);

        $this->database->executeGeneric(Database::SIMPLEPETS_INIT_INFO);
        $this->database->executeGeneric(Database::SIMPLEPETS_INIT_DATA);

        $this->database->waitAll();

        $this->databaseManager = new Database();
    }

    private function initPets(): void {
        if (!is_dir($this->getDataFolder() . "pets_inventory")) {
            mkdir($this->getDataFolder() . "pets_inventory");
        }

        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        $this->petManager = new PetManager();
    }

    public function getDatabase(): DataConnector {
        return $this->database;
    }

    public function getPetManager(): PetManager {
        return $this->petManager;
    }

    public function getDatabaseManager(): Database {
        return $this->databaseManager;
    }

    public function addPlayer(Player $player): void {
        if (!isset($this->players[$player->getXuid()])) {
            $this->players[$player->getXuid()] = $player->getName();
        }
    }

    public function getPlayerByXuid(string $xuid): ?Player {
        if (isset($this->players[$xuid])) {
            return $this->getServer()->getPlayerExact($this->players[$xuid]);
        }

        return null;
    }

    public function removePlayer(Player $player): void {
        if (isset($this->players[$player->getXuid()])) {
            unset($this->players[$player->getXuid()]);
        }
    }

    protected function onDisable(): void {
        foreach ($this->getServer()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                if ($entity instanceof BasePet || $entity instanceof CustomPet) {
                    $entity->saveNBT();
                }
            }
        }

        if ($this->database instanceof DataConnector) {
            $this->database->waitAll();
            $this->database->close();
        }
    }
}