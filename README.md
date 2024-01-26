# Veyoze - Beta version ⚡️ 🛠️

![logo-background-blue-sky-small](https://github.com/mehrancodes/veyoze/assets/7046255/2727ce3d-31c4-4a97-bafc-e461706ff219)

### On-Demand deployments with all setup you need

## Table of Contents

- [Introduction](#introduction)
- [Contributing](#contributing)
- [License](#license)
- [Acknowledgments](#acknowledgments)
- [Roadmap](#roadmap)

## Introduction
In today's fast-paced software development world, the ability to quickly and efficiently test new features is a game-changer for teams aiming to maintain a competitive edge. Preview Environments serve as the critical testing grounds for new features, bug fixes, and updates before they are integrated into the main codebase. These environments are crafted on-demand to validate specific git branches, providing a transient yet powerful platform for thorough pre-merge testing.

`"Don't merge until you preview" - with Veyoze, ensure that excellence is the standard, not the exception.`

## Documentation, and Usage Instructions
See the [documentation](https://veyoze.com/docs/introduction/) for detailed installation and usage instructions.

## Contributing

We welcome contributions! Please see our [CONTRIBUTING.md](https://github.com/mehrancodes/veyoze/blob/main/CONTRIBUTING.md) for details on how to contribute and the process for submitting pull requests.

## License

This project is licensed under the MIT License - see the [LICENSE.md](https://github.com/mehrancodes/veyoze/blob/main/LICENSE.md) file for details.


## Acknowledgments

**Q: Is it secure to use sensitive data like API tokens in our workflow?**

**A:** Yes, when passed securely. We use [GitHub actions secrets](https://docs.github.com/en/actions/security-guides/using-secrets-in-github-actions) to safely pass the Forge token and server ID.

**Q: Can we get a shorter link for long branch names?**

**A:** Absolutely! By configuring the [Subdomain Pattern](https://github.com/mehrancodes/veyoze#forge_subdomain_pattern) you can shorten the domain, e.g., **plt-123.veyoze.com**.

**Q: Why use environment keys for configuration instead of command arguments?**

**A:** We have around 20 configurations and more planned. Using environment keys keeps things organized. However, we're open to suggestions!

**Q: How can we control the conditions under which this workflow runs?**

**A:** Check our [veyoze-laravel-sample workflow](https://github.com/mehrancodes/veyoze-laravel-sample/blob/92fce07b6b63bf665ad2063db7f2ad00fa9f3f31/.github/workflows/pr-preview-provision.yml#L7). It specifies conditions like the PR not being a draft or the PR title containing `[veyoze]`.

**Q: Is there a standalone build for Veyoze CLI?**

**A:** Yes! Grab the latest build [from the releases page](https://github.com/mehrancodes/veyoze/releases). It's quicker to set up and highly recommended.

---

## Roadmap

As this CLI tool has been crafted as an MVP and has undergone real-world testing, we're now focusing on enhancing its robustness and expanding its capabilities. Here's what's on our radar:

- 🚀 **Preview Link Comments**: Plan to automatically comment the preview link on the pull request once provisioning is complete.

- 🛠️ **Enhanced Error Handling**: Aiming for comprehensive error handling throughout the entire CLI process to ensure smooth user experiences.

- 📣 **Slack Integration**: Integrate with Slack to provide real-time notifications and updates.

- 📘 **Expanded Documentation**: We'll be adding more examples showcasing the tool's versatility, such as:
    - Using Laravel as an API backend with Nuxt.js on the frontend for SSR handling.
    - Using with projects like WordPress and more.

- 🧪 **Testing**: Preparing unit tests and feature tests to ensure the tool's reliability and stability.
