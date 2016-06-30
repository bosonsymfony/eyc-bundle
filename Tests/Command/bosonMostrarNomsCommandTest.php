<?php
/**
 * Created by PhpStorm.
 * User: orlando
 * Date: 7/8/15
 * Time: 11:50 a.m.
 */

namespace EyCBundle\Tests\Command;


use UCI\Boson\EyCBundle\Command\bosonMostrarNomsCommand;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;


if(file_exists(__DIR__ . '/../../../../../../app/AppKernel.php')){
    require_once __DIR__ . '/../../../../../../app/AppKernel.php';
}
else{
    require_once __DIR__ . '/../../../../../../../../app/AppKernel.php';
}

class bosonMostrarNomsCommandTest extends \PHPUnit_Framework_TestCase {


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

    /**
     * @var aux
     */
    static $nombre;

    protected function setUp()
    {
        static::$kernel = new \AppKernel('dev', true);
        static::$kernel->boot();
        static::$container = static::$kernel->getContainer();

        static::$container->get('nomenclador')->insertarNom("Prueba");
        $noms = static::$container->get('nomenclador')->mostrarNoms();
        static::$idNom = array_pop($noms)->getId();
        static::$nombre = "Prueba";

    }


    public function testInteractive()
    {
        $application = new Application(static::$kernel);
        $application->add(new bosonMostrarNomsCommand());

        $command = $application->find('boson:eyc:mostrarNoms');
        $commandTester = new CommandTester($command);

        $commandTester->execute(array(
            'command' => $command->getName(),
//            '--no-cache:clear' => true
        ));



        $string = strval(static::$idNom)."   ". strval(static::$nombre);
        $this->assertRegExp('/'."$string".'/', $commandTester->getDisplay());
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
