[![Actions Status](https://github.com/mehrancodes/laravel-harbor/actions/workflows/run-tests.yml/badge.svg?event=pull_request)](https://github.com/mehrancodes/laravel-harbor/actions)


![logo-harbor](https://github.com/mehrancodes/laravel-harbor/assets/7046255/18186f83-dbd7-4a5a-ad2f-051e8c60c832)

# Laravel Harbor (formerly Veyoze)
### On-Demand Laravel Forge deployments with all setup you need

🚀 **Update (February 25, 2024):** Repository Name Change

We have officially changed the repository name from "Veyoze" to "Laravel Harbor." The new repository URL is now:

```bash
composer require mehrancodes/laravel-harbor
```

Please update your references and links accordingly.

## Table of Contents

- [Introduction](#introduction)
- [Forge Server Requirements (Harbor)](#forge-server-requirements-harbor)
- [Forge Deployment Alert Reference](#forge-deployment-alert-reference)
- [Contributing](#contributing)
- [License](#license)
- [Roadmap](#roadmap)

## Introduction
In today's fast-paced software development world, the ability to quickly and efficiently test new features is a game-changer for teams aiming to maintain a competitive edge. Preview Environments serve as the critical testing grounds for new features, bug fixes, and updates before they are integrated into the main codebase. These environments are crafted on-demand to validate specific git branches, providing a transient yet powerful platform for thorough pre-merge testing.

`"Don't merge until you preview" - with Laravel Harbor, ensure that excellence is the standard, not the exception.`

## Documentation, and Usage Instructions
See the [documentation](https://laravel-harbor.com/docs/introduction/) for detailed installation and usage instructions.

## Forge Server Requirements (Harbor)

Use this checklist when provisioning new Harbor preview servers, especially when running multiple Redis instances on the same machine.

- Configure `FORGE_ORGANIZATION` with your Forge organization slug (new Forge API is org-scoped).
- Create a Forge API token with scopes required by Harbor operations (sites, deploys, environment, commands, daemons/background processes, scheduled jobs, databases, domains/certificates, and webhooks).
- Set SWAP to at least `8 GB` for preview workloads with queues and Redis.
- Keep Horizon process counts conservative to avoid memory pressure under burst traffic.
- Plan Redis memory per instance (`maxmemory`) and make sure total Redis limits plus PHP workers fit within available RAM.
- Prefer one Redis instance per workload role when needed (for example: cache, queue, sessions) and monitor each separately.
- Enable basic Redis and system monitoring (memory usage, evictions, OOM events, and queue lag) before going live.

## Forge Deployment Alert Reference

Use these examples as quick references when Harbor deploys fail in Forge.

### Generic Tunnel / SSH Failure Pattern

- `Tunnel exited with a non-zero code [1].`
- Git ref lock error:
  - `cannot lock ref 'refs/remotes/origin/<branch>' ... (unable to update local ref)`
  - this often appears with `is at <sha> but expected <sha>`
- SSH key reminder from Forge:
  - ensure Forge key exists in both:
    - `/home/forge/.ssh/authorized_keys`
    - `/root/.ssh/authorized_keys`
  - Forge output may be truncated (for example `/home/forge/.ssh/auth...`); treat it as the same `authorized_keys` guidance.

### Composer / Working Directory Failure Pattern

- Dist download fallback:
  - `Failed to download <package> from dist ... make sure the directory is writable and you have internet connectivity`
  - `Now trying to download from source`
- Composer/source failure:
  - `RuntimeException: The provided cwd "" does not exist.`
  - `Could not determine the current working directory`

Suggested checks:
- Confirm deploy directory exists and is writable by `forge`.
- Confirm outbound network access to GitHub from the server.
- Confirm git/composer runtime can run from the expected release path.

### Composer Autoload ParseError Pattern

- Deploy and dependency install can succeed, but fail during:
  - `Illuminate\Foundation\ComposerScripts::postAutoloadDump`
- Example signature:
  - `autoload_static.php ... [ParseError] syntax error, unexpected string content "/php"`
- Typical meaning:
  - a malformed PHP file or generated classmap entry is breaking autoload generation.

Suggested checks:
- Run `composer dump-autoload -o` manually on the server in the release directory.
- Inspect the referenced `vendor/composer/autoload_static.php` line and trace the mapped file causing invalid PHP syntax.
- Validate recently changed PHP files for parse errors before deploy.
- If needed, clear and rebuild dependencies (`rm -rf vendor && composer install`) to rule out corrupted generated autoload files.

### Project Install Cleanup / Clone Failure Pattern

- Forge may fail while preparing the project directory with output like:
  - `rm: cannot remove '.../vendor/composer': Directory not empty`
  - followed by `Couldn't clone repository?`
- Typical meaning:
  - stale/in-use files in the target path blocked cleanup before clone/install.

Suggested checks:
- Verify no running process is holding files in the target project directory.
- Manually clean the project path (`vendor`, temporary composer dirs) and retry install.
- Ensure `forge` user owns the directory tree and has full write permissions.
- Re-verify deploy key access to the repository (Forge key added to source provider with read access).

### Git Context Missing (`not a git repository`) Pattern

- Deploy step may fail with:
  - `Tunnel exited with a non-zero code [128].`
  - `fatal: not a git repository (or any of the parent directories): .git`
- Typical meaning:
  - deploy command ran outside the expected repository root, or `.git` metadata is missing/corrupted in the target path.

Suggested checks:
- Verify Forge site deploy path points to the correct project directory.
- Confirm repository clone completed successfully and `.git` exists in that directory.
- Ensure deploy script `cd` targets the repository root before any git command.
- Re-clone the repository into a clean directory if metadata is missing.

## Contributing

We welcome contributions! Please see our [CONTRIBUTING.md](https://github.com/mehrancodes/laravel-harbor/blob/main/CONTRIBUTING.md) for details on how to contribute and the process for submitting pull requests.

## License

This project is licensed under the MIT License - see the [LICENSE](https://github.com/mehrancodes/laravel-harbor/blob/main/LICENSE) file for details.

## Roadmap

As this CLI tool has been crafted as an MVP and has undergone real-world testing, we're now focusing on enhancing its robustness and expanding its capabilities. Here's what's on our radar:

- 🛠️ **Enhanced Error Handling**: Aiming for comprehensive error handling throughout the entire CLI process to ensure smooth user experiences.

- 📘 **Expanded Documentation**: We'll be adding more examples showcasing the tool's versatility, such as:
    - Using Laravel as an API backend with Nuxt.js on the frontend for SSR handling.
    - Using with projects like WordPress and more.

- 🧪 **Testing**: Preparing unit tests and feature tests to ensure the tool's reliability and stability.
