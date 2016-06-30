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



class bosonMostrarValoresCamposEstrucOpCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('boson:eyc:mostrarValoresCamposEstrucOp')
            ->setDefinition(array(new InputArgument('id', InputArgument::REQUIRED, 'id de la instancia de la estructura', Null)))
            ->setDescription('Muestra los campos de la instancia de una estructura')
            ->setHelp('Muestra los campos de la instancia de una estructura');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $contenedor = $this->getContainer();

        $estrucs = $contenedor->get('estructura')->mostrarValoresCamposEstrucOp($id);

        if (count($estrucs) > 0)
        {
            $output->writeln($estrucs['estruc']->getId() . "   " . $estrucs['estruc']->getNombre());

            foreach ($estrucs['campos'] as $est)
            {
              if ($est->getCampoEstruc() -> getVinculado())
              {
                $idie =  intval($est->getValor());

                $instanciaest = $contenedor->get('nomenclador')->mostrarNomOp($idie);

                $output->writeln($est->getCampoEstruc() -> getId().'    '. $est->getCampoEstruc() ->getNombre().'    '. $instanciaest -> getNombre());
              }
              else
              {
                $output->writeln($est->getCampoEstruc() -> getId().'    '. $est->getCampoEstruc() ->getNombre().'    '. $est->getValor());
              }

            }
        }
        else
            $output->writeln('No existen valores que mostrar');
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

        $estructuras = $contenedor->get('estructura')->mostrarEstrucs();

        if (count($estructuras) > 0)
        {
            $output->writeln('Estructuras registradas');
            $output->writeln(' ');

            foreach ($estructuras as $est)
            {
                $output->writeln('id:' . $est->getId() . ',     ' . 'nombre:' . $est->getNombre());
                $ids[] = $est->getId();

            }

            $output->writeln(' ');

            $estructura = $dialog->askAndValidate($output, '<info>Ingrese el id de la estructura de la que desea ver sus instancias:</info> ',
                function ($valor) use ($ids) {
                    if (false == in_array($valor, $ids)) {
                        throw new \InvalidArgumentException($valor . ' no es un id válido');
                    }
                    return $valor;
                }, false);

            $estructura = intval($estructura);

            $instanciasestructura = $contenedor->get('estructura')->mostrarEstrucOps($estructura);

            if (count($instanciasestructura) > 0)
            {
                $ids = array();

                $output->writeln(' ');
                $output->writeln('Instancias de las estructuras registradas');

                foreach ($instanciasestructura as $instancias)
                {
                    $output->writeln('id:' . $instancias->getId() . ',     ' . 'nombre:' . $instancias->getNombre());
                    $ids[] = $instancias->getId();
                }

                $output->writeln(' ');

                $estructuraop = $dialog->askAndValidate($output, '<info>Ingrese el id de la instancia de la estructura que desea mostrar:</info> ',
                    function ($valor) use ($ids)
                    {
                        if (false == in_array($valor, $ids))
                        {
                            throw new \InvalidArgumentException($valor . ' no es un id válido');
                        }
                        return $valor;
                    }, false);

                if ($estructuraop > 0)
                {
                    $input->setArgument('id', $estructuraop);
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
            $output->writeln(array('No existen estructuras registradas','escoja salir y registrelos'));
            exit;
        }
    }
}
