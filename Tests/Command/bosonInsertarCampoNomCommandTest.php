<?php
/**
 * Created by PhpStorm.
 * User: orlando
 * Date: 7/8/15
 * Time: 10:09 a.m.
 */

namespace EyCBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UCI\Boson\EyCBundle\Command\bosonInsertarCampoNomCommand;

if(file_exists(__DIR__ . '/../../../../../../app/AppKernel.php')){
    require_once __DIR__ . '/../../../../../../app/AppKernel.php';
}
else{
    require_once __DIR__ . '/../../../../../../../../app/AppKernel.php';
}


class bosonInsertarCampoNomCommandTest extends \PHPUnit_Framework_TestCase {


    /**
     * @var \AppKernel
     */
    static $kernel;

    /**
     * @var ContainerInterface
     */
    static $container;

    /**
     * @var aux
     */
    static $idNom;


    protected function setUp()
    {
        static::$kernel = new \AppKernel('dev', true);
        static::$kernel->boot();
        static::$container = static::$kernel->getContainer();

        static::$container->get('nomenclador')->insertarNom("Prueba");
        $noms = static::$container->get('nomenclador')->mostrarNoms();
        static::$idNom = array_pop($noms)->getId();
    }


    public function testInteractive()
    {
        $application = new Application(static::$kernel);
        $application->add(new bosonInsertarCampoNomCommand());

        $command = $application->find('boson:eyc:insertarCampoNom');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('dialog');
        $idnom = static::$idNom;
        $string = "$idnom\n";
        $string .= "PruebaCampo\n";
        $string .= "string\n";
        $string .= "Descripcion del campo\n";
        $string .= "n\n";
        $string .= "y\n";


        $dialog->setInputStream($this->getInputStream($string));

        $commandTester->execute(array(
            'command' => $command->getName(),
//            '--no-cache:clear' => true
        ));

        $this->assertRegExp('/El campo se insertó satisfactoriamente./', $commandTester->getDisplay());
    }

    protected function getInputStream($input)
    {
        $stream = fopen('php://memory', 'r+', false);
        fputs($stream, $input);
        rewind($stream);

        return $stream;
    }



    protected function tearDown()
    {
        static::$container->get('nomenclador')->eliminarNom(static::$idNom);
    }

}
