# php-docker-wrapper

PHP wrapper class for docker CLI (feature incomplete).

## Example

The following example tries to retrieve information about the container `my-container`.

````PHP
$docker = new Docker();

if ($docker->isInstalled()) {

    if ($docker->isContainerExisting('my-container')) {

        if (!$docker->isContainerRunning('my-container')) {
            $docker->start('my-container');
        }

        $containerInfo = $docker->getContainerInfo('my-container');

        // .. do something with it

        $docker->stop('my-container');
    }
}
````
