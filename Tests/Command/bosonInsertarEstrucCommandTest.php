<?php
/**
 * Created by PhpStorm.
 * User: killer
 * Date: 7/07/15
 * Time: 8:12
 */

namespace EyCBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UCI\Boson\EyCBundle\Command\bosonInsertarEstrucCommand;

if(file_exists(__DIR__ . '/../../../../../../app/AppKernel.php')){
    require_once __DIR__ . '/../../../../../../app/AppKernel.php';
}
else{
    require_once __DIR__ . '/../../../../../../../../app/AppKernel.php';
}


class bosonInsertarEstrucCommandTest extends \PHPUnit_Framework_TestCase {

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

    }


    public function testInteractive()
    {
        $application = new Application(static::$kernel);
        $application->add(new bosonInsertarEstrucCommand());

        $command = $application->find('boson:eyc:insertarEstruc');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('dialog');

        $idnom = static::$idNom1;

        $string = "Facultad\n";
        $string .= "y\n";
        $string .= "y\n";
        $string .= "Diciplina\n";
        $string .= "string\n";
        $string .= "Perfil de la estructura\n";
        $string .= "n\n";
        $string .= "y\n";
        $string .= "Color\n";
        $string .= "integer\n";
        $string .= "color de la estructura\n";
        $string .= "y\n";
        $string .= "$idnom\n";
        $string .= "n\n";
        $string .= "y\n";

        $dialog->setInputStream($this->getInputStream($string));

        $commandTester->execute(array(
            'command' => $command->getName(),
//            '--no-cache:clear' => true
        ));

        $this->assertRegExp('/Este es el ID de la estructura insertada/', $commandTester->getDisplay());
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
        $var = static::$container->get('estructura')->mostrarEstrucs();
        static::$container->get('estructura')->eliminarEstruc(array_pop($var)->getId());
    }


}
