nucleus-migration-bundle
========================

With this bundle you will be able to manage migration script. By configuration you will be able to call
multiple command like you would have done with the CLI.

To use it in your application you must register 3 bundles since there is a dependency on [nucleus-bundle](https://github.com/mpoiriert/nucleus-bundle)
and [nucleus-console-bundle](https://github.com/mpoiriert/nucleus-console-bundle)

    <?php

    // in AppKernel::registerBundles()
    $bundles = array(
        // ...
        new Nucleus\Bundle\CoreBundle\NucleusCoreBundle(),
        new Nucleus\Bundle\BinderBundle\NucleusConsoleBundle(),
        new Nucleus\Bundle\BinderBundle\NucleusMigrationBundle(),
        // ...
    );

From there you must do the configuration:

    nucleus_migration:
        versions: [v1,v2]
        tasks:
            v1:
                - {command: "command:name1", parameters: {} }
                - {command: "command:name2", parameters: {param1: value1}, salt: first}
                - {command: "command:name3", parameters: {param1: value1}, salt: second}
            v2:
                ...
            v3:
                ...

This configuration files tell the system that you want to execute the migration version v1 and v2. The tasks of the v3
version will not be executed since the v3 is not mention in the **versions** configuration.

The tasks themselves are configure under the corresponding version section under tasks.

There is 5 different command that are available for the migration system:

    nucleus_migration:report
        Will output a report of all the task with a unique id and if they have been run or not

    nucleus_migration:runAll
        Will run all the migration script (once) configure in the specific order.

    nucleus_migration:markAllAsRun
        Will mark all the task like they have been run

    nucleus_migration:runById
        Will run a task by it's id. The task will be run event if it already have been run. You can fin the id of the
        task in the report

    nucleus_migration:manual
        Allow to run the execution flow manually. You will be prompt on each task if you want to run them or not


The id of a task is generated base on the command name, the parameters and a salt in case you need to run a task twice
with the same parameter. The version name is not use for the id, if you move a task from a version to another one
it will have the same id.

**Make sure to override the %nucleus.variable_registry.class% parameter for the nucleus.variable_registry service to be
persistent. The one provided by default just keep information in memory.**

Note that the migration system just support moving forward in version, you need to do backup and have a restore plan
on your own if you want to go backward. From my own experience you should build a system that is always backward
compatible and sometime it's almost impossible to restore data via a script so be sure of what you do.

To create new command easily take a look at the [nucleus-console-bundle](https://github.com/mpoiriert/nucleus-console-bundle).