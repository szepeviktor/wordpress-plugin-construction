# Enable Polylang in WP-CLI commands

### Installation

```bash
mkdir -p ~/.wp-cli/commands/polylang-enabler-hack
cp -v command.php ~/.wp-cli/commands/polylang-enabler-hack/
```

Require it from `~/.wp-cli/config.yml`

```yml
require:
  - commands/polylang-enabler-hack/command.php
```

### Usage

`wp post list --lang=en`
