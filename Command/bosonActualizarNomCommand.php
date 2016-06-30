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



class bosonActualizarNomCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('boson:eyc:actualizarNom')
            ->setDefinition(array(new InputArgument('id',
                                                    InputArgument::REQUIRED,
                                                    'El id del nomenclador a actualizar'),
                                  new InputArgument('nombre',
                                                    InputArgument::REQUIRED,
                                                    'El nombre del nomenclador a actualizar')
                                  )
                           )
            ->setDescription('actualizar un nomenclador')
            ->setHelp('actualizar un nomenclador.');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $contenedor = $this->getContainer();
        $id = $input->getArgument('id');
        $nom = $input->getArgument('nombre');

        $contenedor->get('nomenclador')->actualizarNom($id, $nom);

        $output->writeln('El nomenclador se actualizó satisfactoriamente:');
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

        if (count($noms) > 0)
        {
            $output->writeln(' ');
            $output->writeln('Nomencladores registrados');
            $output->writeln(' ');

            foreach ($noms as $nom)
            {
                $output->writeln('id: ' . $nom->getId() . ',     ' . 'nombre: ' . $nom->getNombre());
                $ids[] = $nom->getId();
            }

            $output->writeln(' ');
            $nomencladorVin = $dialog->askAndValidate($output, '<info>Ingrese el id del nomenclador que desea actualizar:</info> ',
                function ($valor) use ($ids) {
                    if (false == in_array($valor, $ids)) {
                        throw new \InvalidArgumentException($valor . ' no es un id válido');
                    }
                    return $valor;
                }, false);

            $nomencladorVin = intval($nomencladorVin);

            $nombreNom = $dialog->ask($output, '<info>Nombre del nomenclador:</info>', 'nomenclador');

            $input->setArgument('id', $nomencladorVin);
            $input->setArgument('nombre', $nombreNom);
        }
        else
            $output->writeln('no existen nomencladores registrados');


    }
}
