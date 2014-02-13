<?php

namespace Nucleus\Migration;

/**
 * @author Martin Poirier Theoret <mpoiriert@gmail.com>
 */
interface IMigrationTask
{

    /**
     * Return the command name like declare in the symfony console application
     *
     * @return string
     */
    public function getCommandName();

    /**
     * Return the salt of the migration task that will be use to change it's id
     */
    public function getSalt();

    /**
     * Return the parameters of the migration task
     *
     * @return array
     */
    public function getParameters();
}