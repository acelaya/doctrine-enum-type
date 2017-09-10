<?php
namespace Acelaya\Doctrine\Console;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Acelaya\Doctrine\Registrator\EnumTypeRegistratorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpTypeClassesCommand extends Command
{
    /**
     * @var EnumTypeRegistratorInterface
     */
    private $registrator;
    /**
     * @var array
     */
    private $enums;

    public function __construct(EnumTypeRegistratorInterface $registrator, array $enums)
    {
        parent::__construct();
        $this->registrator = $registrator;
        $this->enums = $enums;
    }

    protected function configure()
    {
        $this
            ->setName('det:dump-type-files')
            ->setDescription('Generates the doctrine type class files for every configured enum');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Generating enum doctrine type files...');
        try {
            $this->registrator->registerEnumTypes($this->enums);
            $output->writeln(' <info>Success!</info>');
        } catch (InvalidArgumentException $e) {
            $output->writeln(' <error>Error!</error>');
            if ($output->isVerbose()) {
                $this->getApplication()->renderException($e, $output);
            }
        }
    }
}
