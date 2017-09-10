<?php
namespace Acelaya\Doctrine\Console;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Acelaya\Doctrine\Registrator\EnumTypeRegistrator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleHelper
{
    public static function createApp(array $config)
    {
        $app = new Application('acelaya/doctrine-enum-type', 'v2.1.0');

        if (! isset($config['enum_types']) || ! is_array($config['enum_types'])) {
            throw new InvalidArgumentException('Config param "enum_types" with list of enums not provided');
        }

        // Make sure auto_generate_type_files is set to true, otherwise files won't be generated if accidentally
        // overwritten
        $config['auto_generate_type_files'] = true;
        $enums = $config['enum_types'];
        unset($config['enum_types']);
        $registrator = new EnumTypeRegistrator($config);

        // Create command and register
        $app->add(new DumpTypeClassesCommand($registrator, $enums));
        return $app;
    }

    public static function printConfigMissingError(OutputInterface $output = null)
    {
        $output = $output ?: new ConsoleOutput();
        $output->writeln('<error>det-config.php not found</error>');
        $output->writeln(
            'You have to create a file named <options=bold>det-config.php</> in the project root or the config'
            . ' subdirectory that provides the configuration to generate the doctrine types classes.'
        );
        $output->writeln(<<<EOT
For example:

    <?php
    use Acelaya\Doctrine\Type\AbstractPhpEnumType;
    use App\Enum\Action;
    use App\Enum\Gender;

    return [
        'base_type_class' => AbstractPhpEnumType::class,
        'type_files_dir' => 'some/dir',

        'enum_types' => [
            Action::class,
            'gender_type' => Gender::class,
        ],
    ];

EOT
        );
    }
}
