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


class bosonEliminarCampoNomCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('boson:eyc:eliminarCampoNom')
            ->setDefinition(array(new InputArgument('id', InputArgument::REQUIRED, 'Id del campo del nomenclador', null)))
            ->setDescription('Elimina un campo de un nomenclador')
            ->setHelp('Recibe un id de un campo de  nomenclador y lo elimina');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');

        $contenedor = $this->getContainer();

        $contenedor->get('nomenclador')->eliminarCampoNom($id);


        $output->writeln('<info>El campo del nomenclador se eliminó de forma satisfactoria.</info>');
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


            $camposnom = $contenedor->get('nomenclador')->mostrarCamposNom($idn);

            if (count($camposnom) > 0)
            {
                $ids = array();
                $output->writeln(' ');
                $output->writeln('Campos registrados');
                $output->writeln(' ');

                foreach ($camposnom as $nom) {
                    $output->writeln('id:' . $nom->getId() . ',     ' . 'nombre:' . $nom->getNombre());
                    $ids[] = $nom->getId();
                }

                $output->writeln(' ');

                $idc = $dialog->askAndValidate($output, '<info>Ingrese el id del campo que desea eliminar:</info> ',
                    function ($valor) use ($ids) {
                        if (false == in_array($valor, $ids)) {
                            throw new \InvalidArgumentException($valor . ' no es un id válido');
                        }
                        return $valor;
                    }, false);

                $idc = intval($idc);

                $input->setArgument('id', $idc);
            }
            else
                $output->writeln('<info>No existen campos registrados.</info>');
        }
        else
            $output->writeln('<info>No existen nomencladores registrados.</info>');
    }

}
