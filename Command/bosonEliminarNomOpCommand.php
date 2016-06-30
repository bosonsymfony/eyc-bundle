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



class bosonEliminarNomOpCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('boson:eyc:eliminarNomOp')
            ->setDefinition(array(new InputArgument('id', InputArgument::REQUIRED, 'El id de la instancia de  nomenclador a eliminar')))
            ->setDescription('eliminar un nomenclador')
            ->setHelp('eliminar un nomenclador.');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contenedor = $this->getContainer();
        $id = $input->getArgument('id');

        $contenedor->get('nomenclador')->eliminarNomOp($id);


        $output->writeln('La instancia del nomenclador se eliminó satisfactoriamente:');
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
        $contenedor = $this->getContainer();
        $noms = $contenedor->get('nomenclador')->mostrarNoms();

        if (count($noms) > 0) {
            $output->writeln(' ');
            $output->writeln('Nomencladores registrados');
            $output->writeln(' ');

            foreach ($noms as $nom) {
                $output->writeln('id: ' . $nom->getId() . ',     ' . 'nombre: ' . $nom->getNombre());
                $ids[] = $nom->getId();
            }

            $output->writeln(' ');
            $nomencladorVin = $dialog->askAndValidate($output, '<info>Ingrese el id del nomenclador del que desea eliminar una instancia:</info> ',
                function ($valor) use ($ids) {
                    if (false == in_array($valor, $ids)) {
                        throw new \InvalidArgumentException($valor . ' no es un id válido');
                    }
                    return $valor;
                }, false);

            $nomencladorVin = intval($nomencladorVin);

            $noms = $contenedor->get('nomenclador')->mostrarNomOps($nomencladorVin);

            if (count($noms) > 0)
            {
                $output->writeln(' ');
                $output->writeln('Instancias del nomenclador registradas');
                $output->writeln(' ');

                foreach ($noms as $nom)
                {
                    $output->writeln('id: ' . $nom->getId() . ',     ' . 'nombre: ' . $nom->getNombre());
                    $ids[] = $nom->getId();
                }

                $output->writeln(' ');
                $nomencladorVin = $dialog->askAndValidate($output, '<info>Ingrese el id de la instancia del nomenclador que desea eliminar:</info> ',
                    function ($valor) use ($ids) {
                        if (false == in_array($valor, $ids)) {
                            throw new \InvalidArgumentException($valor . ' no es un id válido');
                        }
                        return $valor;
                    }, false);

                $nomencladorVin = intval($nomencladorVin);

                $input->setArgument('id', $nomencladorVin);

            }
            else
                $output->writeln('no existen instancias registradas para este nomenclador');

        }
        else
            $output->writeln('no existen nomencladores registrados');



    }

}
