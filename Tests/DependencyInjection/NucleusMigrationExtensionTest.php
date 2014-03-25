<?php

namespace Nucleus\Bundle\MigrationBundle\Tests\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class NucleusMigrationExtensionTest extends WebTestCase
{
    public function provideTestCommand()
    {
        return array(
            array('nucleus_migration:report',array(),__DIR__ . '/data/report/report.txt'),
            array('nucleus_migration:runById',array('--taskId=3e0ee316fe1b25adff8b92b0ab5f0601'),__DIR__ . '/data/report/runById.txt'),
            array('nucleus_migration:markAllAsRun',array(),__DIR__ . '/data/report/markAllAsRun.txt'),
            array('nucleus_migration:runAll',array(),__DIR__ . '/data/report/runAll.txt'),
            array('nucleus_migration:manual',array(),__DIR__ . '/data/report/manual.txt',"s\nm\nq\n"),
        );
    }

    /**
     * @dataProvider provideTestCommand
     *
     * @param $command
     * @param $reportFile
     */
    public function testCommand($command,$arguments, $reportFile, $inputStream = null)
    {
        $client = static::createClient();

        $application = new Application($client->getKernel());

        $output = new BufferedOutput();
        $application->setAutoExit(false);

        if($inputStream) {
            $application->getHelperSet()->get('dialog')->setInputStream($this->getInputStream($inputStream));
        }

        //We push a empty argument that is ignore by the application and we push the command itself
        array_unshift($arguments,null,$command);

        $application->run(new ArgvInput($arguments),$output);

        //file_put_contents($reportFile,$this->report($application));

        $this->assertStringEqualsFile($reportFile,$this->report($application));
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }

    private function report(Application $application)
    {
        $output = new BufferedOutput();
        $arguments = array(
            'this will be ignored',
            'nucleus_migration:report'
        );

        $application->run(new ArgvInput($arguments),$output);

        return $output->fetch();
    }
}