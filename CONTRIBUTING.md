Code Contributions & Pull Requests - Guidelines
-----------------------

All pull requests must include a brief but descriptive title, and a description of the changes that you've made with as much detail as possible. **Only include commits that are related to your feature, bug fix, or patch in your pull request.**

## Code formatting and comments:
We ask that you follow existing naming schemes and coding conventions where possible, and that you add comments in your source code where appropriate to aid other developers in debugging and understanding your code in the future.

## Squashing commits for smaller changes:
When submitting a pull request with multiple smaller commits which are related to the same issue (or file), we ask that you please **squash your commits** in order to keep the project's commit history clean and easy to follow for other developers.

## Working with branches:
Development for this project takes place in branches to effectively develop, manage, and test new features and code changes, helping to ensure that each release meets high quality standards. Our primary branches are as follows:

### Primary branches:

* `develop`: Active development code for the the next version of Hestia Control Panel, considered to be unstable.
* `beta`: Feature locked code for the next release, which receives fixes only and goes through feedback and testing.
* `release`: Code in this branch aligns with the latest packages available on our APT repositories.

### I want to contribute some code to Hestia Control Panel, where do I start?

First, create a new branch for your work based on the `develop` branch to ensure that you have the latest commits in your local repository, which will help make sure that your pull requests only contain the commits that are necessary. 

When creating your branches, **please adhere to the following naming conventions:**

- **Prefix:** `bugfix/` or `feature/` based on the type of submission.
- **ID**: `888` (GitHub Issue ID if an issue exists) -or- `2020-07` (Year-Month if an issue does not already exist)
- **Separator:** `_` (underscore)
- **Title:** `my-awesome-patch`

Branch name examples:
* `feature/777_my-awesome-new-feature` or `feature/2020-07_my-other-new-feature`
* `bugfix/000_some-bug-fix` or `bugfix/2020-07_this-feature-is-broken`

Once your code is complete and you have reviewed it for errors, submit a pull request to the correct integration branch:

* `staging/fixes`: bugfix/ branches will merge to this branch when they have been tested/verified.
* `staging/features`: feature/ branches will merge to this branch when they have been tested/verified.

All `staging` branches will integrate to `develop` to form the next release of Hestia Control Panel. Once all features and fixes planned for the release are merged, the code will then be pushed to the `beta` branch where it is feature locked, assigned a version number, and will receive thorough testing before being pushed to the `release` branch with new packages being pushed to APT.

**Please ensure that all pull requests meet the guidelines listed above; those that do not will be rejected and sent back for review.**

Donations
-----------------------
If you would like to make a donation to the Hestia Control Panel project to further its development (or if you'd like to buy our developers a lunch), please feel free to do so via [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ST87LQH2CHGLA).