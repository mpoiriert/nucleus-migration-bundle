<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Martin
 * Date: 14-02-03
 * Time: 13:23
 * To change this template use File | Settings | File Templates.
 */

namespace Nucleus\Bundle\CoreBundle\Tests\DependencyInjection;


use Nucleus\Bundle\MigrationBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function provideTestConfiguration()
    {
        return array(
            array(array(),array('versions'=>array(),'tasks'=>array()),null),
        );
    }

    /**
     * @dataProvider provideTestConfiguration
     *
     * @param array $annotationConfiguration
     * @param array $expected
     * @param string $expectedException
     * @throws \Exception
     */
    public function testAnnotationContainerGeneratorsConfiguration(array $nucleus_migration, array $expected = null, $expectedException = null)
    {
        $configurationTest = compact('nucleus_migration');

        $processor = new Processor();
        $migrationConfiguration = new Configuration();

        try {
            $processedConfiguration = $processor->processConfiguration(
                $migrationConfiguration,
                $configurationTest
            );

            if(!is_null($expectedException)) {
                $this->fail('A exception with message containing [' . $expectedException . '] should have been thrown');
            }

            if($expected === null) {
                $expected = $nucleus_migration;
            }

            $this->assertEquals($expected, $processedConfiguration);
            return;
        } catch(InvalidConfigurationException $e) {
            if(is_null($expectedException)) {
                throw $e;
            }
        }

        $this->assertTrue(
            strpos($e->getMessage(),$expectedException) !== false,
            'The exception message is not valid [' . $e->getMessage() . ']'
        );
    }
}