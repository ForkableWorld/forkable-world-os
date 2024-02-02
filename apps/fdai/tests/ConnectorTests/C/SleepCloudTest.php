<?php /** @noinspection PhpUnhandledExceptionInspection */
namespace Tests\ConnectorTests\C;
use Tests\ConnectorTests\ConnectorTestCase;
class SleepCloudTest extends ConnectorTestCase
{
    public function testSleepCloud()
    {
        /*
        ## Updating expired OAuth access tokens when Connector tests fail
        Occasionally, the OAuth tokens we've hard coded in our [connector tests](Api/Connectors) expire. Take these quick steps to update them and fix the tests.
        - Retrieve token manually from the Connect page by connecting to the provider
        - Dump the credentials table using `php laravel/artisan dump:credentials` from the root of the project
        - Your credentials will be in the credentials.csv inside the laravel folder
        - Copy the base64 encoded string inside the related variable or pass it to the `createCredentials` function
        */

        $this->connectorName = 'sleepcloud';
        $key = 'token';
        $token = 'YsDHqstZIUJFj2Op5mItSglASpIugw4o2OxtO32TiuVVZlnAu4ia6XXgPKRxJkkWnnoD4J3PKqNc1NhtzuBfSdrVgkUC2GmySecAyE8M4/MtsYVqvWKKn/Xjp80QrrRszW4GVJyqEpR9FATfOZhcA/IKsXtHRCin89dESwJ2ulpdzsKwpjc5Xd/tSwYZxn7XDEFl7y8N9EANLqjyh8mvRAfPbliN/8GpHGkCZkkqUfkefk18wZvK8/moOiNpNltT4ifmuRCHlzBmmwfNgKZrTcl0DSZYIsjEpk6wW+mAl+V3gBjmu4eczQbi0bsw1Li71/ax/gkCEQ5rwgJOeFRi/vjGGbL8a1I57jNqV5Ipza+poOg54cs9VvJLbZckg46gjoCBp5YEB9sG5sy6Ou2GLmchYNF6vLNaG1eUyVdAzUcSuDpR3LGhaPhloWEM8V+UrnpjdfMYCYlNDr+Qv0g+Tu+ei4u4L9cEHQqQDvaY+XWmnq8L1W947rRrk2keQnQcnSi3nE9YNOo/7mTxBmPRwuc2u4akCAZNypckoSJFEtDz5ZPSrMOeBMgsrY7NIgH1jVCDsREmuGlIlW5QL+/t4o3F6Xinhkx5sv09MWWxM4ivI57Xc9XDuP2ebOtnvwQR4iOJFHaBdSRxKcMUajxAWgbc8lpYLL5HLH/DTqe3jAbgq5bNe0V+4PubQGXMqzPgdV6I+gj2+wmU+0euJjSwJi7ZayGBWswLqjMY4rmNVBREphKA+g8rRhfz+6VpIoHYIocGXmCb0afvagZGdF/rqTou9P7acuUXAYKfdLbaLqxWEz/0xIESlQi5UMWyzwv/TmDRUlrReyYlIODWDeqVbvACCth1yXHn/n9ki862uYcPKxS5NVS2lBoB9c7g5i0vEmG5cKb+YGuy9MN8KA5vF8fVKyt3NKMyJfXdNJDslyH39CZif0mlGFL3rByVC6az7+mWTTaBNzb0bJIaR4CLPygCIp2UVuk4ey1+NAJ1F8Ae6PZJbWGFJPVQhw0xhWb7hbpNkXqkHZ7t40LnVRW+WNbDaAodJe7qgCm1wkESqvbXHRmQng6D2mFKpgDrfHNIUKg+ry3WimPo8dyIhIn89VJ2f4AnMY7nKFxJ5perY+/OjCJw/dSg/0ZehcrS3wK16iEdkgpW6BDDVAhdukeC5UDmSxosZSzDH3ujAALSJTZriFMtVje+8kT0W2hli+F3DUVuQWVeq6HJ0IHrFICY6HxoEYfgbAP9CertliXOXlMf0ebL01uB9Uev8kibT29nOqEscZP8gTdgU9n/hfJUNM6mov73Xrw7+JGyuiTFoz91i9313RVWhaQ8xv5rb5di0oKhMx259rXg9v5sbWtzpsihy6eZJSaJG18gjosBchGKNWOFUinl8Sc0C/iO/bPU2QBMXWg1Vv+2LAl7Hjn0N/V0DzaXvlys/3PsWhJSWHcLTXjlFSlmrYdjvVqOvszfRfw008Weh0iP1WyimZuyHyNskC6Ix5xb8B066t7TIb4puW/guJfwTaUmcj4opJ8dR6ZNOqLxZ23MM4z6/p4AyGH6d9OX84ISFmQm+IrsR4Q=';
        $parameters = [
            'source' => 56,
            'variables' =>
                [
                    'Sleep Duration',
                    'Sleep Cycles',
                    'Sleep Noise Level',
                    //"Sleep Rating",
                    //"Deep Sleep",
                ],
        ];
        $this->skipTest('Failing!');
        //$this->updateTestHelper($this->getUserId(), $this->getConnectorName(), $key, $token, $parameters);
    }
}
