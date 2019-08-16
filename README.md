## Installation steps for helhum/typo3-distribution

### Install using ddev (recommended)
1. Download and install [ddev](https://ddev.readthedocs.io/en/stable/#installation)
1. Clone the repository `git clone https://github.com/helhum/TYPO3-Distribution.git your-project`
1. Run `cd your-project`
1. Checkout the branch matching your TYPO3 version (e.g. `git checkout origin/9.5 -b 9.5`)
1. Run `ddev start`
1. Open `https://awesome-typo3.test/typo3/` in your browser to log into the backend

### Install in any environment
1. Download and install [composer](https://getcomposer.org/download/)
1. Run `composer create-project helhum/typo3-distribution your-project`
1. Enter correct credentials during setup, select `site` as setup type when asked
1. Run `cd your-project`
1. Run `vendor/bin/typo3cms server:run`
1. Enter `http://127.0.0.1:8080/typo3/` in your browser to log into the backend
