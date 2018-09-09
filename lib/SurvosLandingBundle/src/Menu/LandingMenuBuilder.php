<?php

namespace Survos\LandingBundle\Menu;

use Knp\Menu\FactoryInterface;

class LandingMenuBuilder
{
    private $factory;

    /**
     * @param FactoryInterface $factory
     *
     * Add any other dependency you need
     */
    public function __construct(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        $menu->addChild('Home', ['route' => 'survos_landing']);
        $menu->addChild('About', ['route' => 'survos_landing']);
        $menu->addChild('Three', ['route' => 'survos_landing']);
        // ... add more children

        return $menu;
    }
}