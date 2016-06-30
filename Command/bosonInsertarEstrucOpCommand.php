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


class bosonInsertarEstrucOpCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
            ->setName('boson:eyc:insertarEstrucOp')
            ->setDefinition(array(new InputArgument('id', InputArgument::REQUIRED, 'Id de la estructura', null),
                                  new InputArgument('idp', InputArgument::REQUIRED, 'Id de la estructura padre', null),
                                  new InputArgument('nombre', InputArgument::REQUIRED, 'Nombre de la instancia de la estructura', null),
                                  new InputArgument('campos', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Campos de la instancia de la estructura', null)))
            ->setDescription('Inserta una instancia de una estructura')
            ->setHelp('Inserta una instancia de una estructura, cuyos atributos están en correspondencia
                       con los campos definidos para la estructura especificada con el id.');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id = $input->getArgument('id');
        $idp = $input->getArgument('idp');
        $nombre = $input->getArgument('nombre');
        $campos = $input->getArgument('campos');


        if ($id > 0 && $nombre != " " && count($campos) > 0)
        {
            $contenedor = $this->getContainer();
            $error = $contenedor->get('estructura')->insertarEstrucOp($id, $nombre, $campos, $idp);

            if ($error instanceof ConstraintViolationListInterface)
            {
                foreach ($error as $elem)
                {
                    $output->writeln($elem->getMessage());
                    $output->writeln($elem->getPropertyPath());
                }
            }
            else
            {
                $output->writeln('<info>La instancia de la estructura se insertó satisfactoriamente.</info>');
            }
        }
        else
            $output->writeln('<info>Las datos no son correctos, vuelva a intentarlo.</info>');
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
        $nombreestructura = array();
        $valorescampos = array();
        $estructurapadre = 0;

        $estrucs = $contenedor->get('estructura')->mostrarEstrucs();

        if (count($estrucs) > 0) {
            $output->writeln(' ');
            $output->writeln('Estructuras registradas');
            $output->writeln(' ');

            foreach ($estrucs as $estruc) {
                $output->writeln('id:' . $estruc->getId() . ',     ' . 'nombre:' . $estruc->getNombre());
                $ids[] = $estruc->getId();
                $nombreestructura[] = $estruc->getNombre();
            }

            $estrucregistradas = array_combine($ids, $nombreestructura);

            $output->writeln(' ');
            $estructura = $dialog->askAndValidate($output, '<info>Ingrese el id de la estructura que desea instanciar:</info> ',
                function ($valor) use ($ids) {
                    if (false == in_array($valor, $ids)) {
                        throw new \InvalidArgumentException($valor . ' no es un id válido');
                    }
                    return $valor;
                }, false);

            $estructura = intval($estructura);

            $output->writeln(' ');
            $nombre = $dialog->ask($output, '<info>Instancia de la estructura </info>' . $estrucregistradas[$estructura] . '<info>:</info>', 'estructuraop');

            $camposestructura = $contenedor->get('estructura')->mostrarCamposEstruc($estructura);

            if (count($camposestructura) > 0)
            {

                foreach ($camposestructura as $campo)
                {
                    $output->writeln(' ');

                    if ($campo->getVinculado())
                    {
                        $nomencladorops = $contenedor->get('nomenclador')->mostrarNomOps($campo->getNomenclador());

                        if (count($nomencladorops) > 0)
                        {
                            $idsop = array();

                            $output->writeln(' ');
                            $output->writeln('Instancias de nomencladores registradas');
                            $output->writeln(' ');

                            foreach ($nomencladorops as $nomop)
                            {
                                $output->writeln('id:' . $nomop->getId() . ',     ' . 'nombre:' . $nomop->getNombre());
                                $idsop[] = $nomop->getId();
                            }

                            $output->writeln(' ');

                            $nomencladorop = $dialog->askAndValidate($output, '<info>Ingrese el id de la instancia del nomenclador:</info> ',
                                function ($valor) use ($idsop)
                                {
                                    if (false == in_array($valor, $idsop))
                                    {
                                        throw new \InvalidArgumentException($valor . ' no es un id válido');
                                    }
                                    return $valor;
                                }, false);


                            $valorescampos[] = array('id' => $campo->getId(), 'valor' => $nomencladorop);
                        }
                        else
                        {
                            $output->writeln(array('No existen instancias de nomencladores registrados', 'escoja salir y regístrelos'));
                            $salir = $dialog->askConfirmation($output, '<info>¿Desea salir? (y, n)[</info><comment>y</comment><info>]:</info>', true);

                            exit;
                        }
                    }
                    else
                    {
                        $output->writeln(' ');
                        $valorentrada = $dialog->askAndValidate($output, '<info>Ingrese </info>' . $campo->getNombre() . ' ' . $nombre . '<info>:</info> ',
                            function ($valor) use ($contenedor, $campo)
                            {

                                if (!$contenedor->get('nomenclador')->validarTipoValor($campo->getTipoDato(), $valor))
                                {
                                    throw new \InvalidArgumentException($valor . ' no es un id válido');
                                }
                                return $valor;
                            }, false);


                        $valorescampos[] = array('id' => $campo->getId(), 'valor' => $valorentrada);

                    }

                }
            }
            else
            {
                $output->writeln(array('Esta estructura no tiene campos asociados', 'no se puede obtener una instancia de esta estructura'));
            }

            $output->writeln('Jerarquía de estructuras');
            $output->writeln(' ');

            $estruc = $contenedor->get('estructura')->mostrarEstrucRaizOp();

            if ($estruc != null)
            {

                $output->writeln('Jerarquía de estructuras');
                $output->writeln(' ');

                $idjerarquia = array();

                foreach( $estruc as $est)
                {
                    $this->mostrarJerarquia($output, $est, $idjerarquia);
                }

                $idnomoppadre = $dialog->askAndValidate($output, '<info>Ingrese el id de la estructura padre:</info> ',
                    function ($valor) use ($idjerarquia)
                    {
                        if (false == in_array($valor, $idjerarquia))
                        {
                            throw new \InvalidArgumentException($valor . ' no es un id válido');
                        }
                        return $valor;
                    }, false);
                $estructurapadre = intval($idnomoppadre);
            }

            $output->writeln(' ');
            $insertarnom = $dialog->askConfirmation($output, '<info>¿Desea insertar la estructura? (y, n)[</info><comment>y</comment><info>]:</info>', true);

            if ($insertarnom)
            {
                $input->setArgument('id', $estructura);
                $input->setArgument('idp', $estructurapadre);
                $input->setArgument('nombre', $nombre);
                $input->setArgument('campos', $valorescampos);

            }
            else
                exit;
        }
        else
        {
            $output->writeln(array('No existen estructuras registrados','escoja salir y regístrelos'));
            $salir = $dialog->askConfirmation($output, '<info>Desea salir? (y, n)[</info><comment>y</comment><info>]:</info>', true);

            exit;
        }
    }


    protected function mostrarJerarquia(OutputInterface $output, $nodo, &$arrayids, $separador = ' ')
    {
        if ($nodo != null)
        {
            $output->writeln($separador.$nodo->getId(). "   ". $nodo->getNombre());

            $arrayids[] = $nodo->getId();

            $hijos = $nodo -> getChildren();

            foreach ($hijos as $h)
            {
                if ($nodo != $h)
                {
                    $this->mostrarJerarquia($output, $h, $arrayids, $separador.'   ');
                }
            }

        }
    }
}
