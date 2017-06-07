## Installation steps for helhum/typo3-distribution

1. Download and install [composer](https://getcomposer.org/download/)
1. Run `composer create-project helhum/typo3-distribution your-project`
1. Enter correct credentials during setup, select `site` as setup type when asked
1. Switch to `your-project` directory
1. Run `vendor/bin/typo3cms server:run`
1. Enter `http://127.0.0.1:8080/typo3/` in your browser to log into the backend

## Change configuration directory layout

Add the following section to you `composer.json` to change the configuration directory structure
to fits your needs. Note that you only need to specify the entry point config for the two contexts,
and inside these files you can specify imports of subsequent config files.

### Default layout in this distribution is

```json
{
    "extra": {
        "helhum/typo3-distribution": {
            "prod-config": "conf/config.yml",
            "dev-config": "conf/dev.config.yml"
        }
    }
}
```

### Example to match Symfony framework default layout

```json
{
    "extra": {
        "helhum/typo3-distribution": {
            "prod-config": "conf/config_prod.yml",
            "dev-config": "conf/config_dev.yml"
        }
    }
}
```

### Example to match Neos Flow framework style layout

```json
{
    "extra": {
        "helhum/typo3-distribution": {
            "prod-config": "Configuration/Production/Settings.yml",
            "dev-config": "Configuration/Development/Settings.yml"
        }
    }
}
```
