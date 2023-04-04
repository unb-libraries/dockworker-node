<?php

namespace Dockworker\Robo\Plugin\Commands;

/**
 * Provides commands to copy git hooks into an application repository.
 */
class NodeGitHookInstallCommands extends GitHookInstallCommands
{
  /**
   * Sets up the required git hooks for dockworker-node.
   *
   * @hook post-command git:setup-hooks
   */
  public function setupDrupalGitHooks(): void
  {
    $this->copyGitHookFiles('dockworker-node');
  }
}
