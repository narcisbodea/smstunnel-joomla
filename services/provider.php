<?php

/**
 * @package     SMSTunnel for Joomla
 * @copyright   (C) 2026 NARBOWEB SRL <https://smstunnel.io>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Narboweb\Plugin\User\Smstunnel\Extension\Smstunnel;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $config  = (array) PluginHelper::getPlugin('user', 'smstunnel');
                $subject = $container->get(DispatcherInterface::class);

                $plugin = new Smstunnel($subject, $config);
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
