<?php

declare(strict_types=1);

namespace brokiem\simplepets;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

final class EventListener implements Listener {

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();

        SimplePets::getInstance()->getDatabaseManager()->respawnPet($player);
    }

    public function onQuit(PlayerQuitEvent $event): void {
        $player = $event->getPlayer();

        if (isset(SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()])) {
            foreach (SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()] as $petName => $petId) {
                $pet = $player->getServer()->getWorldManager()->findEntity($petId);

                if (($pet !== null) && !$pet->isFlaggedForDespawn()) {
                    $pet->flagForDespawn();
                    unset(SimplePets::getInstance()->getPetManager()->getActivePets()[$player->getName()][$petName]);
                }
            }
        }
    }
}