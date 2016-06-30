<?php
/**
 * Created by PhpStorm.
 * User: orlando
 * Date: 3/13/15
 * Time: 9:24 a.m.
 */

// EyCBundle/Command/valoresNomCommand.php
namespace UCI\Boson\EyCBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;



class bosonMostrarNomsCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('boson:eyc:mostrarNoms')

            ->setDescription('Muestra todos los nomencladores')
            ->setHelp('Muestra todos los nomencladores');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contenedor = $this->getContainer();

        $noms = $contenedor->get('nomenclador')->mostrarNoms();
        foreach( $noms as $n)
        {
            $output->writeln($n->getId(). "   ". $n->getNombre());
        }
    }
}
