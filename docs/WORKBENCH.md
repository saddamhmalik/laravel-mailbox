# Workbench Development

This package includes an [Orchestra Workbench](https://github.com/orchestral/workbench) demo app for local development.

## Setup

```bash
composer install
composer setup
```

## Start the demo

```bash
composer serve
```

Visit `http://localhost:8000/mailbox` to browse captured emails.

Send a test email:

```
http://localhost:8000/send-test
```

## Commands

| Command | Description |
|---------|-------------|
| `composer serve` | Start workbench server |
| `composer test` | Run Pest tests |
| `composer analyse` | Run PHPStan |
| `composer format` | Run Pint |

## Configuration

Workbench settings live in `testbench.yaml`.
