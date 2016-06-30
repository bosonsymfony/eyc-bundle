<?php
/**
 * Created by PhpStorm.
 * User: orlando
 * Date: 3/13/15
 * Time: 9:24 a.m.
 */

// EyCBundle/Command/insertarEstrucCommand.php
namespace UCI\Boson\EyCBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Validator\ConstraintViolationListInterface;


class bosonMostrarEstrucSubordinadaCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('boson:eyc:mostrarEstrucSub')
            ->setDescription('Muestra las estructuras subordinadas')
            ->setHelp('Muestra las estructuras subordinadas');

    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contenedor = $this->getContainer();
        $estructuras = $contenedor->get('estructura')->mostrarEstrucs();

        if ($estructuras != NULL)
        {
            foreach ($estructuras as $estructura)
            {
              $output->writeln('id:'.$estructura->getId().'        nombre:'.$estructura->getNombre());
              $hijas = $estructura->getEstructurasHijas();

                foreach ($hijas as $hija)
                {
                    $output->writeln('  id:'.$hija->getId().'        nombre:'.$hija->getNombre());
                }
            }
        }
        else
            $output->writeln('No hay estructuras registradas.');

    }

}
