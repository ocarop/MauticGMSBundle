<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OverrideServiceCompilerPass
 *
 * @author Oscar
 */
namespace MauticPlugin\MauticGMSBundle\DependencyInjection\Compiler;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideServiceCompilerPass implements CompilerPassInterface {

    public function process(ContainerBuilder $container) {
        //Sobreescribimos dos clases del plugin original SMS de mautic, que usa twilio
        $definition = $container->getDefinition('mautic.sms.api');
        $definition->setClass('MauticPlugin\MauticGMSBundle\Api\GMSApi');
        $definition2 = $container->getDefinition('mautic.integration.twilio');
        $definition2->setClass('MauticPlugin\MauticGMSBundle\Integration\GMSIntegration');        
    }

}
