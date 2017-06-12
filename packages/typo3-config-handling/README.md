# TYPO3 Config handling package

## Installation steps for helhum/typo3-config-handling

1. Run `composer req helhum/typo3-config-handling`

## Change configuration directory layout

Add the following section to you `composer.json` to change the configuration directory structure
to fits your needs. Note that you only need to specify the entry point config for the two contexts,
and inside these files you can specify imports of subsequent config files.

Optionally for the automatic LocalConfiguration.php config extraction you can specify different
files for main config and extension config being extracted to.

All paths are relative to your root composer.json directory and must not begin with a slash

### Default layout

```json
{
    "extra": {
        "helhum/typo3-config-handling": {
            "prod-config": "conf/config.yml",
            "dev-config": "conf/dev.config.yml",
            "main-config": "conf/config.yml",
            "ext-config": "conf/config.yml"
        }
    }
}
```

### Example to match Symfony framework default layout

```json
{
    "extra": {
        "helhum/typo3-config-handling": {
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
        "helhum/typo3-config-handling": {
            "prod-config": "Configuration/Settings.yml",
            "dev-config": "Configuration/Development/Settings.yml"
        }
    }
}
```
