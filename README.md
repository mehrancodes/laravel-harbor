# Veyoze 🛠️

Veyoze is a command-line interface (CLI) tool designed to automate the setup and deployment of preview environments for web applications. It's optimized for creating PR preview environments for Laravel, Nuxt.js, Next.js, and other web applications deployable to Laravel Forge.

## Why Veyoze?

- **Swift Deployment**: Deploy environments swiftly as soon as your PR is ready.
- **Automatic Cleanup**: Ensures no remnants of test environments after PRs are merged or closed.
- **Streamlined Workflow**: Efficiently manage multiple environments, ensuring everyone has their space when needed.

## Getting Started

### Requirements
Before diving in, ensure you have:

- An [app server on Forge](https://forge.laravel.com/docs/servers/types.html#app-servers).
- A Forge [API token](https://forge.laravel.com/docs/1.0/accounts/api.html#create-api-token).

### Setup and Deploy

To utilize Veyoze CLI on pull requests, integrate a GitHub action workflow that activates on PR events. Begin by creating a workflow named `preview-provision.yml`:

```yml
name: preview-provision
on:
  pull_request:
    types: [opened, edited, reopened, ready_for_review]
jobs:
  veyoze-provision:
    runs-on: ubuntu-latest
    container:
      image: kirschbaumdevelopment/laravel-test-runner:8.1
    steps:
        - name: Install Veyoze
          run: composer global require mehrancodes/veyoze -q
        - name: Start Provisioning
          env:
              FORGE_TOKEN: ${{ secrets.FORGE_TOKEN }}
              FORGE_SERVER: ${{ secrets.FORGE_SERVER }}
              FORGE_GIT_REPOSITORY: ${{ github.repository }}
              FORGE_GIT_BRANCH: ${{ github.head_ref }}
              FORGE_DOMAIN: veyoze.com
          run: veyoze provision
```

This workflow:

1. Listens for specific GitHub action events: opened, edited, reopened, ready_for_review
2. Sets up the workflow container and installs Veyoze.
3. Uses environment keys for Veyoze configuration.
4. Generates a URL like **plt-123-add-new-feature.veyoze.com** upon running the `veyoze provision` command.

### FAQ

**Q: Is it secure to use sensitive data like API tokens in our workflow?**

**A:** Yes, when passed securely. We use [GitHub actions secrets](https://docs.github.com/en/actions/security-guides/using-secrets-in-github-actions) to safely pass the Forge token and server ID.

**Q: Why use environment keys for configuration instead of command arguments?**

**A:** We have around 20 configurations and more planned. Using environment keys keeps things organized. However, we're open to suggestions!

**Q: How can we control the conditions under which this workflow runs?**

**A:** Check our [veyoze-laravel-sample workflow](https://github.com/mehrancodes/veyoze-laravel-sample/blob/92fce07b6b63bf665ad2063db7f2ad00fa9f3f31/.github/workflows/pr-preview-provision.yml#L7). It specifies conditions like the PR not being a draft or the PR title containing `[veyoze]`.

**Q: Is there a standalone build for Veyoze CLI?**

**A:** Yes! Grab the latest build [from the releases page](https://github.com/mehrancodes/veyoze/releases). It's quicker to set up and highly recommended.

### Destroy the Site

To dismantle the site after a PR is merged or closed, introduce a new workflow to execute the `veyoze teardown` command. Create a workflow named `preview-teardown.yml`:

```yml
name: preview-teardown
on:
  pull_request:
    types: [closed]
jobs:
  veyoze-teardown:
    runs-on: ubuntu-latest
    container:
      image: kirschbaumdevelopment/laravel-test-runner:8.1
    steps:
        - name: Install Veyoze
          run: composer global require mehrancodes/veyoze -q
        - name: Start Teardown
          env:
              FORGE_TOKEN: ${{ secrets.FORGE_TOKEN }}
              FORGE_SERVER: ${{ secrets.FORGE_SERVER }}
              FORGE_GIT_REPOSITORY: ${{ github.repository }}
              FORGE_GIT_BRANCH: ${{ github.head_ref }}
              FORGE_DOMAIN: veyoze.com
          run: veyoze teardown
```
