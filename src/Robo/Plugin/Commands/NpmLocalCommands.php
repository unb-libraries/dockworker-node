<?php

namespace Dockworker\Robo\Plugin\Commands;

use Consolidation\AnnotatedCommand\CommandData;
use Dockworker\Cli\CliCommandTrait;
use Dockworker\DockworkerDaemonCommands;
use Dockworker\DockworkerException;
use Dockworker\IO\DockworkerIOTrait;

/**
 * Provides NPM commands running outside of any deployed resource.
 */
class NpmLocalCommands extends DockworkerDaemonCommands {

  use CliCommandTrait;
  use DockworkerIOTrait;

  /**
   * Lint this application's source code.
   *
   * @command node:npm:lint
   * @aliases npm-lint
   */
  public function lint(): void
  {
    $cmd = $this->executeCliCommand(
      ['npm', 'run', 'lint'],
      $this->dockworkerIO,
      null,
      'Linting source code',
      '',
      true
    );

    if ($cmd && $cmd->getExitCode() !== 0) {
      throw new DockworkerException(
        $cmd->getErrorOutput()
          ?: "NPM command failed with exit code {$cmd->getExitCode()}");
    }
  }

  /**
   * Execute this application's tests.
   *
   * @hook post-command test:all
   */
  public function runNpmTests(): void
  {
    $this->initDockworkerIO();
    $cmd = $this->executeCliCommand(
      ['npm', 'run', 'test'],
      $this->dockworkerIO,
      null,
      'Running tests',
      '',
      true
    );

    if ($cmd && $cmd->getExitCode() !== 0) {
      throw new DockworkerException(
        $cmd->getErrorOutput()
          ?: "NPM command failed with exit code {$cmd->getExitCode()}");
    }
  }

  /**
   * Execute this application's unit tests.
   *
   * @hook post-command test:unit
   */
  public function runNpmUnitTests(): void
  {
    $this->initDockworkerIO();
    $cmd = $this->executeCliCommand(
      ['npm', 'run', 'test:unit'],
      $this->dockworkerIO,
      null,
      'Running unit tests',
      '',
      true
    );

    if ($cmd && $cmd->getExitCode() !== 0) {
      throw new DockworkerException(
        $cmd->getErrorOutput()
          ?: "NPM command failed with exit code {$cmd->getExitCode()}");
    }
  }

  /**
   * Execute this application's e2e tests.
   *
   * @hook post-command test:e2e
   */
  public function runNpmE2eTests($result, CommandData $command_data): void
  {
    $this->initDockworkerIO();

    $headless = $command_data->input()->getOption('headless');
    $cmd = $this->executeCliCommand(
      [
        'npm',
        'run',
        !$headless
          ? 'test:e2e'
          : 'test:e2e:headless'
      ],
      $this->dockworkerIO,
      null,
      'Running end-to-end tests',
      '',
      true
    );

    if ($cmd && $cmd->getExitCode() !== 0) {
      throw new DockworkerException(
        $cmd->getErrorOutput()
          ?: "NPM command failed with exit code {$cmd->getExitCode()}");
    }
  }

}
