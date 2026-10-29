# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.5] - 29 October 2026
### Fixed
- Poll component messages to avoid race after AJAX update
- Pass explicit characters to trim() for PHP 8.6 compatibility
- Respect core email validator errors and skip MX lookup for empty domain

## [1.0.4] - 02 June 2026
### Fixed
- Update composer requirements

## [1.0.3] - 19 February 2026
### Fixed
- Fix config integration test

## [1.0.2] - 12 February 2026
### Fixed
- Allow PHP Config to read from current store level

## [1.0.1] - 20 January 2026
### Fixed
- Add new GitHub Action workflows
- Check for invalid email without domain part

## [1.0.1] - 15 October 2025
### Fixed
- Copy generic CI/CD files
- Adding all files to git via Yireo Command
- Initial release
