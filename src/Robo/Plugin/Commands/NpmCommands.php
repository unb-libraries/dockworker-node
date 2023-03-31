<?php

namespace Dockworker\Robo\Plugin\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Dockworker\Core\CommandLauncherTrait;
use Dockworker\Docker\DockerContainerExecTrait;
use Dockworker\DockworkerDaemonCommands;
use Dockworker\IO\DockworkerIO;
use Dockworker\IO\DockworkerIOTrait;

/**
 * Provides commands for running npm in the application's deployed resources.
 */
class NpmCommands extends DockworkerDaemonCommands
{
  use DockerContainerExecTrait;
  use DockworkerIOTrait;
  use CommandLauncherTrait;

  /**
   * Runs a generic npm within this application.
   *
   * @param array $args
   *   The command and arguments to pass to npm.
   * @param string[] $options
   *   An array of options to pass to the command.
   *
   * @option string $env
   *   The environment to run the command in.
   *
   * @command node:npm
   * @aliases npm
   * @usage --env=prod -- update
   */
  public function runGenericNpmCommand(
    array $args,
    array $options = [
      'env' => 'local',
    ]
  ): void {
    $this->executeNpmCommand(
      $this->dockworkerIO,
      $options['env'],
      $args
    );
  }

  /**
   * Executes an NPM command in this application.
   *
   * @param \Dockworker\IO\DockworkerIO $io
   *   The IO to use for input and output.
   * @param string $env
   *   The environment to run the command in.
   * @param string[] $command
   *   The command to run.
   *
   * @option string $env
   *   The environment to run the command in.
   */
  protected function executeNpmCommand(
    DockworkerIO $io,
    string $env,
    array $command
  ): void {
    $io->title('NPM');
    $cmd_base = [
      'npm',
    ];
    $this->executeContainerCommand(
      $env,
      array_merge($cmd_base, $command),
      $this->dockworkerIO,
      'Execute',
      sprintf(
        "[%s] Running 'npm %s'...",
        $env,
        implode(' ', $command)
      )
    );
  }

  /**
   * Installs the application's NPM dependencies.
   *
   * @param CommandData $command_data
   *   The command data.
   *
   * @hook pre-command dockworker:install
   */
  public function executeNpmInstallCommand(CommandData $command_data): void
  {
    $this->initDockworkerIO();
    $args = $command_data->input()->getArguments()['dependencies'];
    if ($only = $command_data->input()->getOptions()['only']) {
      $args = array_merge($args, [
        "--",
        "--save-$only"
      ]);
    }

    $this->setRunOtherCommand(
      $this->dockworkerIO, [
        'npm',
        'install',
        ...$args,
      ]
    );

    $this->setRunOtherCommand($this->dockworkerIO, ['node:npm:write-package']);
    $this->setRunOtherCommand($this->dockworkerIO, ['node:npm:write-lock']);
  }

  /**
   * Uninstalls the application's NPM dependencies.
   *
   * @param CommandData $command_data
   *   The command data.
   *
   * @hook pre-command dockworker:uninstall
   */
  public function executeUninstallNpmCommand(CommandData $command_data): void
  {
    $this->initDockworkerIO();
    $args = $command_data->input()->getArguments()['dependencies'];

    $this->setRunOtherCommand(
      $this->dockworkerIO, [
        'npm',
        'uninstall',
        ...$args,
      ]
    );

    $this->setRunOtherCommand($this->dockworkerIO, ['node:npm:write-package']);
    $this->setRunOtherCommand($this->dockworkerIO, ['node:npm:write-lock']);
  }

  /**
   * Updates the application and its NPM dependencies.
   *
   * @hook post-command dockworker:update
   */
  public function executeNpmUpdateCommand(): void
  {
    $this->initDockworkerIO();
    $this->setRunOtherCommand(
      $this->dockworkerIO, [
        'npm',
        'update',
      ]);

    $this->setRunOtherCommand($this->dockworkerIO, ['node:npm:write-lock']);
  }

  /**
   * Writes this application's package file back to the repository.
   *
   * @option string $env
   *   The environment to copy the file from.
   *
   * @command node:npm:write-package
   * @aliases npm-write-package
   * @usage --env=prod
   */
  public function writePackage(
    array $options = [
      'env' => 'local'
    ]
  ): void
  {
    $container = $this->initGetDeployedContainer(
      $this->dockworkerIO,
      $options['env']
    );
    $this->dockworkerIO->title('Copying Package');
    $container->copyFrom(
      $this->dockworkerIO,
      '/app/package.json',
      $this->applicationRoot . '/app/package.json'
    );
    $this->dockworkerIO->say('Done!');
  }

  /**
   * Writes this application's package lockfile back to the repository.
   *
   * @option string $env
   *   The environment to copy the file from.
   *
   * @command node:npm:write-lock
   * @aliases npm-write-lock
   * @usage --env=prod
   */
  public function writePackageLock(
    array $options = [
      'env' => 'local'
    ]
  ): void
  {
    $container = $this->initGetDeployedContainer(
      $this->dockworkerIO,
      $options['env']
    );
    $this->dockworkerIO->title('Copying Lockfile');
    $container->copyFrom(
      $this->dockworkerIO,
      '/app/package-lock.json',
      $this->applicationRoot . '/app/package-lock.json'
    );
    $this->dockworkerIO->say('Done!');
  }

}
