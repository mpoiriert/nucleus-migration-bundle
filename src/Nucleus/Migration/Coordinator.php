<?php

namespace Nucleus\Migration;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Nucleus\Application\IVariableRegistry;

class Coordinator
{
    private static $variableNamespace = 'migration';

    /**
     * @var array
     */
    private $tasks = array();

    private $versions = array();

    /**
     * @var IVariableRegistry
     */
    private $applicationVariable;

    public function __construct(IVariableRegistry $variableRegistry)
    {
        $this->applicationVariable = $variableRegistry;
    }

    public function setVersions(array $versions)
    {
        $this->versions = $versions;
    }

    public function addTask($version, $command, array $parameters = array(), $salt = null)
    {
        $task = new MigrationTask($command,$parameters,$salt);

        $uniqueId = $this->getTaskUniqueId($task);

        if(isset($this->tasks[$version][$uniqueId])) {
            throw new \LogicException('The migration task [' . $this->getTaskFullName($task) . '] is already set. Change the salt if you want to rerun the same task twice.');
        }

        $this->tasks[$version][$uniqueId] = $task;
    }

    /**
     * Run all the migration mask
     *
     * @\Nucleus\Console\CommandLine(name="nucleus_migration:runAll")
     */
    public function runAll(OutputInterface $output, Command $parentCommand)
    {
        foreach ($this->versions as $version) {
            if(!isset($this->tasks[$version])) {
                continue;
            }
            foreach($this->tasks[$version] as $migrationTask) {
                if (!$this->asBeenRun($migrationTask)) {
                    $this->runTask($migrationTask,$parentCommand->getApplication(),$output);
                    $output->writeln('Task [' . $this->getTaskFullName($migrationTask) . '] as been run');
                }
            }
        }
    }

    private function getTaskUniqueId(IMigrationTask $migrationTask)
    {
        return md5($migrationTask->getCommandName() . serialize($migrationTask->getParameters()) . $migrationTask->getSalt());
    }

    private function markAsRun(IMigrationTask $migrationTask)
    {
        $this->applicationVariable->set(
            $this->getTaskUniqueId($migrationTask),true, self::$variableNamespace
        );
    }

    private function asBeenRun(IMigrationTask $migrationTask)
    {
        return $this->applicationVariable->has(
            $this->getTaskUniqueId($migrationTask), self::$variableNamespace
        );
    }

    /**
     * Manually run the migration tasked
     *
     * @\Nucleus\Console\CommandLine(name="nucleus_migration:manual")
     */
    public function manual(OutputInterface $output, Command $parentCommand)
    {
        $skipAlreadyRun = false;

        $dialog = $parentCommand->getApplication()->getHelperSet()->get('dialog');

        foreach ($this->versions as $version) {
            if(!isset($this->tasks[$version])) {
                continue;
            }
            foreach($this->tasks[$version] as $migrationTask) {
                if($skipAlreadyRun && $this->asBeenRun($migrationTask)) {
                    $output->writeln('Skip [' . $this->getTaskFullName($migrationTask));
                    continue;
                }
                $result = $this->promptTask($dialog,$migrationTask,$output);
                switch($result) {
                    case 'r':
                        $this->runTask($migrationTask,$parentCommand->getApplication(),$output);
                        break;
                    case 's':
                        $output->writeln('Skipped');
                        break;
                    case 'm':
                        $this->markAsRun($migrationTask);
                        $output->writeln('Mark As Run');
                        break;
                    case 'a':
                        $skipAlreadyRun = true;
                        break;
                    case 'q':
                        return;
                }
            }
        }
    }

    /**
     * Display a report of registered migration task
     *
     * @\Nucleus\Console\CommandLine(name="nucleus_migration:report")
     */
    public function report(OutputInterface $output, Command $parentCommand)
    {
        $table = $parentCommand->getApplication()->getHelperSet()->get('table');
        $table->setHeaders(array('Version','Status','Id','Command','Parameters','Salt'));
        $table->setRows(array());
        $output->writeln('Migration report');
        foreach ($this->versions as $version) {
            if(!isset($this->tasks[$version])) {
                continue;
            }

            foreach($this->tasks[$version] as $migrationTask) {
                $table->addRow(
                    array(
                        $version,
                        $this->asBeenRun($migrationTask) ? 'Has Been Run' : 'To Run',
                        $this->getTaskUniqueId($migrationTask),
                        $migrationTask->getCommandName(),
                        json_encode($migrationTask->getParameters()),
                        $migrationTask->getSalt()
                    )
                );
            }
        }

        $table->render($output);
    }

    private function getTaskFullName(IMigrationTask $migrationTask)
    {
        return sprintf(
            'Id: %s, Command: %s, Parameters: %s, Salt: %s',
            $this->getTaskUniqueId($migrationTask),
            $migrationTask->getCommandName(),
            json_encode($migrationTask->getParameters()),
            $migrationTask->getSalt()
        );
    }

    private function promptTask(DialogHelper $helper, IMigrationTask $migrationTask, OutputInterface $output)
    {
        $question = "Task: " . $this->getTaskFullName($migrationTask) . "\n";
        if($this->asBeenRun($migrationTask)) {
            $question .= "(Already runned)";
        }

        return $helper->select(
            $output,
            $question,
            array('r'=>'Run','s'=>'Skip','m'=>'Mark As Run','a'=>'Skip All Already Run','q'=>'Quit')
        );
    }

    /**
     * Mark all the migration task as run
     *
     * @\Nucleus\Console\CommandLine(name="nucleus_migration:markAllAsRun")
     */
    public function markAllAsRun()
    {
        foreach ($this->tasks as $versionTasks) {
            foreach ($versionTasks as $migrationTask) {
                $this->markAsRun($migrationTask);
            }
        }
    }

    /**
     * Run a task by it's id, use the migration:report to see all the task and their id
     *
     * @\Nucleus\Console\CommandLine(name="nucleus_migration:runById")
     *
     * @param string $taskId The task id you want to run
     */
    public function runById($taskId, OutputInterface $output, Command $parentCommand)
    {
        if($migrationTask = $this->loadTaskById($taskId)) {
            $this->runTask($migrationTask,$parentCommand->getApplication(),$output);
            $output->writeln('Task id [' . $taskId . '] has been run');
        } else {
            $output->writeln('Task id [' . $taskId . '] not found');
        }

    }

    /**
     * @param string $taskId
     *
     * @return IMigrationTask|null
     */
    private function loadTaskById($taskId)
    {
        foreach ($this->tasks as $versionTasks) {
            foreach ($versionTasks as $migrationTask) {
                if($this->getTaskUniqueId($migrationTask) == $taskId) {
                    return $migrationTask;
                }
            }
        }
    }

    /**
     * @param IMigrationTask $task
     * @throws Exception
     */
    private function runTask(IMigrationTask $task, Application $application, OutputInterface $output)
    {
        try {
            $command = $application->get($task->getCommandName());

            $arguments = array_merge(
                array('command'=>$task->getCommandName()),
                $task->getParameters()
            );

            $input = new ArrayInput($arguments);
            $command->run($input, $output);
            $this->markAsRun($task);
        } catch (Exception $e) {
            throw $e;
        }
    }
}