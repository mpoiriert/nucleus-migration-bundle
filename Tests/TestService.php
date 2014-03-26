<?php

namespace Nucleus\Bundle\MigrationBundle\Tests;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of TestableCommandLineService
 *
 * @author AxelBarbier
 */
class TestService {

    /**
     * Comment from the method
     *
     * @\Nucleus\Bundle\ConsoleBundle\Command\CommandLine(name="test:test")
     *
     * @param string $name The name of the person you want to say hello to
     * @param Output $output The interface output
     */
    public function hello(OutputInterface $output, $name = "unknown")
    {
        $output->write('Hello ' . $name . ' !');
    }
}