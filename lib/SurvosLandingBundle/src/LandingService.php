<?php

namespace Survos\LandingBundle;

use EasyCorp\Bundle\EasyAdminBundle\Configuration\ConfigManager;

class LandingService
{
    private $entityClasses;
    private $configManager;
    public function __construct(array $entityClasses)
    {
        $this->entityClasses = $entityClasses;
        // dump($entityClasses); die("Stopped");
    }

    public function setConfigManager(ConfigManager $configManager): self
    {
        $this->configManager = $configManager;
        return $this;
    }

    public function getEntities()
    {
        // return $this->configManager->getBackendConfig();
        return $this->entityClasses;
    }

}

