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
use Symfony\Component\Validator\ConstraintViolationListInterface;


class bosonInsertarNomCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('boson:eyc:insertarNom')
            ->setDefinition(array(new InputArgument('nombre', InputArgument::REQUIRED, 'Nombre del nomenclador', null)))
            ->setDescription('Inserta un nomenclador')
            ->setHelp('Inserta un nomenclador, debe definir el nombre');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $nombre = $input->getArgument('nombre');

        if ($nombre != " " && is_string($nombre))
        {
            $contenedor = $this->getContainer();
            $result = $contenedor->get('nomenclador')->insertarNom($nombre);


            if ($result instanceof ConstraintViolationListInterface)
            {
                foreach($result as $elem )
                {
                    $output->writeln($elem->getMessage());
                    $output->writeln($elem->getPropertyPath());
                }

                exit;
            }

            $output->writeln('El nomenclador se ha insertado satisfactoriamente');
        }
        else
        {
            $output->writeln('<info>Las datos no son correctos, vuelva a intentarlo.</info>');
        }

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(' ');
        $dialog = $this->getHelperSet()->get('dialog');

        $nombre = $dialog->ask($output, '<info>Nombre del nomenclador:</info>', 'nomenclador');
        $input->setArgument('nombre', $nombre);
    }

}