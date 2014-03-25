<?php

namespace Nucleus\Bundle\MigrationBundle\Migration;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
class MigrationTask implements IMigrationTask
{

    private $commandName;
    private $parameters;
    private $salt;

    public function __construct($commandName, array $parameters, $salt = null)
    {
        $this->commandName = $commandName;
        $this->parameters = $parameters;
        $this->salt = $salt;
    }

    /**
     * Return the command name like declare in the symfony console application
     *
     * @return string
     */
    public function getCommandName()
    {
        return $this->commandName;
    }

    /**
     * Return the salt of the migration task that will be use to change it's id
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Return the parameters of the migration task
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }
}