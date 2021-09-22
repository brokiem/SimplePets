<?php

/*
 * Copyright (c) 2021 broki
 * brokiem/SimplePets is licensed under the MIT License
 */

declare(strict_types=1);

namespace brokiem\simplepets\task\async;

use brokiem\simplepets\SimplePets;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;

class CheckUpdateTask extends AsyncTask {

    private const POGGIT_URL = "https://poggit.pmmp.io/releases.json?name=";

    private string $version;
    private string $name;

    public function __construct(SimplePets $plugin) {
        $this->storeLocal([$plugin]);
        $this->name = $plugin->getDescription()->getName();
        $this->version = $plugin->getDescription()->getVersion();
    }

    public function onRun(): void {
        $poggitData = Internet::getURL(self::POGGIT_URL . $this->name);

        if (!$poggitData) {
            return;
        }

        $poggit = json_decode($poggitData, true);

        if (!is_array($poggit)) {
            return;
        }

        $version = "";
        $date = "";
        $updateUrl = "";

        foreach ($poggit as $pog) {
            if (version_compare($this->version, str_replace("-beta", "", $pog["version"]), ">=")) {
                continue;
            }

            $version = $pog["version"];
            $date = $pog["last_state_change_date"];
            $updateUrl = $pog["html_url"];
        }

        $this->setResult([$version, $date, $updateUrl]);
    }

    public function onCompletion(Server $server): void {
        /** @var SimplePets $plugin */
        [$plugin] = $this->fetchLocal();

        if ($this->getResult() === null) {
            $plugin->getLogger()->debug("Async update check failed!");
            return;
        }

        [$latestVersion, $updateDateUnix, $updateUrl] = $this->getResult();

        if ($latestVersion != "" || $updateDateUnix != null || $updateUrl !== "") {
            $updateDate = date("j F Y", (int)$updateDateUnix);

            if ($this->version !== $latestVersion) {
                $plugin->getLogger()->notice("SimplePets v$latestVersion has been released on $updateDate. Download the new update at $updateUrl");
                $plugin->cachedUpdate = [$latestVersion, $updateDate, $updateUrl];
            }
        }
    }
}