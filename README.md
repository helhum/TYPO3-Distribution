## Installation steps for helhum/typo3-distribution

1. Download and install [composer](https://getcomposer.org/download/)
1. Run `composer create-project helhum/typo3-distribution your-project`
1. Enter correct credentials during setup, select `site` as setup type when asked
1. Run `cd your-project`
1. Run `cp .env.dist .env`
1. Run `vendor/bin/typo3cms server:run`
1. Enter `http://127.0.0.1:8080/typo3/` in your browser to log into the backend
