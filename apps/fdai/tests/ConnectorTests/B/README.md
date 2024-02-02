## Updating expired OAuth access tokens when Connector tests fail

Occasionally, the OAuth tokens we've hard coded in our [connector tests](Api/Connectors) expire. Take these quick steps to update them and fix the tests.

- Retrieve token manually from the Connect page by connecting to the provider
- Dump the credentials table using `php laravel/artisan dump:credentials` from the root of the project
- Your credentials will be in the credentials.csv inside the laravel folder
- Copy the base64 encoded string inside the related variable or pass it to the `createCredentials` function