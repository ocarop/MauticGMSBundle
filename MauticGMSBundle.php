<?php

// plugins/HelloWorldBundle/HelloWorldBundle.php

namespace MauticPlugin\MauticGMSBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Mautic\PluginBundle\Bundle\PluginBundleBase;
use MauticPlugin\MauticGMSBundle\DependencyInjection\Compiler\OverrideServiceCompilerPass;

class MauticGMSBundle extends PluginBundleBase {

    
    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new OverrideServiceCompilerPass());
    }

    
    public function getParent() {
        return 'MauticSmsBundle';
    }
    
}
