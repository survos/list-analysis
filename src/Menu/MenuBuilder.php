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
        $menu->addChild('pie', [
            'route' => 'pie'
        ]);
        // ... add more children

        return $this->cleanupMenu($menu);
    }



}