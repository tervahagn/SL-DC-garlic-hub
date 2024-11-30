# Symfony Console - Command Line Interface

The garlic-hub uses the symfony console component for  cli commands. For those who are noct familar with the symfony console component, here is a brief overview.
You will find more details in the [Symfony Console documentation](https://symfony.com/doc/current/components/console.html).

## Usage

Run the console application with the following command:

```bash
php bin/console [command] [options]
php bin/console my-command --option=value
```
## Symfony Console Command Structure
Each Symfony Console command is a PHP class that extends `Symfony\Component\Console\Command\Command`.
Commands define their behavior in the `configure` and `execute` methods.

### Basic Structure of a Command
Here is an example of a Symfony Console command:

```php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MyCommand extends Command
{
    protected static $defaultName = 'my-command'; // The name of the command

    protected function configure(): void
    {
        $this
            ->setDescription('Executes the custom command.')
            ->setHelp('This command allows you to perform a specific action...')
            ->addOption('option', null, InputOption::VALUE_OPTIONAL, 'An example option for the command');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $option = $input->getOption('option');
        $output->writeln("Executing the command with option: $option");

        return Command::SUCCESS; // Indicate success
    }
}
```

## Help Command (--help)

Symfony Console automatically provides a `--help option for every command. When you run:

```bash
php bin/console my-command --help
```
## Symfony displays:

- The command name
- Its description
- Available options and arguments
- Usage examples (if defined in the configure method)

