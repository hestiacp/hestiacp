Hestia Control Panel - Contribution Guidelines
-----------------------

Ways to contribute
-----------------------
- **Beta testing**:
    - Download and install builds from the `beta` branch. Provide feedback to our developers and file any issues that you come across on [GitHub](https://www.github.com/hestiacp/hestiacp/issues).<br>
    `v-update-sys-hestia-git hestiacp beta install` will install the latest beta build from our GitHub repository.
- **Code review and bug fixes**:
    - Read over the code and if you notice errors (even spelling mistakes), submit a pull request with your fixes.
- **New features**:
    - Is there an awesome feature that you'd love to see included? While our development team tries to fulfill all reasonable requests, it can take time to implement new features depending on the amount of work involved. Submit a pull request with your code and if your idea is approved, we'll review and test it for inclusion with an upcoming release.
- **Translations**:
    - If you are a non-English speaker and would like to improve the quality of the translations used in Hestia Control Panel's web interface, Please go to [Hestia Translate](https://translate.hestiacp.com/projects/hestiacp/) to review the translations. For more information please read [How to contribute with Translations](https://forum.hestiacp.com/t/how-to-contribute-with-translations/1664).  Or open an issue report [GitHub](https://www.github.com/hestiacp/hestiacp/issues) highlighting the issue with the current translation so that it can be corrected.
- **Donations**:
    - If you're not a developer but you still want to make a contribution, you can make a donation to the Hestia Control Panel project to further its development (or if you'd just like to buy our developers a lunch, we'd appreciate that too). We currently accept donations through [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ST87LQH2CHGLA).

Development Guidelines
-----------------------
### Code formatting and comments
We ask that you follow existing naming schemes and coding conventions where possible, and that you add comments in your source code where appropriate to aid other developers in debugging and understanding your code in the future.

### Workflow and process
Development for this project takes place in branches to effectively develop, manage, and test new features and code changes. Our tiered approach allows us to closely control the quality of code as it is checked in for inclusion.

We have three primary or "evergreen" branches, which exist throughout our product's lifetime. Please refer to the following table for a description:

| Branch        | Description     | Cycle           |
|---------------|:---------------:|:---------------:|
| `main`        | Contains a snapshot of the latest development code.<br>**Not intended for production use and contains code from a merge snapshot.** | Daily  |
| `beta`        | Contains a snapshot of the next version which is currently in testing.<br>**Not intended for production but should be highly stable.**  | Weekly |
| `release`     | Contains a snapshot of the latest stable release.<br>**Intended for production use. This repository contains the same code as our compiled packages.** | Monthly |

### Creating a new branch and submitting pull requests
The first step is to create a fork of the `hestiacp/hestiacp` repository under your account so that you may submit pull requests and patches via GitHub. 

Once you've created your fork, clone the repository to your computer and make sure that you've checked out the `main` branch. **Always** create a new topic branch for you work. When submitting pull requests it is important that you target the correct branch to ensure that your changes are properly integrated and tested based on our release schedule. When creating a new branch, we ask that you please adhere to the following naming conventions as much as possible:

### Branch naming convention:
- **Prefix:** `topic/` (such as **fix**, **feature**, **refactor**, etc.)
- **ID**: `888` (GitHub Issue ID if an issue exists) -or- `2020-07` (Year-Month if an issue does not already exist)
- **Separator:** `_` (underscore)
- **Title:** `my-awesome-patch`

Branch name examples:
* `feature/777_my-awesome-new-feature` or `feature/2020-07_my-other-new-feature`
* `fix/000_some-bug-fix` or `fix/2020-07_this-feature-is-broken`
* `refactor/2020-07_v-change-domain-owner`
* `test/2020-07_mail-domain-ssl`

### Squashing commits for smaller changes
To aid other developers and keep the project's commit history clean, please **squash your commits** when it's appropriate. For example with smaller commits related to the same piece of code, such as commits labelled "Fixed item 1", "Adjusted color of button XYZ", "Adjusted alignment of button XYZ" can be squashed into one commit with the title "Fixed button issues in item". 

### What happens when I submit a pull request?
- Our internal development team will review your work and validate your request.
- Your changes will be tested to ensure that there are no issues.
- If changes need to be made, you will be notified via GitHub.
- Once approved, your code will be merged to the appropriate `staging/*` branch based on the chart below:

All pull requests must include a brief but descriptive title, and a description of the changes that you've made with as much detail as possible. **Only include commits that are related to your feature, bug fix, or patch in your pull request.**

| Topic branches:              | Primary Target:             | Final destination:                    | 
| -----------------------------|:---------------------------:|:-------------------------------------:|
| **`feature/*`**              | `staging/features`          | `main`                                |
| **`fix/*`**                  | `staging/fixes`             | `main` **and** `beta` *or* `release`  |
| **`refactor/*`**             | `staging/refactoring`       | `main`                                |
| **`test/*`**                 | `staging/tests`             | `main`                                |
| **`doc/*`**                  | `staging/docs`              | `main`, `beta`, *or* `release`        |

Our development and release cycles
-----------------------
### During the development cycle:
- `topic/*` branches are submitted to our team via a pull request. Your changes will be reviewed and tested, and if all appropriate quality assurance checks pass the branch will be merged to the corresponding `staging/*` branch.

- `staging/*` branches merge into `main` at various intervals throughout the development process.

- When all planned features and fixes have been merged to `main`, the code is tested for regressions and bugs.

- A snapshot of `main` is pushed to a temporary branch called `staging/refactoring`, and final code review, refactoring, and optimization takes place. Once complete, `staging/refactoring` merges back to `main` bringing the codebase up-to-date. All other `staging/*` branches synchronize with `main` at this time.

- After final validation checks pass, our development team signs off on the release and the code is pushed from `main` to `beta`.

### During the release cycle:
- **What happens when code moves from `main` to `beta`**:<br>
    - **No new feature requests will be approved**.
    - `main` will receive an increment in it's version number signaling the start of a new development cycle.
    - `fix/*` topic branches/commits will be cherry picked to `beta` as necessary.
    - `staging/docs` will merge into `beta` prior to the code being pushed to `release` to bring documentation and supporting files up-to-date.

- If all quality assurance checks pass, our development team will then:
    - Sign off on the code in `beta`.
    - Push the code to the `release` branch and create a corresponding version tag.
    - Compile new packages and publish them to our APT repository. 
    - **Notes:**
        - `release` always contains the highest released version of Hestia Control Panel.
        - For major releases, a `release/vX.x` branch will be created for maintenance and servicing purposes.




Thank you!
-----------------------
We appreciate **all** contributions no matter what size; your feedback and input directly shapes the future of Hestia Control Panel and we could not do it without your support.

Thank you for your time and we look forward to seeing your pull requests,<br>
The Hestia Control Panel development team
