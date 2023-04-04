<?php

namespace Dockworker\Robo\Plugin\Commands;

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

}
