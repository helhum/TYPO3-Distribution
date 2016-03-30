<?php
/** @var \TYPO3\Surf\Domain\Model\Deployment $deployment */

use TYPO3\Surf\Domain\Model\Node;
use TYPO3\Surf\Domain\Model\SimpleWorkflow;

$projectName = 'PROJECTID - Dev';
$repositoryUrl = 'PROJECTREPOURL';
$deploymentPath = '/path/to/deploy';
$deploymentHost = 'PROJECTID-dev';

$application = new \TYPO3\Surf\Application\TYPO3\CMS();
$deployment->addApplication($application);

$node = new Node($deploymentHost);
$node->setHostname($deploymentHost);
// Set this if on remote the PHP binary is not available in PATH
//$node->setOption('phpBinaryPathAndFilename', '/usr/local/bin/php5-56LATEST-CLI');

$application->addNode($node);

$application->setOption('projectName', $projectName);
$application->setOption('repositoryUrl', $repositoryUrl);
$application->setOption('branch', getenv('DEPLOY_BRANCH') ?: 'master');

$application->setDeploymentPath($deploymentPath);
$application->setOption('keepReleases', 1);
$application->setOption('composerCommandPath', 'composer');
$application->setOption('TYPO3\\Surf\\Task\\Package\\GitTask[hardClean]', true);
$application->setOption('TYPO3\\Surf\\Task\\TYPO3\\CMS\\CreatePackageStatesTask[phpBinaryPathAndFilename]', 'php');
$application->setContext('Production');

$deployment->onInitialize(function() use ($deployment) {
    /** @var SimpleWorkflow $workflow */
    $workflow = $deployment->getWorkflow();
    $workflow->setEnableRollback(FALSE);

    $workflow->defineTask('Helhum\\SiteFrank\\DefinedTask\\EnvAwareTask', 'TYPO3\\Surf\\Task\\ShellTask', array(
        'command' => array(
            "cp {sharedPath}/.env {releasePath}/.env",
            "cd {releasePath}",
        )
    ));

    $workflow->removeTask('TYPO3\\Surf\\Task\\TYPO3\\CMS\\CompareDatabaseTask');
    $workflow->removeTask('TYPO3\\Surf\\Task\\TYPO3\\CMS\\FlushCachesTask');
    $workflow->beforeStage('migrate', 'Helhum\\SiteFrank\\DefinedTask\\EnvAwareTask');
    $workflow->forStage('finalize', 'TYPO3\\Surf\\Task\\TYPO3\\CMS\\FlushCachesTask');
});
