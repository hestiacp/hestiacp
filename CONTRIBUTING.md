How to Contribute
==================================================

Do you want to contribute to the Hestia Control Panel project? The easiest way to get started is to [submit an issue report](https://github.com/hestiacp/hestia/issues) if you come across a bug or issue. If you're a developer, please feel free to submit a patch/pull request with a fix. 

**We appreciate all contributions to our project; it is with the help of the open-source community that we are able to build a product that meets users needs.**

Code Contributions & Pull Requests - Guidelines
-----------------------

**All pull requests must include:**
1. A brief but descriptive title, such as **Fixed typo in php.ini** or **Updated rebuild_mail_conf function in rebuild.sh**.
2. A detailed description of the changes that you've made.
   1. Example: "Apache subdomain configuration was incorrect, modified variables in templates to point to the correct files."
3. Only commits which are related to the pull request or issue itself.

### Code formatting and comments:
We ask that you try to follow existing naming schemes and coding conventions as much as possible, and that you please add brief but descriptive comments in your source code to aid other developers in debugging and understanding your code in the future.

### Squashing commits for smaller changes:
When submitting a pull request with multiple smaller commits which are related to the same issue (or file), we ask that you please **squash your commits** in order to keep the project's commit history clean and easy to follow for other developers.

### Working with branches:
Development for this project takes place in branches to effectively develop, manage, and test manage new features and code changes, ensuring that that each release meets high quality standards. There are two main branches which have a never-ending life cycle:

* **master**:Latest stable release code (install packages align with this branch)
* **develop**: Primary development branch containing code for the next release.

If you are submitting a fix for a critical issue (such as security or broken functionality) in the current release, please create a branch with the prefix **hotfix-** starting from **master** for your work and submit a pull request with **[CRITICAL]** in the title.

For all other changes, we ask that you create a new branch based on **develop**. Please follow the below naming conventions for your work and submit a pull request when it has been completed. Once reviewed and approved, our development team will merge your code into the primary development branch.

* **feature-**: New features
* **bugfix-**: Fixes for non-critical bugs
* **secfix-**: Fixes for non-critical security issues
* **webui-**: Control Panel user experience and interface changes

**Please ensure that all pull requests meet the guidelines listed above; those that do not will be rejected and sent back for review.**

Donations
-----------------------
If you would like to make a donation to the Hestia Control Panel project to further its development (or if you'd like to buy our developers a lunch), please feel free to do so via [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=ST87LQH2CHGLA).