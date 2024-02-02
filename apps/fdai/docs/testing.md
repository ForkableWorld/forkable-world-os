# Testing
This document describing how you can run tests within this environment.

### General
Tests use [PHPUnit](https://phpunit.de/) library.

### Commands to run tests

If you want to run single test or all tests in specified directory you can use next steps:

1.Use next local shell command in order to enter into laravel container shell:
```bash
make ssh    # Enter laravel container shell
```
2.Use next laravel container shell command(s) in order to run test(s):
```bash
./vendor/bin/phpunit ./tests/Feature/Controller/ApiKeyControllerTest.php  # Just this single test class
./vendor/bin/phpunit ./tests/Feature/Controller/                          # All tests in this directory
```

### Separate environment for testing
By default, this environment is using `storage/qm_test.sqlite` copied from `tests/fixtures/qm_test.sqlite` for testing.
You need to create a `.env.testing` file to run the tests.


## PhpStorm
You can run tests directly from your IDE PhpStorm. Please follow [PhpStorm](phpstorm.md) documentation in order to do it.
