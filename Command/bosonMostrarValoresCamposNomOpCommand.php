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



class bosonMostrarValoresCamposNomOpCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('boson:eyc:mostrarValoresCamposNomOp')
            ->setDefinition(array(new InputArgument('id', InputArgument::REQUIRED, 'id de la instancia del nomenclador', Null)))
            ->setDescription('Muestra los campos de la instancia de un nomenclador')
            ->setHelp('Muestra los campos de la instancia de un nomenclador');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $contenedor = $this->getContainer();

        $noms = $contenedor->get('nomenclador')->mostrarValoresCamposNomOp($id);

        if (count($noms) > 0)
        {
            $output->writeln($noms['nom']->getId() . "   " . $noms['nom']->getNombre());

            foreach ($noms['campos'] as $n)
            {
              if ($n->getCampoNom() -> getVinculado())
              {
                $idin =  intval($n->getValor());

                $instancianom = $contenedor->get('nomenclador')->mostrarNomOp($idin);

                $output->writeln($n->getCampoNom() -> getId().'    '. $n->getCampoNom() ->getNombre().'    '. $instancianom -> getNombre());
              }
              else
              {
                $output->writeln($n->getCampoNom() -> getId().'    '. $n->getCampoNom() ->getNombre().'    '. $n->getValor());
              }

            }
        }
        else
            $output->writeln('No esxisten valores que mostrar');
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(' ');
        $contenedor = $this->getContainer();
        $dialog = $this->getHelperSet()->get('dialog');

        $ids = array();

        $nomencladores = $contenedor->get('nomenclador')->mostrarNoms();

        if (count($nomencladores) > 0)
        {
            $output->writeln('Nomencladores registrados');
            $output->writeln(' ');

            foreach ($nomencladores as $nom)
            {
                $output->writeln('id:' . $nom->getId() . ',     ' . 'nombre:' . $nom->getNombre());
                $ids[] = $nom->getId();

            }

            $output->writeln(' ');

            $nomenclador = $dialog->askAndValidate($output, '<info>Ingrese el id del nomenclador del que desea ver sus instancias:</info> ',
                function ($valor) use ($ids) {
                    if (false == in_array($valor, $ids)) {
                        throw new \InvalidArgumentException($valor . ' no es un id válido');
                    }
                    return $valor;
                }, false);

            $nomenclador = intval($nomenclador);

            $instanciasnomenclador = $contenedor->get('nomenclador')->mostrarNomOps($nomenclador);

            if (count($instanciasnomenclador) > 0)
            {
                $ids = array();

                $output->writeln(' ');
                $output->writeln('Instancias del nomenclador registradas');

                foreach ($instanciasnomenclador as $instancias)
                {
                    $output->writeln('id:' . $instancias->getId() . ',     ' . 'nombre:' . $instancias->getNombre());
                    $ids[] = $instancias->getId();
                }

                $output->writeln(' ');

                $nomencladorop = $dialog->askAndValidate($output, '<info>Ingrese el id de la instancia del nomenclador que desea mostrar:</info> ',
                    function ($valor) use ($ids)
                    {
                        if (false == in_array($valor, $ids))
                        {
                            throw new \InvalidArgumentException($valor . ' no es un id válido');
                        }
                        return $valor;
                    }, false);

                if ($nomencladorop > 0)
                {
                    $input->setArgument('id', $nomencladorop);
                }
            }
            else
            {
                $output->writeln(array('Esta estructura no tiene campos asociados', 'no se puede obtener una instancia de esta estructura'));
                exit;
            }
        }
        else
        {
            $output->writeln(array('No existen nomencladores registrados','escoja salir y registrelos'));
            exit;
        }
    }
}
