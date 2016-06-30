<?php
/**
 * Created by PhpStorm.
 * User: orlando
 * Date: 7/9/15
 * Time: 3:50 p.m.
 */

namespace EyCBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UCI\Boson\EyCBundle\Command\bosonInsertarEstrucSubordinadaCommand;


if(file_exists(__DIR__ . '/../../../../../../app/AppKernel.php')){
    require_once __DIR__ . '/../../../../../../app/AppKernel.php';
}
else{
    require_once __DIR__ . '/../../../../../../../../app/AppKernel.php';
}

class bosonInsertarEstrucSubordinadaCommandTest extends \PHPUnit_Framework_TestCase {

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
    static $idEstruc2;


    protected function setUp()
    {
        static::$kernel = new \AppKernel('dev', true);
        static::$kernel->boot();
        static::$container = static::$kernel->getContainer();
        //Agrega una estructura facultad
        static::$container->get('estructura')->insertarEstruc("Facultad", true);
        $estrucs1 = static::$container->get('estructura')->mostrarEstrucs();
        static::$idEstruc1 = array_pop($estrucs1)->getId();

        //Agrega una estructura Departamento
        static::$container->get('estructura')->insertarEstruc("Departamento", true);
        $estrucs2 = static::$container->get('estructura')->mostrarEstrucs();
        static::$idEstruc2 = array_pop($estrucs2)->getId();

    }


    public function testInteractive()
    {
        $application = new Application(static::$kernel);
        $application->add(new bosonInsertarEstrucSubordinadaCommand());

        $command = $application->find('boson:eyc:insertarEstrucSub');
        $commandTester = new CommandTester($command);

        $dialog = $command->getHelper('dialog');

        $idestruc1 = static::$idEstruc1;
        $idestruc2 = static::$idEstruc2;

        $string = "$idestruc1\n";
        $string .= "$idestruc2\n";
        $string .= "\n";
        $string .= "y\n";

        $dialog->setInputStream($this->getInputStream($string));

        $commandTester->execute(array(
            'command' => $command->getName(),
//            '--no-cache:clear' => true
        ));


        $this->assertRegExp('/Las subordinaciones se adicionaron correctamente/', $commandTester->getDisplay());
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

        static::$container->get('estructura')->eliminarEstruc(static::$idEstruc2);
        static::$container->get('estructura')->eliminarEstruc(static::$idEstruc1);
    }

}
