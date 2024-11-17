# CLI.php - Command Line Interface

This PHP script serves as a command-line interface to interact with various commands within the system.

## Usage

Run the `cli.php` script with the following command:

```bash
php cli.php [Command] [Options]
```

## Available Options

 - --update: Forces an update of the command metadata.This option is used to regenerate the metadata file (command_metadata.json). The metadata is necessary for the system to know what commands are available and how they should be executed.
 - --help: Displays the help menu with available commands and options.
  
 ### Why use --update?

- Performance Reasons: If the metadata file is outdated or missing, the script will regenerate it, ensuring the system has up-to-date command information. 
- Avoiding Redundant Parsing: Using --update ensures that you don’t have to re-parse the command classes every time the script is executed, saving time and system resources. 
- New Commands: If new commands are added to the system, running --update will ensure they are included in the metadata file.

## Command Metadata (`cli_meta`)

Each console script needs to include a `cli_meta` array that defines the metadata for the available commands. This array contains the following fields:

- **`command`**: The name of the command (e.g., `php cli.php [command]`).
- **`description`**: A brief description of what the command does.
- **`usage`**: How the command should be used (e.g., `php cli.php [command] [options]`).
- **`options`** (optional): A list of available options for the command. Each option can have a name, description, and whether it's required or optional.

### Example of `cli_meta` Array:

```php
$cli_meta = [
    [
        'command'   => 'my-command',
        'description' => 'Executes the custom command.',
        'usage'     => 'php cli.php my-command --option=value',
        'options'   => [
            '--option' => 'An example option for the command (optional).'
        ]
    ]
];
```
### Why Is This Important?
The cli_meta array is crucial because it defines the structure and behavior of each command. It allows the CLI script to:

- Display helpful usage information when the user requests --help.
- Automatically validate and parse the available commands and options.
- Maintain consistent documentation for all commands, making it easier to understand how each command works.

The options field is optional. If your command doesn't have any options, you can leave it empty or omit it entirely. However, for commands that have configurable options, including them in the cli_meta array ensures users can see what options are available and how to use them correctly.

Conclusion
Each command in your console script must have an entry in the cli_meta array with at least the command, description, and usage fields. The options field is optional but recommended when the command has configurable options. This structure helps keep your commands organized and provides the necessary metadata for command execution and help generation.