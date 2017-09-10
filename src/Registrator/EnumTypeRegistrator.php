<?php
namespace Acelaya\Doctrine\Registrator;

use Acelaya\Doctrine\Exception\InvalidArgumentException;
use Acelaya\Doctrine\Type\AbstractPhpEnumType;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Types\Type;
use MyCLabs\Enum\Enum;
use Symfony\Component\Filesystem\Filesystem;

class EnumTypeRegistrator implements EnumTypeRegistratorInterface
{
    const TYPES_NAMESPACE = 'Acelaya\Doctrine\Type\Generated';
    const ENUM_CLASS_PATTERN = <<<EOL
<?php
namespace %s;

class %s extends \\%s
{
    protected \$name = '%s';
    protected \$enumClass = '%s';
}

EOL;

    /**
     * @var array
     */
    private $config;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var bool
     */
    private $isAutoloaderRegistrated = false;

    public function __construct(array $config = [], Filesystem $filesystem = null)
    {
        $this->config = array_merge([
            'base_type_class' => AbstractPhpEnumType::class,
            'type_files_dir' => sys_get_temp_dir(),
            'auto_generate_type_files' => true,
        ], $config);
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    /**
     * @param string $typeNameOrEnumClass
     * @param string|null $enumClass
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public function registerEnumType($typeNameOrEnumClass, $enumClass = null)
    {
        $typeName = $typeNameOrEnumClass;
        $enumClass = $enumClass ?: $typeNameOrEnumClass;
        $typeClass = str_replace('\\', '', $enumClass);

        if (! is_subclass_of($enumClass, Enum::class)) {
            throw new InvalidArgumentException(sprintf(
                'Provided enum class "%s" is not valid. Enums must extend "%s"',
                $enumClass,
                Enum::class
            ));
        }

        // Register an autoloader for generated classes
        if (! $this->isAutoloaderRegistrated) {
            $this->registerAutoloader();
        }

        // Generate file if requested
        if ($this->config['auto_generate_type_files']) {
            $this->generateFile($typeClass, $typeName, $enumClass);
        }

        // Register type
        Type::addType($typeName, self::TYPES_NAMESPACE . '\\' . $typeClass);
    }

    private function generateFile($typeClass, $typeName, $enumClass)
    {
        $classContents = sprintf(
            self::ENUM_CLASS_PATTERN,
            self::TYPES_NAMESPACE,
            $typeClass,
            $this->config['base_type_class'],
            $typeName,
            $enumClass
        );
        $path = $this->config['type_files_dir'] . '/' . $typeClass . '.php';

        $this->filesystem->dumpFile($path, $classContents);
    }

    private function registerAutoloader()
    {
        $this->isAutoloaderRegistrated = true;
        spl_autoload_register(function ($class) {
            $prefix = self::TYPES_NAMESPACE;
            $baseDir = $this->config['type_files_dir'];

            // Check if requested class starts with generated classes namespace, otherwise delegate to next autoloader
            $prefixLength = strlen($prefix);
            if (strncmp($prefix, $class, $prefixLength) !== 0) {
                return;
            }

            // Generate file path by replacing prefix by files dir, and then require it
            $relativeClass = substr($class, $prefixLength);
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            require $file;
        });
    }

    /**
     * @param string[] $types
     * @throws InvalidArgumentException
     * @throws DBALException
     */
    public function registerEnumTypes(array $types)
    {
        foreach ($types as $typeName => $enumClass) {
            $typeName = is_string($typeName) ? $typeName : $enumClass;
            $this->registerEnumType($typeName, $enumClass);
        }
    }
}
