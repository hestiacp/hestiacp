Code Contributions & Pull Requests - Guidelines
-----------------------

All pull requests must include a brief but descriptive title, and a description of the changes that you've made with as much detail as possible. **Only include commits that are related to your feature, bug fix, or patch in your pull request.**

### Code formatting and comments:
We ask that you follow existing naming schemes and coding conventions where possible, and that you add comments in your source code where appropriate to aid other developers in debugging and understanding your code in the future.

### Squashing commits for smaller changes:
When submitting a pull request with multiple smaller commits which are related to the same issue (or file), we ask that you please **squash your commits** in order to keep the project's commit history clean and easy to follow for other developers.

### Working with branches:
Development for this project takes place in branches to effectively develop, manage, and test new features and code changes, helping to ensure that each release meets high quality standards. Our primary branches are as follows:

* **master**: Active development code for the the next version of Hestia Control Panel.
* **release**: The latest stable release code - installation packages generally align with this branch.

We ask that you create a new branch for your work based on **master**, this will allow you to submit only the necessary commits/changes that you've made. We generally adhere to the below naming convention for internal branches; you're welcome to use your own naming conventions so long as your commits follow the guidelines mentioned above.

* **feature-name**: New features
* **bugfix-000**: Bug fixes

**Note**: Replace **000** with the GitHub Issue ID if available, or use a short but descriptive name.

### Feature freeze:
Once development has reached a point where all planned new features have been incorporated, the **master** branch will then enter a "feature freeze" period where only fixes to the existing functionality will be added or merged for inclusion. Once any known issues have been resolved and the build has passed internal validation and testing procedures, it will then be pushed to **release** and tagged with it's respective version number and made available through our APT repositories.

**Please ensure that all pull requests meet the guidelines listed above; those that do not will be rejected and sent back for review.**

Donations
-----------------------
If you would like to make a donation to the Hestia Control Panel project to further its development (or if you'd like to buy our developers a lunch), please feel free to do so via [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ST87LQH2CHGLA).