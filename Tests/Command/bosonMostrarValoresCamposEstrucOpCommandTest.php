<?php
/**
 * Created by PhpStorm.
 * User: orlando
 * Date: 7/9/15
 * Time: 5:38 p.m.
 */

namespace EyCBundle\Tests\Command;

namespace EyCBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UCI\Boson\EyCBundle\Command\bosonMostrarValoresCamposEstrucOpCommand;


if(file_exists(__DIR__ . '/../../../../../../app/AppKernel.php')){
    require_once __DIR__ . '/../../../../../../app/AppKernel.php';
}
else{
    require_once __DIR__ . '/../../../../../../../../app/AppKernel.php';
}


class bosonMostrarValoresCamposEstrucOpCommandTest extends \PHPUnit_Framework_TestCase {

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
    static $idEstruc1;

    /**
     * @var aux
     */
    static $idCampEstruc1;

    /**
     * @var aux
     */
    static $idEstrucOp1;


    protected function setUp()
    {
        static::$kernel = new \AppKernel('dev', true);
        static::$kernel->boot();
        static::$container = static::$kernel->getContainer();

        //Agrega una estructura facultad
        static::$container->get('estructura')->insertarEstruc("Facultad", true);
        $estrucs1 = static::$container->get('estructura')->mostrarEstrucs();
        static::$idEstruc1 = array_pop($estrucs1)->getId();

        //Le agrega un campo a la estructura facultad
        static::$container->get('estructura')->insertarCampoEstruc(static::$idEstruc1, "Disciplina", "string", "Perfil de la estructura", false);
        $camp1 = static::$container->get('estructura')->mostrarCamposEstruc(static::$idEstruc1);
        static::$idCampEstruc1 = array_pop($camp1)->getId();

        //Inserta una instancia de facultad.
        static::$container->get('estructura')->insertarEstrucOp(static::$idEstruc1 , "Facultad3",  array(array('id' => static::$idCampEstruc1,'valor' => "Informatica")));
        $estrucop1 = static::$container->get('estructura')->mostrarEstrucOps(static::$idEstruc1);
        static::$idEstrucOp1 = array_pop($estrucop1)->getId();

    }


    public function testInteractive()
    {
        $application = new Application(static::$kernel);
        $application->add(new bosonMostrarValoresCamposEstrucOpCommand());

        $command = $application->find('boson:eyc:mostrarValoresCamposEstrucOp');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('dialog');

        $idestruc = static::$idEstruc1;
        $idestrucop = static::$idEstrucOp1;

        $string = "$idestruc\n";
        $string .= "$idestrucop\n";


        $dialog->setInputStream($this->getInputStream($string));

        $commandTester->execute(array(
            'command' => $command->getName(),
//            '--no-cache:clear' => true
        ));

        $string = strval(static::$idEstrucOp1)."   "."Facultad3\n".strval(static::$idCampEstruc1)."    "."Disciplina"."    "."Informatica";
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
        static::$container->get('estructura')->eliminarEstruc(static::$idEstruc1);
    }
}
