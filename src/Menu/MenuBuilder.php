<?php


namespace App\Menu;


use Survos\LandingBundle\Menu\LandingMenuBuilder;

class MenuBuilder extends LandingMenuBuilder
{

    public function createMainMenu(array $options)
    {
        $menu = $this->factory->createItem('root');

        $menu->setChildrenAttribute('class', 'nav navbar-nav mr-auto');
        $menu->addChild('Home', ['route' => 'survos_landing'])
            ->setAttribute('icon', 'fa fa-home');
        $menu->addChild('xx', ['route' => 'survos_landing']);
        $menu->addChild('Three', ['route' => 'survos_landing']);
        // ... add more children

        return $this->cleanupMenu($menu);
    }



}