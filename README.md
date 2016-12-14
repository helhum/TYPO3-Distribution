## Installation steps

1. Clone this repository `https://github.com/helhum/TYPO3-Distribution.git -b okon`
1. Download and install [composer](https://getcomposer.org/download/)
2. Run `composer.phar install`
3. Run `vendor/bin/typo3cms install:setup`
4. Enter correct credentials during setup, select `site` as setup type when asked
5. Run `php -S 127.0.0.1:8080 -t web`
6. Enter `http://127.0.0.1:8080/typo3/` in your browser to log into the backend
