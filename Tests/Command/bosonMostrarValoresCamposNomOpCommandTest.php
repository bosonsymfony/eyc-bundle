<?php
/**
 * Created by PhpStorm.
 * User: orlando
 * Date: 7/9/15
 * Time: 11:04 a.m.
 */

namespace EyCBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UCI\Boson\EyCBundle\Command\bosonEliminarNomOpCommand;
use UCI\Boson\EyCBundle\Command\bosonMostrarValoresCamposNomOpCommand;


if(file_exists(__DIR__ . '/../../../../../../app/AppKernel.php')){
    require_once __DIR__ . '/../../../../../../app/AppKernel.php';
}
else{
    require_once __DIR__ . '/../../../../../../../../app/AppKernel.php';
}


class bosonMostrarValoresCamposNomOpCommandTest extends \PHPUnit_Framework_TestCase {

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

    /**
     * @var aux
     */
    static $idNomOp;

    protected function setUp()
    {
        static::$kernel = new \AppKernel('dev', true);
        static::$kernel->boot();
        static::$container = static::$kernel->getContainer();

        static::$container->get('nomenclador')->insertarNom("Color");
        $noms = static::$container->get('nomenclador')->mostrarNoms();
        static::$idNom = array_pop($noms)->getId();

        static::$container->get('nomenclador')->insertarCampoNom(static::$idNom, "Base", "integer", "Dimension del color", false);
        $camp = static::$container->get('nomenclador')->mostrarCamposNom(static::$idNom);
        static::$idCampNom = array_pop($camp)->getId();

        static::$container->get('nomenclador')->insertarNomOp(static::$idNom, "Azul",  array(array('id'=> static::$idCampNom, 'valor'=> "8")));
        $nomops = static::$container->get('nomenclador')->mostrarNomOps(static::$idNom);
        static::$idNomOp = array_pop($nomops)->getId();

    }


    public function testInteractive()
    {
        $application = new Application(static::$kernel);
        $application->add(new bosonMostrarValoresCamposNomOpCommand());

        $command = $application->find('boson:eyc:mostrarValoresCamposNomOp');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('dialog');
        $idnom = static::$idNom;
        $idnomop = static::$idNomOp;
        $string = "$idnom\n";
        $string .= "$idnomop\n";


        $dialog->setInputStream($this->getInputStream($string));

        $commandTester->execute(array(
            'command' => $command->getName(),
//            '--no-cache:clear' => true
        ));

        $string = strval(static::$idNomOp)."   "."Azul\n".strval(static::$idCampNom)."    "."Base"."    "."8";
        $this->assertRegExp('/'.$string.'/', $commandTester->getDisplay());
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
