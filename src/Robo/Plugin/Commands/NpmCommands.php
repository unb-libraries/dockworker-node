<?php

namespace Dockworker\Robo\Plugin\Commands;

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
   * @param string $args
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
    string $args,
    array $options = [
      'env' => 'local',
    ]
  ): void {
    $args_array = explode(' ', $args);
    $this->executeNpmCommand(
      $this->dockworkerIO,
      $options['env'],
      $args_array
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
      'Generating ULI',
      sprintf(
        "[%s] Running 'npm %s'...",
        $env,
        implode(' ', $command)
      )
    );
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
  }

}
