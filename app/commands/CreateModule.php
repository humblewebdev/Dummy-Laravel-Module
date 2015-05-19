<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * CreateModule
 *
 * @uses      Command
 * @package
 * @version   1.0.0
 * @copyright 1997-2005 The PHP Group
 * @author    Thomas Veilleux Thomas@perk.com
 * @license   PHP
 */
class CreateModule extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'module:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'creates a module in the modules directory';


    const BASE = 'app/modules';

    /**
     * directories
     *
     * @var array
     * @access private
     */
    protected $Directories = array(
        'src',
        'src/controllers',
        'src/models',
        'config',
        'test'
    );

    /**
     * files
     *
     * @var array
     * @access private
     */
    protected $Files = array(
        'routes'
    );

    protected $keepFiles = array(
        'config/.gitkeep',
        'test/.gitkeep',
        'src/controllers/.gitkeep',
        'src/models/.gitkeep'
    );

    /**
     * SplFileObject
     *
     * @var mixed
     * @access protected
     */
    protected $SplFileObject;

    public function __construct()
    {
        parent::__construct();
        $this->SplFileObject = new SplFileObject(app_path('config/app.php'));
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        //create a directory structure
        $this->createDirectories();
        $this->createFiles();
        $this->createModuleTestingConfig();
        $this->updateProvidersConfig();
        $this->updatePhpUnitConfiguration();
        echo exec('composer dump-autoload');
    }

    /**
     * getArgument s
     *
     * @access protected
     * @return void
     */
    protected function getArguments()
    {
        return array(
            array(
                'moduleName',
                InputArgument::REQUIRED,
                'Name of the module to be created'
            )
        );
    }


    /**
     * createDirectories
     *
     * @access private
     * @return void
     */
    private function createDirectories()
    {

        mkdir(self::BASE . '/' . $this->argument('moduleName'), 0777);

        foreach ($this->Directories as $dir) {
            mkdir(self::BASE . '/' . $this->argument('moduleName') . '/' . $dir, 0777);
        }
    }

    /**
     * createFiles
     *
     * @access private
     * @return void
     */
    private function createFiles()
    {
        file_put_contents(
            self::BASE . '/' . $this->argument('moduleName') . '/' .
            ucfirst($this->argument('moduleName')) . 'ServiceProvider.php',
            $this->createServiceProvider()
        );

        foreach ($this->Files as $file) {
            file_put_contents(
                self::BASE . '/' . $this->argument('moduleName') . '/' .
                $file . '.php',
                '<?php' . PHP_EOL
            );
        }

        foreach ($this->keepFiles as $file) {
            file_put_contents(
                self::BASE . '/' . $this->argument('moduleName') . '/' .
                $file,
                ''
            );
        }
    }

    /**
     * createServiceProvider
     *
     * @access private
     * @return void
     */
    private function createServiceProvider()
    {
        $upperCaseModuleName = ucfirst($this->argument('moduleName'));
        $moduleName          = $this->argument('moduleName');

        $serviceProvider = <<<EOT
<?php

namespace Perk\\{$upperCaseModuleName};

use Perk\ServiceProvider;

class {$upperCaseModuleName}ServiceProvider extends ServiceProvider
{
    public function register()
    {
        parent::register('{$moduleName}');
    }

    public function boot()
    {
        parent::boot('{$moduleName}');
    }
}

EOT;

        return $serviceProvider;
    }

    /**
     * updateProvidersConfig
     *
     * @access private
     * @return void
     */
    private function updateProvidersConfig()
    {
        $moduleName = ucfirst($this->argument('moduleName'));

        $ServiceProvider = "'Perk\\" . $moduleName . '\\' . $moduleName .
                           'ServiceProvider\',';

        $lines = file('app/config/app.php');

        $output = '';
        foreach ($lines as $lineNum => $line) {
            if (strpos($line, '/* Modules */') != false) {
                $line = "        /* Modules */" . PHP_EOL . "        " . $ServiceProvider . PHP_EOL;
            }
            $output .= $line;
        }

        file_put_contents('app/config/app.php', $output);
    }

    private function createModuleTestingConfig()
    {
        $xml        = simplexml_load_file('phpunit-template.xml');
        $moduleName = $this->argument('moduleName');

        $xml->attributes()->bootstrap                   = '../../../' . $xml->attributes()->bootstrap;
        $xml->testsuites->testsuite->directory          = './test';
        $xml->testsuites->testsuite->attributes()->name = 'Perk ' . ucfirst($moduleName) . ' Test Suite';

        $dom                     = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->loadXML($xml->asXML());

        file_put_contents(self::BASE . '/' . $moduleName . '/phpunit.xml', $dom->saveXML());
    }

    private function updatePhpUnitConfiguration()
    {
        $xml        = simplexml_load_file('phpunit.xml');
        $moduleName = $this->argument('moduleName');

        $testSuite = $xml->testsuites->addChild('testsuite');
        $testSuite->addAttribute('name', 'Perk ' . ucfirst($moduleName) . ' Test Suite');
        $testSuite->addChild('directory', './' . self::BASE . '/' . $moduleName . '/test');

        $dom                     = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput       = true;
        $dom->loadXML($xml->asXML());

        file_put_contents('phpunit.xml', $dom->saveXML());
    }
}
