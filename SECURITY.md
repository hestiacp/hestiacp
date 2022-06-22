# Hestia CP Security policy

Welcome and thanks for taking interest in Hestia CP!

We are mostly interested in reports by actual Hestia CP users but  all high quality contributions are welcome.

If you believe that you have have discovered a vulnerability in Hestia Control Panel,
please let our development team know by submitting a report [Huntr.dev](https://huntr.dev/bounties/disclose/?target=https://github.com/hestiacp/hestiacp) Bounties and CVEs are automatically managed and allocated via the platform.

If you are unable to use [Huntr.dev](https://huntr.dev/bounties/disclose/?target=https://github.com/hestiacp/hestiacp) please send an email to support@hestiacp.com

We ask you to include a detailed description of the vulnerability, a list of services involved (e.g. exim, dovecot) and the versions which you've tested, full steps to reproduce the vulnerability, and include your findings and expected results.

Please do not open any public issue on Github or any other social media before the report has been published and a fix has been released. 

With that, good luck hacking us ;)

## Supported versions

| Version | Supported          |
| ------- | ------------------ |
| Latest  | :white_check_mark: |

## Qualifying Vulnerabilities

### Vulnerabilities we really care about!
- Remote command execution
- Code/SQL Injection
- Authentication bypass
- Privilege Escalation
- Cross-site scripting (XSS)
- Performing limited admin actions without authorization
- CSRF

### Vulnerabilities we accept

- Open redirects
- Password brute-forcing that circumvents rate limiting

## Non-Qualifying Vulnerabilities

- Theoretical attacks without proof of exploitability
- Attacks that are the result of a third party library should be reported to the library maintainers
- Social engineering
- Reflected file download
- Physical attacks
- Weak SSL/TLS/SSH algorithms or protocols
- Attacks involving physical access to a user’s device, or involving a device or network that’s already seriously compromised (eg man-in-the-middle).
- The user attacks themselves
- anything in `/test/` folder

