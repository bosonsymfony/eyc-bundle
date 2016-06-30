<?php
/**
 * Created by PhpStorm.
 * User: orlando
 * Date: 7/8/15
 * Time: 3:31 p.m.
 */

namespace EyCBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UCI\Boson\EyCBundle\Command\bosonInsertarNomOpCommand;


if(file_exists(__DIR__ . '/../../../../../../app/AppKernel.php')){
    require_once __DIR__ . '/../../../../../../app/AppKernel.php';
}
else{
    require_once __DIR__ . '/../../../../../../../../app/AppKernel.php';
}

class bosonInsertarNomOpCommandTest extends \PHPUnit_Framework_TestCase {

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
    static $idNom1;

    /**
     * @var aux
     */
    static $idCampNom1;

    /**
     * @var aux
     */
    static $idNomOp;

    /**
     * @var aux
     */
    static $idNom2;

    /**
     * @var aux
     */
    static $idCampNom2;

    protected function setUp()
    {
        static::$kernel = new \AppKernel('dev', true);
        static::$kernel->boot();
        static::$container = static::$kernel->getContainer();

        //Insertamos un nomenclador color y optenemos su id.
        static::$container->get('nomenclador')->insertarNom("Color");
        $noms1 = static::$container->get('nomenclador')->mostrarNoms();
        static::$idNom1 = array_pop($noms1)->getId();

        //Se le inserta un campo al nomenclador color.
        static::$container->get('nomenclador')->insertarCampoNom(static::$idNom1, "Base", "integer", "Base, Octal, Hex", false);
        $camp1 = static::$container->get('nomenclador')->mostrarCamposNom(static::$idNom1);
        static::$idCampNom1 = array_pop($camp1)->getId();

        //Se inserta una instancia del nomenclador color.
        static::$container->get('nomenclador')->insertarNomOp( static::$idNom1 , "Azul",  array(array('id' => static::$idCampNom1, 'valor' => "8")));
        $idnomop = static::$container->get('nomenclador')->mostrarNomOps(static::$idNom1);
        static::$idNomOp = array_pop($idnomop)->getId();

        static::$container->get('nomenclador')->insertarNom("Carro");
        $noms2 = static::$container->get('nomenclador')->mostrarNoms();
        static::$idNom2 = array_pop($noms2)->getId();

        static::$container->get('nomenclador')->insertarCampoNom(static::$idNom2, "Color", "integer", "Vinculado al nomenclador color", true, static::$idNom1);
        $camp2 = static::$container->get('nomenclador')->mostrarCamposNom(static::$idNom2);
        static::$idCampNom2 = array_pop($camp2)->getId();
    }


    public function testInteractive()
    {
        $application = new Application(static::$kernel);
        $application->add(new bosonInsertarNomOpCommand());

        $command = $application->find('boson:eyc:insertarNomOp');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('dialog');

        $idnom = static::$idNom2;
        $idnomop = static::$idNomOp;

        $string = "$idnom\n";
        $string .= "VW\n";
        $string .= "$idnomop\n";
        $string .= "y\n";

        $dialog->setInputStream($this->getInputStream($string));

        $commandTester->execute(array(
            'command' => $command->getName(),
//            '--no-cache:clear' => true
        ));


        $this->assertRegExp('/La instancia del nomenclador se insertó satisfactoriamente./', $commandTester->getDisplay());
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
       static::$container->get('nomenclador')->eliminarNom(static::$idNom1);
       static::$container->get('nomenclador')->eliminarNom(static::$idNom2);
    }


}
