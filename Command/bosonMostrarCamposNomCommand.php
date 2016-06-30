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


class bosonMostrarCamposNomCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('boson:eyc:mostrarCamposNom')
            ->setDefinition(array(new InputArgument('id', InputArgument::REQUIRED, 'Id del nomenclador', null)))
            ->setDescription('Muestra los campos de un nomenclador')
            ->setHelp('Recibe un id de un nomenclador y muestra sus campos');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');

        $contenedor = $this->getContainer();

        $campos = $contenedor->get('nomenclador')->mostrarCamposNom($id);

        if (count($campos) > 0)
        {
            foreach ($campos as $elem)
            {
                $output->writeln($elem->getId() . ' ' . $elem->getNombre() . ' ' . $elem->getTipoDato() . ' ' .
                    $elem->getVinculado() . ' ' . $elem->getNomencladorVin());
            }
        }
        else
        {
            $output->writeln('<info>El nomenclador no existe o no tiene campos asociados.</info>');
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

        $ids = array();

        $output->writeln(' ');

        $contenedor = $this->getContainer();
        $noms = $contenedor->get('nomenclador')->mostrarNoms();

        if (count($noms) > 0)
        {
            $output->writeln(' ');
            $output->writeln('Nomencladores registrados');
            $output->writeln(' ');

            foreach ($noms as $nom)
            {
                $output->writeln('id:' . $nom->getId() . ',     ' . 'nombre:' . $nom->getNombre());
                $ids[] = $nom->getId();
            }

            $output->writeln(' ');

            $idn = $dialog->askAndValidate($output, '<info>Ingrese el id del nomenclador del que desea mostrar los campos:</info> ',
                function ($valor) use ($ids) {
                    if (false == in_array($valor, $ids)) {
                        throw new \InvalidArgumentException($valor . ' no es un id válido');
                    }
                    return $valor;
                }, false);

            $idn = intval($idn);

            $input->setArgument('id', $idn);


        }
        else
            $output->writeln('<info>No existen nomencladores registrados.</info>');
    }

}
