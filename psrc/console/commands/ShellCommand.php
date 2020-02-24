<?php
defined( '_JEXEC' ) or die( 'Acceso Restringido' );

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
	Ref: https://symfony.com/doc/current/console.html
    https://github.com/bobthecow/psysh/wiki/Config-options
*/
class ShellCommand extends ExjCommand
{
    protected static $defaultName = 'shell';

    protected function configure()
    {
        $this->setDescription('Shell app')
           ->setHelp('Este comando interactua con clases de la aplicacion.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->setCatchExceptions(false);

        $shell = new Psy\Shell($this->getCfgShell());

        try {
            $shell->run();
        } catch (Exception $e) {
            $output->write('Excepcion: '.$e->getMessage());
        } finally {
            // $loader->unregister();
        }
    }

    private function getCfgShell(){
        $this->loginUser('adminbc', '2ynor');

        $startupMessage = "<info>Usuario</info>: ".Exj::GetUserUserName();
        $startupMessage .= " <info>Ciudad</info>: ".ExjUser::GetNombreCiudad();
        $startupMessage .= " <info>Empresa</info>: ".ExjUser::GetNombreEmpresa();
        $startupMessage .= " <info>DB</info>: ".ExjDatabase::GetNameDB();


        $defaultIncludes = array();
        
        $bootstrapPath = JPATH_BASE."/libraries/vendor/autoload.php";
        $defaultIncludes[] = $bootstrapPath;


        $cfg = new Psy\Configuration([
            'updateCheck' => 'never',
            'prompt' => '~ ',
            'defaultIncludes' => $defaultIncludes,
            'startupMessage' => $startupMessage,
            'useUnicode' => true,
            'eraseDuplicates' => true
        ]);

        return $cfg;
    }    
}
