<?php
/**
 * Created by PhpStorm.
 * User: orlando
 * Date: 7/8/15
 * Time: 10:53 a.m.
 */

namespace EyCBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UCI\Boson\EyCBundle\Command\bosonEliminarCampoNomCommand;


if(file_exists(__DIR__ . '/../../../../../../app/AppKernel.php')){
    require_once __DIR__ . '/../../../../../../app/AppKernel.php';
}
else{
    require_once __DIR__ . '/../../../../../../../../app/AppKernel.php';
}

class bosonEliminarCampoNomCommandTest extends \PHPUnit_Framework_TestCase {


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
    static $idCampNom;

    protected function setUp()
    {
        static::$kernel = new \AppKernel('dev', true);
        static::$kernel->boot();
        static::$container = static::$kernel->getContainer();

        static::$container->get('nomenclador')->insertarNom("Prueba");
        $noms = static::$container->get('nomenclador')->mostrarNoms();
        static::$idNom = array_pop($noms)->getId();

        static::$container->get('nomenclador')->insertarCampoNom(static::$idNom, "CampoPrueba", "string", "Descripcion", false);
        $camp = static::$container->get('nomenclador')->mostrarCamposNom(static::$idNom);
        static::$idCampNom = array_pop($camp)->getId();
    }


    public function testInteractive()
    {
        $application = new Application(static::$kernel);
        $application->add(new bosonEliminarCampoNomCommand());

        $command = $application->find('boson:eyc:eliminarCampoNom');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('dialog');
        $idnom = static::$idNom;
        $idcampnom = static::$idCampNom;
        $string = "$idnom\n";
        $string .= "$idcampnom\n";


        $dialog->setInputStream($this->getInputStream($string));

        $commandTester->execute(array(
            'command' => $command->getName(),
//            '--no-cache:clear' => true
        ));

        $this->assertRegExp('/El campo del nomenclador se eliminó de forma satisfactoria./', $commandTester->getDisplay());
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
