# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2024-12-23

### Added
- `BrainServiceClient` class for service-to-service communication through Brain
- `get()`, `post()`, `put()`, `patch()`, `delete()` proxy methods
- `proxy()` method for custom HTTP method calls
- `timeout()` method for configuring request timeouts
- Service authentication via `X-Brain-Service-Secret` header

## [1.0.1] - 2024-12-22

### Changed
- Version bump to test Packagist auto-update workflow

## [1.0.0] - 2024-12-22

### Added
- Initial release
- `BrainEventClient` class for sending events to Brain Nucleus
- `send()` method for synchronous event sending
- `sendAsync()` method for fire-and-forget events via Laravel queues
- `getVersion()` method to get current client version
- `checkVersion()` method to check for updates
- Automatic client version tracking via `X-Brain-Client-Version` header
